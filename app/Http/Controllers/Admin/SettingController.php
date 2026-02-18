<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index($group = 'general')
    {
        $allowedGroups = [
            'general',
            'appearance',
            'images',
            'player',
            'ads',
            'pwa',
            'marketplace',
            'gateway',
            'smtp',
            'social',
            'seo',
            'system'
        ];

        if (!in_array($group, $allowedGroups)) {
            return redirect()->route('admin.settings', ['group' => 'general']);
        }

        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $settings = $this->normalizeFileSettings($settings);

        if (request()->routeIs('panel.*')) {
            return view('panel.admin.settings.index', compact('settings', 'group'));
        }

        return view('admin.settings.index', compact('settings', 'group'));
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
            'site_bg_image',
            'remove_site_bg_image',
            'smtp_test_email', // Não salvar e-mail de teste
            // Marketplace hero/exit (arquivos tratados separadamente)
            'marketplace_hero_slide_1_image',
            'marketplace_hero_slide_2_image',
            'marketplace_hero_slide_3_image',
            'marketplace_exit_banner_image',
            // Marketplace remove flags (tratados separadamente)
            'remove_marketplace_hero_slide_1_image',
            'remove_marketplace_hero_slide_2_image',
            'remove_marketplace_hero_slide_3_image',
            'remove_marketplace_exit_banner_image',
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
            'uploads/imagens/marketplace',
            'uploads/imagens/marketplace/hero',
            'uploads/imagens/marketplace/exit',
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
            'site_bg_image',
            'seo_og_image',
            'seo_twitter_image',
            'marketplace_hero_slide_1_image',
            'marketplace_hero_slide_2_image',
            'marketplace_hero_slide_3_image',
            'marketplace_exit_banner_image',
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
        if ($request->hasFile('site_bg_image')) {
            $this->replaceFile('site_bg_image', $this->storePublic($request->file('site_bg_image'), 'uploads/imagens/frontend'));
        }
        if ($request->hasFile('seo_og_image')) {
            $this->replaceFile('seo_og_image', $this->storePublic($request->file('seo_og_image'), 'uploads/imagens/seo'));
        }
        if ($request->hasFile('seo_twitter_image')) {
            $this->replaceFile('seo_twitter_image', $this->storePublic($request->file('seo_twitter_image'), 'uploads/imagens/seo'));
        }
        if ($request->hasFile('marketplace_hero_slide_1_image')) {
            $this->replaceFile('marketplace_hero_slide_1_image', $this->storePublic($request->file('marketplace_hero_slide_1_image'), 'uploads/imagens/marketplace/hero'));
        }
        if ($request->hasFile('marketplace_hero_slide_2_image')) {
            $this->replaceFile('marketplace_hero_slide_2_image', $this->storePublic($request->file('marketplace_hero_slide_2_image'), 'uploads/imagens/marketplace/hero'));
        }
        if ($request->hasFile('marketplace_hero_slide_3_image')) {
            $this->replaceFile('marketplace_hero_slide_3_image', $this->storePublic($request->file('marketplace_hero_slide_3_image'), 'uploads/imagens/marketplace/hero'));
        }
        if ($request->hasFile('marketplace_exit_banner_image')) {
            $this->replaceFile('marketplace_exit_banner_image', $this->storePublic($request->file('marketplace_exit_banner_image'), 'uploads/imagens/marketplace/exit'));
        }

        // Mapeamento de booleanos por grupo para garantir que desativar (unchecked) funcione
        $groupBools = [
            'pwa' => ['pwa_enabled'],
            'appearance' => ['preloader_enabled'],
            'player' => [
                'video_player_enabled',
                'video_plyr_autoplay',
                'video_plyr_muted',
                'video_plyr_click_to_play',
                'video_plyr_disable_context_menu',
                'video_plyr_rewind_enabled',
                'video_plyr_fast_forward_enabled',
                'video_plyr_volume_enabled',
                'video_watermark_enabled',
                'video_watermark_text_enabled',
                'video_watermark_animate'
            ],
            'ads' => ['ads_enabled', 'ads_inter_feed_enabled'],
            'gateway' => [
                'gateway_transparent_checkout',
                'gateway_pass_tax_to_client',
                'mercadopago_method_credit_card',
                'mercadopago_method_pix',
                'mercadopago_method_ticket'
            ],
            'marketplace' => ['marketplace_hero_enabled', 'marketplace_hero_autoplay', 'marketplace_exit_enabled', 'marketplace_events_popup_enabled'],
            'social' => [
                'social_login_enabled',
                'social_google_enabled',
                'social_facebook_enabled',
                'social_twitter_enabled'
            ],
            'system' => ['s3_path_style'],
        ];

        $currentGroup = $request->input('current_group', 'general');
        if (isset($groupBools[$currentGroup])) {
            foreach ($groupBools[$currentGroup] as $b) {
                $data[$b] = $request->has($b) ? 1 : 0;
            }
        }

        $data['video_plyr_options_json'] = $plyrOptionsJson;

        if (array_key_exists('marketplace_hero_animation', $data)) {
            $val = trim((string) $data['marketplace_hero_animation']);
            $data['marketplace_hero_animation'] = in_array($val, ['slide', 'fade'], true) ? $val : 'slide';
        }

        if (array_key_exists('marketplace_hero_interval_seconds', $data)) {
            $raw = trim((string) $data['marketplace_hero_interval_seconds']);
            if ($raw === '') {
                $data['marketplace_hero_interval_seconds'] = '';
            } else {
                $val = (int) $raw;
                $val = max(2, min(20, $val));
                $data['marketplace_hero_interval_seconds'] = (string) $val;
            }
        }

        if (array_key_exists('marketplace_exit_delay_seconds', $data)) {
            $raw = trim((string) $data['marketplace_exit_delay_seconds']);
            if ($raw === '') {
                $data['marketplace_exit_delay_seconds'] = '';
            } else {
                $val = (int) $raw;
                $val = max(0, min(120, $val));
                $data['marketplace_exit_delay_seconds'] = (string) $val;
            }
        }

        if (array_key_exists('marketplace_events_popup_interval_seconds', $data)) {
            $raw = trim((string) $data['marketplace_events_popup_interval_seconds']);
            if ($raw === '') {
                $data['marketplace_events_popup_interval_seconds'] = '';
            } else {
                $val = (int) $raw;
                $val = max(20, min(300, $val));
                $data['marketplace_events_popup_interval_seconds'] = (string) $val;
            }
        }

        if (array_key_exists('marketplace_events_popup_max_per_session', $data)) {
            $raw = trim((string) $data['marketplace_events_popup_max_per_session']);
            if ($raw === '') {
                $data['marketplace_events_popup_max_per_session'] = '';
            } else {
                $val = (int) $raw;
                $val = max(0, min(10, $val));
                $data['marketplace_events_popup_max_per_session'] = (string) $val;
            }
        }

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

        // Infra (reCAPTCHA v3 / S3 / limites de upload)
        foreach ([
            'recaptcha_v3_site_key',
            'recaptcha_v3_secret_key',
            's3_key',
            's3_secret',
            's3_region',
            's3_bucket',
            's3_url',
            's3_endpoint',
        ] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            $data[$key] = trim((string) $data[$key]);
        }

        if (array_key_exists('recaptcha_v3_min_score', $data)) {
            $raw = trim((string) $data['recaptcha_v3_min_score']);
            if ($raw === '') {
                $data['recaptcha_v3_min_score'] = '';
            } else {
                $raw = str_replace(',', '.', $raw);
                $value = (float) $raw;
                $value = max(0.0, min(1.0, $value));
                $data['recaptcha_v3_min_score'] = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
            }
        }

        foreach ([
            'video_max_mb' => ['min' => 1, 'max' => 10240],
            'document_max_mb' => ['min' => 1, 'max' => 1024],
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

        foreach (['allowed_video_formats', 'allowed_document_formats'] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $raw = trim((string) $data[$key]);
            if ($raw === '') {
                $data[$key] = '';
                continue;
            }

            $parts = preg_split('/[,\s;]+/', $raw) ?: [];
            $parts = array_map(static fn($p) => strtolower(trim((string) $p)), $parts);
            $parts = array_values(array_filter($parts, static fn($p) => $p !== '' && preg_match('/^[a-z0-9]+$/', $p)));
            $parts = array_values(array_unique($parts));

            $data[$key] = implode(',', $parts);
        }

        if (array_key_exists('uploads_storage_disk', $data)) {
            $raw = trim((string) $data['uploads_storage_disk']);
            if ($raw === '') {
                $data['uploads_storage_disk'] = '';
            } else {
                $data['uploads_storage_disk'] = in_array($raw, ['public', 's3'], true) ? $raw : 'public';
            }
        }

        if ($request->has('s3_path_style')) {
            $data['s3_path_style'] = $request->boolean('s3_path_style') ? 1 : 0;
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value ?? '']);
        }

        // Se o Admin estiver salvando chaves globais do Mercado Pago, sincronizar com o gateway_account dele
        // para manter consistência entre o painel AdminLTE e o novo Marketplace
        if ($currentGroup === 'gateway') {
            $userId = Auth::id();
            $mpAccount = \App\Models\GatewayAccount::firstOrNew(['user_id' => $userId, 'provider' => 'mercadopago']);

            if (isset($data['mercadopago_public_key']))
                $mpAccount->public_key = $data['mercadopago_public_key'];
            if (isset($data['mercadopago_access_token']))
                $mpAccount->access_token = $data['mercadopago_access_token'];

            // Ativar se tiver as chaves
            if ($mpAccount->public_key && $mpAccount->access_token) {
                $mpAccount->enabled = true;
            }

            $mpAccount->save();
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
            'site_bg_image' => ['uploads/imagens/frontend', 'uploads/imagens'],
            'seo_og_image' => ['uploads/imagens/seo', 'uploads/imagens'],
            'seo_twitter_image' => ['uploads/imagens/seo', 'uploads/imagens'],
            'marketplace_hero_slide_1_image' => ['uploads/imagens/marketplace/hero', 'uploads/imagens/marketplace', 'uploads/imagens/frontend', 'uploads/imagens'],
            'marketplace_hero_slide_2_image' => ['uploads/imagens/marketplace/hero', 'uploads/imagens/marketplace', 'uploads/imagens/frontend', 'uploads/imagens'],
            'marketplace_hero_slide_3_image' => ['uploads/imagens/marketplace/hero', 'uploads/imagens/marketplace', 'uploads/imagens/frontend', 'uploads/imagens'],
            'marketplace_exit_banner_image' => ['uploads/imagens/marketplace/exit', 'uploads/imagens/marketplace', 'uploads/imagens/frontend', 'uploads/imagens'],
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

    private function buildRecaptchaStatus(array $settings): array
    {
        $siteKey = trim((string) ($settings['recaptcha_v3_site_key'] ?? config('services.recaptcha.site_key', '')));
        $secretKey = trim((string) ($settings['recaptcha_v3_secret_key'] ?? config('services.recaptcha.v3_secret', '')));
        $minScore = trim((string) ($settings['recaptcha_v3_min_score'] ?? config('services.recaptcha.v3_min_score', 0.5)));
        $isConfigured = ($siteKey !== '' && $secretKey !== '');

        return [
            'is_configured' => $isConfigured,
            'site_key_masked' => $siteKey !== '' ? $this->maskSecret($siteKey, 8) : '-',
            'secret_key_masked' => $secretKey !== '' ? $this->maskSecret($secretKey, 6) : '-',
            'min_score' => $minScore !== '' ? $minScore : '0.5',
        ];
    }

    private function buildStorageStatus(array $settings): array
    {
        $uploadsDisk = trim((string) ($settings['uploads_storage_disk'] ?? config('uploads.disk', 'public')));
        if (!in_array($uploadsDisk, ['public', 's3'], true)) {
            $uploadsDisk = 'public';
        }

        $s3Key = trim((string) ($settings['s3_key'] ?? config('filesystems.disks.s3.key', '')));
        $s3Secret = trim((string) ($settings['s3_secret'] ?? config('filesystems.disks.s3.secret', '')));
        $s3Region = trim((string) ($settings['s3_region'] ?? config('filesystems.disks.s3.region', '')));
        $s3Bucket = trim((string) ($settings['s3_bucket'] ?? config('filesystems.disks.s3.bucket', '')));
        $s3Endpoint = trim((string) ($settings['s3_endpoint'] ?? config('filesystems.disks.s3.endpoint', '')));

        $s3Configured = ($s3Key !== '' && $s3Secret !== '' && $s3Region !== '' && $s3Bucket !== '');
        $isConfigured = $uploadsDisk === 'public' ? true : $s3Configured;

        $uploadedFiles = 0;
        $uploadedBytes = 0;
        $statsError = '';

        try {
            $disk = Storage::disk($uploadsDisk);
            $files = $disk->allFiles('uploads');
            $uploadedFiles = count($files);

            foreach ($files as $path) {
                try {
                    $uploadedBytes += (int) $disk->size($path);
                } catch (\Throwable $e) {
                    // ignora arquivo sem metadado de tamanho
                }
            }
        } catch (\Throwable $e) {
            $statsError = $e->getMessage();
        }

        return [
            'disk' => $uploadsDisk,
            'is_configured' => $isConfigured,
            's3_configured' => $s3Configured,
            's3_bucket' => $s3Bucket !== '' ? $s3Bucket : '-',
            's3_region' => $s3Region !== '' ? $s3Region : '-',
            's3_endpoint' => $s3Endpoint !== '' ? $s3Endpoint : '-',
            'uploaded_files' => $uploadedFiles,
            'uploaded_bytes' => $uploadedBytes,
            'uploaded_size_human' => $this->formatBytes($uploadedBytes),
            'stats_error' => $statsError,
        ];
    }

    private function buildUploadLimitsStatus(array $settings): array
    {
        $videoMaxMb = (int) ($settings['video_max_mb'] ?? config('uploads.video_max_mb', 1024));
        $documentMaxMb = (int) ($settings['document_max_mb'] ?? config('uploads.document_max_mb', 50));

        $allowedVideo = trim((string) ($settings['allowed_video_formats'] ?? ''));
        if ($allowedVideo === '') {
            $allowedVideo = implode(',', (array) config('uploads.allowed_video_formats', []));
        }

        $allowedDocument = trim((string) ($settings['allowed_document_formats'] ?? ''));
        if ($allowedDocument === '') {
            $allowedDocument = implode(',', (array) config('uploads.allowed_document_formats', []));
        }

        return [
            'video_max_mb' => $videoMaxMb,
            'document_max_mb' => $documentMaxMb,
            'allowed_video_formats' => $allowedVideo !== '' ? $allowedVideo : '-',
            'allowed_document_formats' => $allowedDocument !== '' ? $allowedDocument : '-',
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = max(0, min($power, count($units) - 1));
        $value = $bytes / (1024 ** $power);

        return number_format($value, $power === 0 ? 0 : 2, '.', '') . ' ' . $units[$power];
    }

    private function maskSecret(string $value, int $visible): string
    {
        $value = trim($value);
        if ($value === '') {
            return '-';
        }

        $len = strlen($value);
        if ($len <= $visible) {
            return str_repeat('*', $len);
        }

        $head = substr($value, 0, max(1, (int) floor($visible / 2)));
        $tail = substr($value, -max(1, (int) ceil($visible / 2)));

        return $head . str_repeat('*', max(4, $len - strlen($head) - strlen($tail))) . $tail;
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
        if ($encryption === 'null' || $encryption === '')
            $encryption = null;

        $config = [
            'transport' => 'smtp',
            'host' => trim($request->smtp_host),
            'port' => trim($request->smtp_port),
            'username' => trim($request->smtp_username),
            'password' => trim($request->smtp_password),
            'encryption' => $encryption,
            'timeout' => null,
            'auth_mode' => null,
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
            if (!$logo)
                $logo = Setting::where('key', 'logo_front')->value('value');
            if (!$logo)
                $logo = Setting::where('key', 'logo_image')->value('value');

            $logoUrl = $logo ? asset($logo) : asset('img/logo.svg');

            // Fetch Site Name from Database
            $siteName = Setting::where('key', 'app_name')->value('value');
            if (!$siteName)
                $siteName = Setting::where('key', 'company_name')->value('value');
            if (!$siteName)
                $siteName = config('app.name');

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
                                <div style="background: linear-gradient(135deg, ' . $primaryColor . ' 0%, ' . $secondaryColor . ' 100%); padding: 30px 20px; text-align: center;">
                                    <img src="' . $logoUrl . '" alt="' . $data['site']['name'] . '" style="max-height: 60px; max-width: 200px;">
                                </div>
                                
                                <!-- Body -->
                                <div style="padding: 30px; color: #333333; line-height: 1.6;">
                                    ' . $rendered . '
                                </div>
                                
                                <!-- Footer -->
                                <div style="background-color: #f8f9fa; padding: 20px; text-align: center; color: #777777; font-size: 12px; border-top: 1px solid #eeeeee;">
                                    <p>&copy; ' . date('Y') . ' ' . $data['site']['name'] . '. Todos os direitos reservados.</p>
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

    public function testGateway(Request $request)
    {
        try {
            $data = $request->validate([
                'gateway' => 'required|in:mercadopago,pagseguro',
                'env' => 'required|in:sandbox,production',
                'access_token' => 'nullable|string',
                'token' => 'nullable|string',
                'email' => 'nullable|email'
            ]);

            $success = false;
            $message = '';

            if ($data['gateway'] === 'mercadopago') {
                $token = $data['access_token'];
                if (!$token) {
                    throw new \Exception('Access Token não informado.');
                }

                // Teste MP: Get Stores ou User Info
                $response = Http::withToken($token)->get('https://api.mercadopago.com/users/me');

                if ($response->successful()) {
                    $json = $response->json();
                    $userName = $json['first_name'] . ' ' . $json['last_name'];
                    $success = true;
                    $message = "Conexão com Mercado Pago [{$data['env']}] realizada com sucesso! Usuário: {$userName}";
                } else {
                    $error = $response->json()['message'] ?? 'Erro desconhecido';
                    throw new \Exception("Falha na conexão MP: {$error}");
                }

            } elseif ($data['gateway'] === 'pagseguro') {
                $token = $data['token'];
                $email = $data['email'];

                if (!$token) {
                    throw new \Exception('Token não informado.');
                }

                // URL base depende do ambiente
                $baseUrl = $data['env'] === 'sandbox'
                    ? 'https://ws.sandbox.pagseguro.uol.com.br'
                    : 'https://ws.pagseguro.uol.com.br';

                // Teste PagSeguro V2 Session
                $response = Http::asForm()->post("{$baseUrl}/v2/sessions?email={$email}&token={$token}");

                if ($response->successful()) {
                    $success = true;
                    $message = "Conexão com PagSeguro [{$data['env']}] realizada com sucesso!";
                } else {
                    $body = $response->body();
                    if (str_contains($body, 'Unauthorized') || $response->status() == 401) {
                        throw new \Exception("Falha na autenticação PagSeguro. Verifique E-mail e Token.");
                    }
                    throw new \Exception("Falha na conexão PagSeguro. Status: " . $response->status());
                }
            }

            return response()->json(['success' => $success, 'message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

}
