<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $settings = $this->normalizeFileSettings($settings);
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except([
            '_token',
            '_method',
            // arquivos (tratados separadamente)
            'pwa_icon_192',
            'pwa_icon_512',
            'pwa_splash',
            'pwa_banner',
            'preloader_image',
            'logo_image',
            'favicon_image',
            'logo_admin',
            'logo_auth',
            'logo_front',
            'watermark_image',
            // flags de remoção
            'remove_pwa_icon_192',
            'remove_pwa_icon_512',
            'remove_pwa_splash',
            'remove_pwa_banner',
            'remove_preloader_image',
            'remove_logo_image',
            'remove_favicon_image',
            'remove_logo_admin',
            'remove_logo_auth',
            'remove_logo_front',
            'remove_watermark_image',
            'hero_image',
            'remove_hero_image',
            'smtp_test_email', // Não salvar e-mail de teste
        ]);

        $dirs = [
            'uploads/imagens',
            'uploads/imagens/administrativo',
            'uploads/imagens/logins',
            'uploads/imagens/frontend',
            'uploads/imagens/pwa',
            'uploads/imagens/preloader',
            'uploads/imagens/geral',
            'uploads/imagens/watermark',
        ];
        foreach ($dirs as $dir) {
            $this->ensurePublicDir($dir);
        }

        $removals = [
            'pwa_icon_192',
            'pwa_icon_512',
            'pwa_splash',
            'pwa_banner',
            'preloader_image',
            'logo_image',
            'favicon_image',
            'logo_admin',
            'logo_auth',
            'logo_front',
            'watermark_image',
            'hero_image',
        ];
        foreach ($removals as $key) {
            if ($request->boolean('remove_' . $key)) {
                $this->removeFile($key);
            }
        }

        if ($request->hasFile('pwa_icon_192')) {
            $this->replaceFile('pwa_icon_192', $this->storePublic($request->file('pwa_icon_192'), 'uploads/imagens/pwa'));
        }
        if ($request->hasFile('pwa_icon_512')) {
            $this->replaceFile('pwa_icon_512', $this->storePublic($request->file('pwa_icon_512'), 'uploads/imagens/pwa'));
        }
        if ($request->hasFile('pwa_splash')) {
            $this->replaceFile('pwa_splash', $this->storePublic($request->file('pwa_splash'), 'uploads/imagens/pwa'));
        }
        if ($request->hasFile('pwa_banner')) {
            $this->replaceFile('pwa_banner', $this->storePublic($request->file('pwa_banner'), 'uploads/imagens/pwa'));
        }
        if ($request->hasFile('preloader_image')) {
            $this->replaceFile('preloader_image', $this->storePublic($request->file('preloader_image'), 'uploads/imagens/preloader'));
        }
        if ($request->hasFile('logo_image')) {
            $this->replaceFile('logo_image', $this->storePublic($request->file('logo_image'), 'uploads/imagens/geral'));
        }
        if ($request->hasFile('favicon_image')) {
            $this->replaceFile('favicon_image', $this->storePublic($request->file('favicon_image'), 'uploads/imagens/geral'));
        }
        if ($request->hasFile('logo_admin')) {
            $this->replaceFile('logo_admin', $this->storePublic($request->file('logo_admin'), 'uploads/imagens/administrativo'));
        }
        if ($request->hasFile('logo_auth')) {
            $this->replaceFile('logo_auth', $this->storePublic($request->file('logo_auth'), 'uploads/imagens/logins'));
        }
        if ($request->hasFile('logo_front')) {
            $this->replaceFile('logo_front', $this->storePublic($request->file('logo_front'), 'uploads/imagens/frontend'));
        }
        if ($request->hasFile('watermark_image')) {
            $this->replaceFile('watermark_image', $this->storePublic($request->file('watermark_image'), 'uploads/imagens/watermark'));
        }
        if ($request->hasFile('hero_image')) {
            $this->replaceFile('hero_image', $this->storePublic($request->file('hero_image'), 'uploads/imagens/frontend'));
        }

        $bools = ['pwa_enabled', 'preloader_enabled'];
        foreach ($bools as $b) {
            $data[$b] = $request->boolean($b) ? 1 : 0;
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value ?? '']);
        }

        return redirect()->back()->with('success', 'Configurações salvas');
    }

    private function storePublic($file, $relativeDir)
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $name = uniqid('', true) . '.' . $ext;
        $targetDir = public_path($relativeDir);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }
        $file->move($targetDir, $name);
        return $relativeDir . '/' . $name;
    }

    private function replaceFile($key, $newPath)
    {
        $this->removeFile($key);
        Setting::updateOrCreate(['key' => $key], ['value' => $newPath]);
    }

    private function removeFile($key)
    {
        $old = Setting::where('key', $key)->value('value');
        if (!$old) {
            return;
        }
        $paths = [
            public_path($old),
            public_path(ltrim(str_replace('storage/', '', $old), '/')),
        ];
        foreach ($paths as $path) {
            if ($path && file_exists($path)) {
                @unlink($path);
            }
        }
    }

    private function ensurePublicDir($dir)
    {
        $full = public_path($dir);
        if (!is_dir($full)) {
            mkdir($full, 0775, true);
        }
    }

    private function normalizeFileSettings(array $settings): array
    {
        $keyDirs = [
            'preloader_image' => ['uploads/imagens/preloader', 'uploads/imagens/geral', 'uploads/imagens'],
            'logo_image' => ['uploads/imagens/geral', 'uploads/imagens'],
            'favicon_image' => ['uploads/imagens/geral', 'uploads/imagens'],
            'logo_admin' => ['uploads/imagens/administrativo', 'uploads/imagens'],
            'logo_auth' => ['uploads/imagens/logins', 'uploads/imagens'],
            'logo_front' => ['uploads/imagens/frontend', 'uploads/imagens'],
            'watermark_image' => ['uploads/imagens/watermark', 'uploads/imagens'],
            'pwa_icon_192' => ['uploads/imagens/pwa', 'uploads/imagens'],
            'pwa_icon_512' => ['uploads/imagens/pwa', 'uploads/imagens'],
            'pwa_splash' => ['uploads/imagens/pwa', 'uploads/imagens'],
            'pwa_banner' => ['uploads/imagens/pwa', 'uploads/imagens'],
            'hero_image' => ['uploads/imagens/frontend', 'uploads/imagens'],
        ];

        foreach ($keyDirs as $key => $searchDirs) {
            $value = $settings[$key] ?? '';
            if (!$value) {
                continue;
            }

            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            if (Str::startsWith($value, ['http://', 'https://'])) {
                continue;
            }

            $value = str_replace('\\', '/', $value);
            $value = preg_replace('/[?#].*$/', '', $value);

            $publicRoot = str_replace('\\', '/', public_path());
            if (Str::startsWith($value, $publicRoot)) {
                $value = ltrim(substr($value, strlen($publicRoot)), '/');
            }

            $value = ltrim($value, '/');
            if (Str::startsWith($value, 'public/')) {
                $value = substr($value, strlen('public/'));
            }

            if (file_exists(public_path($value))) {
                $settings[$key] = $value;
                continue;
            }

            $basename = basename($value);
            $resolved = '';
            foreach ((array) $searchDirs as $dir) {
                $candidate = $dir . '/' . $basename;
                if (file_exists(public_path($candidate))) {
                    $resolved = $candidate;
                    break;
                }
            }
            if ($resolved) {
                $settings[$key] = $resolved;
                Setting::updateOrCreate(['key' => $key], ['value' => $resolved]);
            }
        }
        return $settings;
    }

    public function testSmtp(Request $request)
    {
        $request->validate([
            'smtp_host' => 'required',
            'smtp_port' => 'required',
            'smtp_username' => 'required',
            'smtp_password' => 'required',
            'smtp_from_email' => 'required|email',
            'smtp_test_email' => 'required|email',
        ]);

        $encryption = $request->smtp_encryption;
        if($encryption === 'null' || $encryption === '') $encryption = null;

        $config = [
            'transport' => 'smtp',
            'host'       => trim($request->smtp_host),
            'port'       => trim($request->smtp_port),
            'username'   => trim($request->smtp_username),
            'password'   => trim($request->smtp_password),
            'encryption' => $encryption,
            'timeout'    => null,
            'auth_mode'  => null,
        ];

        \Config::set('mail.mailers.smtp', $config);
        \Config::set('mail.from.address', trim($request->smtp_from_email));
        \Config::set('mail.from.name', $request->smtp_from_name ?? config('app.name'));

        try {
            // Find or create the template
            $template = \App\Models\MailTemplate::firstOrCreate(
                ['slug' => 'smtp_test'],
                [
                    'name' => 'Teste de Configuração SMTP',
                    'category' => 'sistema',
                    'subject' => 'Teste de Envio SMTP - {{site.name}}',
                    'body' => '<h1>Olá, {{user.name}}!</h1><p>Este é um e-mail de teste para validar as configurações de SMTP do sistema <strong>{{site.name}}</strong>.</p><p>Se você recebeu esta mensagem, significa que seu servidor de e-mail está configurado corretamente.</p><br><p>Atenciosamente,<br>Equipe {{site.name}}</p>',
                    'is_active' => true,
                    'locale' => 'pt-BR'
                ]
            );

            // Prioritize Admin Logo (Sidebar/Header) for Emails
            $logo = Setting::where('key', 'logo_admin')->value('value');
            if(!$logo) $logo = Setting::where('key', 'logo_front')->value('value');
            if(!$logo) $logo = Setting::where('key', 'logo_image')->value('value');
            
            $logoUrl = $logo ? asset($logo) : asset('img/logo.svg');

            // Fetch Site Name from Database
            $siteName = Setting::where('key', 'app_name')->value('value');
            if(!$siteName) $siteName = Setting::where('key', 'company_name')->value('value');
            if(!$siteName) $siteName = config('app.name');

            $data = [
                'user' => ['name' => 'Administrador'],
                'site' => [
                    'name' => $siteName,
                    'logo' => $logoUrl
                ],
            ];

            // Render logic simple for test
            $rendered = $template->body;
            $subject = $template->subject ?? 'Teste SMTP';
            
            foreach ($data as $key => $values) {
                foreach ($values as $k => $v) {
                    $pattern = '/\{\{\s*' . $key . '\.' . $k . '\s*\}\}/';
                    $rendered = preg_replace($pattern, $v, $rendered);
                    $subject = preg_replace($pattern, $v, $subject);
                }
            }

            // System Colors
            $primaryColor = Setting::where('key', 'site_color_primary')->value('value') ?? '#007bff';
            $secondaryColor = Setting::where('key', 'site_color_secondary')->value('value') ?? '#6c757d';
            
            // Wrap with layout
            $layout = '
            <div style="background-color: #f4f6f9; padding: 20px; font-family: sans-serif; min-height: 100%;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="center">
                            <div style="background-color: #ffffff; max-width: 600px; padding: 0px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden;">
                                <!-- Header -->
                                <div style="background: linear-gradient(135deg, '.$primaryColor.' 0%, '.$secondaryColor.' 100%); padding: 30px 20px; text-align: center;">
                                    <img src="'.$logoUrl.'" alt="'.$data['site']['name'].'" style="max-height: 60px; max-width: 200px;">
                                </div>
                                
                                <!-- Body -->
                                <div style="padding: 30px; color: #333333; line-height: 1.6;">
                                    '.$rendered.'
                                </div>
                                
                                <!-- Footer -->
                                <div style="background-color: #f8f9fa; padding: 20px; text-align: center; color: #777777; font-size: 12px; border-top: 1px solid #eeeeee;">
                                    <p>&copy; '.date('Y').' '.$data['site']['name'].'. Todos os direitos reservados.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>';

            \Mail::html($layout, function ($message) use ($request, $subject) {
                $message->to($request->smtp_test_email)
                        ->subject($subject);
            });

            return response()->json(['success' => true, 'message' => 'E-mail de teste enviado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao enviar e-mail: ' . $e->getMessage()], 500);
        }
    }
}
