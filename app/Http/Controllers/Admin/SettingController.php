<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $settings = $this->normalizeFileSettings($settings);

        $analytics = [
            'enabled' => false,
            'today' => 0,
            'last7' => 0,
            'last30' => 0,
            'top_pages' => collect(),
            'top_countries' => collect(),
        ];

        try {
            if (Schema::hasTable('visitor_logs')) {
                $analytics['enabled'] = true;
                $now = now();

                $analytics['today'] = (int) DB::table('visitor_logs')
                    ->whereDate('created_at', $now->toDateString())
                    ->count();

                $analytics['last7'] = (int) DB::table('visitor_logs')
                    ->where('created_at', '>=', $now->copy()->subDays(7))
                    ->count();

                $analytics['last30'] = (int) DB::table('visitor_logs')
                    ->where('created_at', '>=', $now->copy()->subDays(30))
                    ->count();

                $analytics['top_pages'] = DB::table('visitor_logs')
                    ->select('path', DB::raw('count(*) as total'))
                    ->where('created_at', '>=', $now->copy()->subDays(30))
                    ->groupBy('path')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get();

                $analytics['top_countries'] = DB::table('visitor_logs')
                    ->select('country', DB::raw('count(*) as total'))
                    ->where('created_at', '>=', $now->copy()->subDays(30))
                    ->whereNotNull('country')
                    ->where('country', '!=', '')
                    ->groupBy('country')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get();
            }
        } catch (\Throwable $e) {
            // analytics é opcional; não bloqueia a tela de settings
        }

        return view('admin.settings.index', compact('settings', 'analytics'));
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
            'seo_og_image',
            'seo_twitter_image',
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
            'remove_seo_og_image',
            'remove_seo_twitter_image',
            'hero_image',
            'remove_hero_image',
            'smtp_test_email', // Não salvar e-mail de teste
        ]);

        if ($request->hasFile('seo_og_image') && $this->imageIsSmallerThan($request->file('seo_og_image'), 1200, 630)) {
            return redirect()->back()->withInput()->with('error', 'A imagem OpenGraph precisa ter pelo menos 1200×630px.');
        }
        if ($request->hasFile('seo_twitter_image') && $this->imageIsSmallerThan($request->file('seo_twitter_image'), 1200, 628)) {
            return redirect()->back()->withInput()->with('error', 'A imagem do Twitter precisa ter pelo menos 1200×628px.');
        }

        $plyrOptionsJson = trim((string) $request->input('video_plyr_options_json', ''));
        if ($plyrOptionsJson !== '') {
            try {
                json_decode($plyrOptionsJson, true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable $e) {
                return redirect()->back()->withInput()->with('error', 'Opções avançadas do Plyr: JSON inválido.');
            }
        }

        $dirs = [
            'uploads/imagens',
            'uploads/imagens/administrativo',
            'uploads/imagens/logins',
            'uploads/imagens/frontend',
            'uploads/imagens/pwa',
            'uploads/imagens/preloader',
            'uploads/imagens/geral',
            'uploads/imagens/watermark',
            'uploads/imagens/seo',
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
            'seo_og_image',
            'seo_twitter_image',
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
        if ($request->hasFile('seo_og_image')) {
            $this->replaceFile('seo_og_image', $this->storePublic($request->file('seo_og_image'), 'uploads/imagens/seo'));
        }
        if ($request->hasFile('seo_twitter_image')) {
            $this->replaceFile('seo_twitter_image', $this->storePublic($request->file('seo_twitter_image'), 'uploads/imagens/seo'));
        }

        $bools = ['pwa_enabled', 'preloader_enabled'];
        foreach ($bools as $b) {
            $data[$b] = $request->boolean($b) ? 1 : 0;
        }

        $videoBools = [
            'video_player_enabled',
            'video_plyr_autoplay',
            'video_plyr_muted',
            'video_plyr_click_to_play',
            'video_plyr_disable_context_menu',
            'video_watermark_enabled',
            'video_watermark_text_enabled',
            'video_watermark_animate',
        ];
        foreach ($videoBools as $b) {
            if ($request->has($b)) {
                $data[$b] = $request->boolean($b) ? 1 : 0;
            }
        }

        $data['video_plyr_options_json'] = $plyrOptionsJson;

        foreach ([
            'video_plyr_seek_time' => ['min' => 0, 'max' => 120],
            'video_plyr_speed_selected' => ['min' => 0, 'max' => 10],
            'video_watermark_size_percent' => ['min' => 1, 'max' => 100],
            'video_watermark_margin' => ['min' => 0, 'max' => 200],
            'video_watermark_rotate' => ['min' => -180, 'max' => 180],
        ] as $key => $limits) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $raw = trim((string) $data[$key]);
            if ($raw === '') {
                $data[$key] = '';
                continue;
            }

            $value = (int) $raw;
            $value = max((int) $limits['min'], min((int) $limits['max'], $value));
            $data[$key] = (string) $value;
        }

        foreach (['video_plyr_volume', 'video_watermark_opacity'] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $raw = trim((string) $data[$key]);
            if ($raw === '') {
                $data[$key] = '';
                continue;
            }

            $raw = str_replace(',', '.', $raw);
            $value = (float) $raw;
            $value = max(0.0, min(1.0, $value));
            $data[$key] = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
        }

        if (array_key_exists('video_watermark_position', $data)) {
            $allowed = ['top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'];
            $value = trim((string) $data['video_watermark_position']);
            $data['video_watermark_position'] = in_array($value, $allowed, true) ? $value : 'top-right';
        }

        if (array_key_exists('video_watermark_blend', $data)) {
            $allowed = ['normal', 'multiply', 'screen', 'overlay', 'lighten', 'darken'];
            $value = trim((string) $data['video_watermark_blend']);
            $data['video_watermark_blend'] = in_array($value, $allowed, true) ? $value : 'normal';
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
            'seo_og_image' => ['uploads/imagens/seo', 'uploads/imagens'],
            'seo_twitter_image' => ['uploads/imagens/seo', 'uploads/imagens'],
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

    private function imageIsSmallerThan($file, int $minWidth, int $minHeight): bool
    {
        try {
            $path = $file->getPathname();
            $size = @getimagesize($path);
            if (!is_array($size) || !isset($size[0], $size[1])) {
                return true;
            }

            return ((int) $size[0] < $minWidth) || ((int) $size[1] < $minHeight);
        } catch (\Throwable $e) {
            return true;
        }
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
