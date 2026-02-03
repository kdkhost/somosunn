<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

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
        $fileKeys = [
            'preloader_image',
            'logo_image',
            'favicon_image',
            'logo_admin',
            'logo_auth',
            'logo_front',
            'watermark_image',
            'pwa_icon_192',
            'pwa_icon_512',
            'pwa_splash',
            'pwa_banner',
            'hero_image',
        ];
        $searchDirs = [
            'uploads/imagens/geral',
            'uploads/imagens/administrativo',
            'uploads/imagens/logins',
            'uploads/imagens/frontend',
            'uploads/imagens/pwa',
            'uploads/imagens/preloader',
            'uploads/imagens/watermark',
            'uploads/imagens',
        ];
        foreach ($fileKeys as $key) {
            $value = $settings[$key] ?? '';
            if (!$value) {
                continue;
            }
            $value = ltrim($value, '/');
            if (file_exists(public_path($value))) {
                continue;
            }
            $basename = basename($value);
            $resolved = '';
            foreach ($searchDirs as $dir) {
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
}
