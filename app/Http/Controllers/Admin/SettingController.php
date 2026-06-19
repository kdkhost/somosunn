<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLogService;
use App\Services\WatermarkService;
use App\Support\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;


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
            'storage',
            'system'
        ];

        if (!in_array($group, $allowedGroups)) {
            return redirect()->route('admin.settings', ['group' => 'general']);
        }

        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $settings = $this->normalizeFileSettings($settings);

        $getUrl = function ($key) use ($settings) {
            return \App\Models\Setting::getUrl($key);
        };

        if (request()->routeIs('panel.*')) {
            return view('panel.admin.settings.index', compact('settings', 'group', 'getUrl'));
        }

        return view('admin.settings.index', compact('settings', 'group', 'getUrl'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required',
        ]);

        $key = $request->input('key');
        $value = $request->input('value');

        // Tratamento especial: alterar APP_DEBUG no .env
        if ($key === 'toggle_env_debug') {
            $this->setEnvValue('APP_DEBUG', $value ? 'true' : 'false');
            \Artisan::call('config:clear');
            return response()->json(['success' => true, 'message' => 'Debug ' . ($value ? 'ativado' : 'desativado') . '.']);
        }

        Setting::set($key, (int) $value);

        return response()->json(['success' => true, 'message' => 'Configuração atualizada.']);
    }

    /**
     * Altera um valor no arquivo .env
     */
    private function setEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) return;

        $content = file_get_contents($envPath);
        $pattern = "/^{$key}=.*/m";

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, "{$key}={$value}", $content);
        } else {
            $content .= "\n{$key}={$value}\n";
        }

        file_put_contents($envPath, $content);
    }

    public function update(Request $request)
    {
        $currentGroup = $request->input('current_group', 'general');

        $data = $request->except([
            '_token',
            '_method',
            'current_group',
            // arquivos (tratados separadamente)
            'pwa_icon_192',
            'pwa_icon_512',
            'pwa_splash',
            'pwa_banner',
            'preloader_image',
            'logo_image',
            'logo_somos_unicas',
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
            'remove_logo_somos_unicas',
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
            'marketplace_hero_slide_1_image_mobile',
            'marketplace_hero_slide_2_image',
            'marketplace_hero_slide_2_image_mobile',
            'marketplace_hero_slide_3_image',
            'marketplace_hero_slide_3_image_mobile',
            'marketplace_exit_banner_image',
            // Marketplace remove flags (tratados separadamente)
            'remove_marketplace_hero_slide_1_image',
            'remove_marketplace_hero_slide_1_image_mobile',
            'remove_marketplace_hero_slide_2_image',
            'remove_marketplace_hero_slide_2_image_mobile',
            'remove_marketplace_hero_slide_3_image',
            'remove_marketplace_hero_slide_3_image_mobile',
            'remove_marketplace_exit_banner_image',
        ]);

        if ($request->has('ads_code_html_encoded')) {
            $data['ads_code_html'] = $this->decodeEncodedSettingField($request->input('ads_code_html_encoded'));
            unset($data['ads_code_html_encoded']);
        }

        if ($request->has('ads_inter_feed_code_encoded')) {
            $data['ads_inter_feed_code'] = $this->decodeEncodedSettingField($request->input('ads_inter_feed_code_encoded'));
            unset($data['ads_inter_feed_code_encoded']);
        }

        if ($request->hasFile('seo_og_image') && $this->imageIsSmallerThan($request->file('seo_og_image'), 1200, 630)) {
            return response()->json(['message' => 'A imagem OpenGraph precisa ter pelo menos 1200x630px.'], 422);
        }
        if ($request->hasFile('seo_twitter_image') && $this->imageIsSmallerThan($request->file('seo_twitter_image'), 1200, 628)) {
            return response()->json(['message' => 'A imagem do Twitter precisa ter pelo menos 1200x628px.'], 422);
        }

        if ($request->hasFile('watermark_image') && !$this->watermarkFileIsSupported($request->file('watermark_image'))) {
            return response()->json(['message' => 'A marca dagua deve ser PNG ou WEBP com fundo transparente.'], 422);
        }

        $plyrOptionsJson = trim((string) $request->input('video_plyr_options_json', ''));
        if ($plyrOptionsJson !== '') {
            try {
                json_decode($plyrOptionsJson, true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable $e) {
                return response()->json(['message' => 'Opcoes avancadas do Plyr: JSON invalido.'], 422);
            }
        }

        if ($currentGroup === 'marketplace') {
            $seller = (float) ($data['marketplace_split_seller_percent'] ?? 0);
            $platform = (float) ($data['marketplace_split_platform_percent'] ?? 0);
            $traffic = (float) ($data['marketplace_split_traffic_percent'] ?? 0);
            $superadmin = (float) ($data['marketplace_split_superadmin_percent'] ?? 0);

            if (round($seller + $platform + $traffic + $superadmin, 2) !== 100.00) {
                return response()->json(['message' => 'A soma dos percentuais de split deve ser exatamente 100%.'], 422);
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
            $this->preparePublicDir($dir);
        }

        $removals = [
            'pwa_icon_192',
            'pwa_icon_512',
            'pwa_splash',
            'pwa_banner',
            'preloader_image',
            'logo_image',
            'logo_somos_unicas',
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
            $this->replaceFile('pwa_icon_192', $this->storeUploadedPublicFile($request->file('pwa_icon_192'), 'uploads/imagens/pwa'));
        }
        if ($request->hasFile('pwa_icon_512')) {
            $this->replaceFile('pwa_icon_512', $this->storeUploadedPublicFile($request->file('pwa_icon_512'), 'uploads/imagens/pwa'));
        }
        if ($request->hasFile('pwa_splash')) {
            $this->replaceFile('pwa_splash', $this->storeUploadedPublicFile($request->file('pwa_splash'), 'uploads/imagens/pwa'));
        }
        if ($request->hasFile('pwa_banner')) {
            $this->replaceFile('pwa_banner', $this->storeUploadedPublicFile($request->file('pwa_banner'), 'uploads/imagens/pwa'));
        }
        if ($request->hasFile('preloader_image')) {
            $this->replaceFile('preloader_image', $this->storeUploadedPublicFile($request->file('preloader_image'), 'uploads/imagens/preloader'));
        }
        if ($request->hasFile('logo_image')) {
            $this->replaceFile('logo_image', $this->storeUploadedPublicFile($request->file('logo_image'), 'uploads/imagens/geral'));
        }
        if ($request->hasFile('logo_somos_unicas')) {
            $this->replaceFile('logo_somos_unicas', $this->storeUploadedPublicFile($request->file('logo_somos_unicas'), 'uploads/imagens/geral'));
        }
        if ($request->hasFile('favicon_image')) {
            $this->replaceFile('favicon_image', $this->storeUploadedPublicFile($request->file('favicon_image'), 'uploads/imagens/geral'));
        }
        if ($request->hasFile('logo_admin')) {
            $this->replaceFile('logo_admin', $this->storeUploadedPublicFile($request->file('logo_admin'), 'uploads/imagens/administrativo'));
        }
        if ($request->hasFile('logo_auth')) {
            $this->replaceFile('logo_auth', $this->storeUploadedPublicFile($request->file('logo_auth'), 'uploads/imagens/logins'));
        }
        if ($request->hasFile('logo_front')) {
            $this->replaceFile('logo_front', $this->storeUploadedPublicFile($request->file('logo_front'), 'uploads/imagens/frontend'));
        }
        if ($request->hasFile('watermark_image')) {
            $this->replaceFile('watermark_image', $this->storeUploadedPublicFile($request->file('watermark_image'), 'uploads/imagens/watermark'));
        }
        if ($request->hasFile('hero_image')) {
            $this->replaceFile('hero_image', $this->storeUploadedPublicFile($request->file('hero_image'), 'uploads/imagens/frontend'));
        }
        if ($request->hasFile('site_bg_image')) {
            $this->replaceFile('site_bg_image', $this->storeUploadedPublicFile($request->file('site_bg_image'), 'uploads/imagens/frontend'));
        }
        if ($request->hasFile('seo_og_image')) {
            $this->replaceFile('seo_og_image', $this->storeUploadedPublicFile($request->file('seo_og_image'), 'uploads/imagens/seo'));
        }
        if ($request->hasFile('seo_twitter_image')) {
            $this->replaceFile('seo_twitter_image', $this->storeUploadedPublicFile($request->file('seo_twitter_image'), 'uploads/imagens/seo'));
        }
        if ($request->hasFile('marketplace_hero_slide_1_image')) {
            $this->replaceFile('marketplace_hero_slide_1_image', $this->storeUploadedPublicFile($request->file('marketplace_hero_slide_1_image'), 'uploads/imagens/marketplace/hero'));
        }
        if ($request->hasFile('marketplace_hero_slide_1_image_mobile')) {
            $this->replaceFile('marketplace_hero_slide_1_image_mobile', $this->storeUploadedPublicFile($request->file('marketplace_hero_slide_1_image_mobile'), 'uploads/imagens/marketplace/hero'));
        }
        if ($request->hasFile('marketplace_hero_slide_2_image')) {
            $this->replaceFile('marketplace_hero_slide_2_image', $this->storeUploadedPublicFile($request->file('marketplace_hero_slide_2_image'), 'uploads/imagens/marketplace/hero'));
        }
        if ($request->hasFile('marketplace_hero_slide_2_image_mobile')) {
            $this->replaceFile('marketplace_hero_slide_2_image_mobile', $this->storeUploadedPublicFile($request->file('marketplace_hero_slide_2_image_mobile'), 'uploads/imagens/marketplace/hero'));
        }
        if ($request->hasFile('marketplace_hero_slide_3_image')) {
            $this->replaceFile('marketplace_hero_slide_3_image', $this->storeUploadedPublicFile($request->file('marketplace_hero_slide_3_image'), 'uploads/imagens/marketplace/hero'));
        }
        if ($request->hasFile('marketplace_hero_slide_3_image_mobile')) {
            $this->replaceFile('marketplace_hero_slide_3_image_mobile', $this->storeUploadedPublicFile($request->file('marketplace_hero_slide_3_image_mobile'), 'uploads/imagens/marketplace/hero'));
        }
        if ($request->hasFile('marketplace_exit_banner_image')) {
            $this->replaceFile('marketplace_exit_banner_image', $this->storeUploadedPublicFile($request->file('marketplace_exit_banner_image'), 'uploads/imagens/marketplace/exit'));
        }

        // Mapeamento de booleanos por grupo para garantir que desativar (unchecked) funcione
        $groupBools = [
            'pwa' => ['pwa_enabled', 'pwa_prompt_enabled'],
            'appearance' => ['preloader_enabled'],
            'player' => [
                'video_player_enabled',
                'image_watermark_enabled',
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
                'mercadopago_enabled',
                'mercadopago_method_credit_card',
                'mercadopago_method_pix',
                'mercadopago_method_ticket',
                'sumup_enabled',
                'sumup_method_card',
                'sumup_method_pix',
                'sumup_pass_fee',
            ],            'marketplace' => ['marketplace_hero_enabled', 'marketplace_hero_autoplay', 'marketplace_exit_enabled', 'marketplace_events_popup_enabled'],
            'social' => [
                'social_login_enabled',
                'social_google_enabled',
                'social_facebook_enabled',
                'social_twitter_enabled',
                'social_linkedin_enabled',
            ],
            'smtp' => [
                'email_queue_schedule_enabled',
            ],
            'storage' => [
                'storage_path_style',
            ],
            'system' => [
                'maintenance_enabled',
                'maintenance_auto_enabled',
            ],
        ];

        $currentGroup = $request->input('current_group', 'general');
        if (isset($groupBools[$currentGroup])) {
            foreach ($groupBools[$currentGroup] as $b) {
                // Check value explicitly to handle '0', 'false', 'off' sent by JS or hidden inputs
                $inputVal = $request->input($b);

                if (!is_null($inputVal)) {
                    $data[$b] = filter_var($inputVal, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
                } else {
                    // If missing (standard unchecked checkbox), assume 0
                    $data[$b] = 0;
                }
            }
        }

        // Mantem compatibilidade entre chaves antigas (app_*) e novas (client_*) do Facebook.
        $facebookClientId = trim((string) ($data['social_facebook_client_id'] ?? ''));
        $facebookAppId = trim((string) ($data['social_facebook_app_id'] ?? ''));
        if (array_key_exists('social_facebook_client_id', $data) || array_key_exists('social_facebook_app_id', $data)) {
            $resolvedFacebookClientId = $facebookClientId !== '' ? $facebookClientId : $facebookAppId;
            $data['social_facebook_client_id'] = $resolvedFacebookClientId;
            $data['social_facebook_app_id'] = $resolvedFacebookClientId;
        }

        $facebookClientSecret = trim((string) ($data['social_facebook_client_secret'] ?? ''));
        $facebookAppSecret = trim((string) ($data['social_facebook_app_secret'] ?? ''));
        if (array_key_exists('social_facebook_client_secret', $data) || array_key_exists('social_facebook_app_secret', $data)) {
            $resolvedFacebookClientSecret = $facebookClientSecret !== '' ? $facebookClientSecret : $facebookAppSecret;
            $data['social_facebook_client_secret'] = $resolvedFacebookClientSecret;
            $data['social_facebook_app_secret'] = $resolvedFacebookClientSecret;
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
            'image_watermark_opacity' => ['min' => 5, 'max' => 100],
            'image_watermark_size_percent' => ['min' => 1, 'max' => 60],
            'image_watermark_margin' => ['min' => 0, 'max' => 300],
            'video_plyr_speed_selected' => ['min' => 0, 'max' => 10],
            'video_watermark_size_percent' => ['min' => 1, 'max' => 100],
            'video_watermark_margin' => ['min' => 0, 'max' => 200],
            'video_watermark_rotate' => ['min' => -180, 'max' => 180],
            // SumUp parcelamento
            'sumup_max_installments'        => ['min' => 1, 'max' => 12],
            'sumup_installments_no_interest' => ['min' => 1, 'max' => 12],
            // MercadoPago parcelamento
            'mercadopago_max_installments'        => ['min' => 1, 'max' => 12],
            'mercadopago_installments_no_interest' => ['min' => 1, 'max' => 12],
            // Expiração do PIX
            'mercadopago_pix_expiration_minutes' => ['min' => 1, 'max' => 1440],
            'sumup_pix_expiration_minutes'       => ['min' => 1, 'max' => 1440],
        ] as $key => $limits) {            if (!array_key_exists($key, $data)) {
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

        foreach (['video_plyr_volume', 'video_watermark_opacity'] as $key) {            if (!array_key_exists($key, $data)) {
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

        // Validação de taxa de parcelamento SumUp (0.00 a 99.99%)
        if (array_key_exists('sumup_installment_tax', $data)) {
            $raw = trim(str_replace(',', '.', (string) $data['sumup_installment_tax']));
            if ($raw === '') {
                $data['sumup_installment_tax'] = '0.00';
            } else {
                $value = (float) $raw;
                $value = max(0.0, min(99.99, $value));
                $data['sumup_installment_tax'] = number_format($value, 2, '.', '');
            }
        }

        // Validação de taxa de parcelamento MercadoPago (0.00 a 99.99%)
        if (array_key_exists('mercadopago_installment_tax', $data)) {
            $raw = trim(str_replace(',', '.', (string) $data['mercadopago_installment_tax']));
            if ($raw === '') {
                $data['mercadopago_installment_tax'] = '0.00';
            } else {
                $value = (float) $raw;
                $value = max(0.0, min(99.99, $value));
                $data['mercadopago_installment_tax'] = number_format($value, 2, '.', '');
            }
        }

        if (array_key_exists('video_watermark_position', $data)) {
            $allowed = ['top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'];
            $value = trim((string) $data['video_watermark_position']);
            $data['video_watermark_position'] = in_array($value, $allowed, true) ? $value : 'top-right';
        }

        if (array_key_exists('image_watermark_position', $data)) {
            $allowed = ['top-left', 'top-right', 'bottom-left', 'bottom-right', 'center'];
            $value = trim((string) $data['image_watermark_position']);
            $data['image_watermark_position'] = in_array($value, $allowed, true) ? $value : 'bottom-right';
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
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_encryption',
            'smtp_from_email',
            'smtp_from_name',
            'smtp_cc',
            'smtp_bcc',
        ] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            $data[$key] = trim((string) $data[$key]);
        }

        if (array_key_exists('email_dispatch_mode', $data)) {
            $mode = trim((string) $data['email_dispatch_mode']);
            $data['email_dispatch_mode'] = in_array($mode, ['sync', 'queue'], true) ? $mode : 'sync';
        }

        if (array_key_exists('email_queue_connection', $data)) {
            $connection = trim((string) $data['email_queue_connection']);
            $allowedConnections = array_keys((array) config('queue.connections', []));
            $data['email_queue_connection'] = in_array($connection, $allowedConnections, true) ? $connection : 'database';
        }

        if (array_key_exists('email_queue_name', $data)) {
            $queueName = preg_replace('/[^a-zA-Z0-9_\-]/', '', trim((string) $data['email_queue_name'])) ?: '';
            $data['email_queue_name'] = $queueName !== '' ? $queueName : 'emails';
        }

        foreach ([
            'email_queue_delay_seconds' => ['min' => 0, 'max' => 3600],
            'email_queue_tries' => ['min' => 1, 'max' => 10],
            'email_queue_timeout' => ['min' => 30, 'max' => 900],
            'email_queue_sleep' => ['min' => 1, 'max' => 10],
        ] as $key => $limits) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $raw = trim((string) $data[$key]);
            if ($raw === '') {
                $data[$key] = (string) $limits['min'];
                continue;
            }

            $value = (int) $raw;
            $value = max((int) $limits['min'], min((int) $limits['max'], $value));
            $data[$key] = (string) $value;
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

        foreach ([
            'maintenance_title',
            'maintenance_subtitle',
            'maintenance_message',
            'maintenance_button_label',
            'maintenance_button_url',
            'maintenance_contact_email',
        ] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $data[$key] = trim((string) $data[$key]);
        }

        if (array_key_exists('maintenance_contact_email', $data)) {
            if ($data['maintenance_contact_email'] !== '' && !filter_var($data['maintenance_contact_email'], FILTER_VALIDATE_EMAIL)) {
                $data['maintenance_contact_email'] = '';
            }
        }

        foreach (['maintenance_start_at', 'maintenance_end_at'] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $raw = trim((string) $data[$key]);
            if ($raw === '') {
                $data[$key] = '';
                continue;
            }

            try {
                $data[$key] = \Carbon\Carbon::parse($raw)->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                $data[$key] = '';
            }
        }
        $data['uploads_storage_disk'] = 'public';
        foreach (['s3_key', 's3_secret', 's3_region', 's3_bucket', 's3_url', 's3_endpoint'] as $key) {
            $data[$key] = '';
        }
        $data['s3_path_style'] = 0;

        // Garantir que checkboxes de meios de pagamento sejam salvos como 0/1
        $paymentMethodCheckboxes = [
            'mercadopago_enabled',
            'mercadopago_method_credit_card',
            'mercadopago_method_debit_card',
            'mercadopago_method_pix',
            'mercadopago_method_ticket',
            'mercadopago_method_mercadopago',
            // MercadoPago - permissões
            'mercadopago_allow_members',
            'mercadopago_allow_instructors',
            'mercadopago_allow_sellers',
            'mercadopago_allow_mentors',
            'mercadopago_allow_courses',
            'mercadopago_allow_mentorships',
            'mercadopago_allow_events',
            'mercadopago_allow_marketplace',
            'mercadopago_allow_subscriptions',
            'mercadopago_allow_services',
            // SumUp - checkboxes
            'sumup_enabled',
            'sumup_method_card',
            'sumup_method_pix',
            'sumup_allow_members',
            'sumup_allow_instructors',
            'sumup_allow_sellers',
            'sumup_allow_mentors',
            'sumup_allow_courses',
            'sumup_allow_mentorships',
            'sumup_allow_events',
            'sumup_allow_marketplace',
            'sumup_allow_subscriptions',
            'sumup_allow_services',
            'sumup_fallback_to_mercadopago',
        ];

        if ($currentGroup === 'gateway') {
            foreach ($paymentMethodCheckboxes as $checkbox) {
                // Usar o valor real (último) em vez de has(), pois o hidden input
                // com value=0 + checkbox com value=1 sempre faz has() retornar true
                $value = $request->input($checkbox);
                // Pode vir como array se houver hidden+checkbox — pegar último
                if (is_array($value)) {
                    $value = end($value);
                }
                $data[$checkbox] = ((int) $value === 1) ? 1 : 0;
            }

            // VALIDAÇÃO: cada gateway ativo deve ter ao menos um método de pagamento ativo
            $mpEnabled = (int) ($data['mercadopago_enabled'] ?? 0) === 1;
            $sumupEnabled = (int) ($data['sumup_enabled'] ?? 0) === 1;

            if ($mpEnabled) {
                $mpMethodsActive = (int) ($data['mercadopago_method_credit_card'] ?? 0)
                    + (int) ($data['mercadopago_method_debit_card'] ?? 0)
                    + (int) ($data['mercadopago_method_pix'] ?? 0)
                    + (int) ($data['mercadopago_method_ticket'] ?? 0)
                    + (int) ($data['mercadopago_method_mercadopago'] ?? 0);

                if ($mpMethodsActive === 0) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['message' => 'Ao menos um método de pagamento deve permanecer ativo para este gateway.'], 422);
                    }
                    return redirect()->back()
                        ->with('error', 'Ao menos um método de pagamento deve permanecer ativo para este gateway.')
                        ->withInput();
                }
            }

            if ($sumupEnabled) {
                $sumupMethodsActive = (int) ($data['sumup_method_card'] ?? 0)
                    + (int) ($data['sumup_method_pix'] ?? 0);

                if ($sumupMethodsActive === 0) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['message' => 'Ao menos um método de pagamento deve permanecer ativo para este gateway.'], 422);
                    }
                    return redirect()->back()
                        ->with('error', 'Ao menos um método de pagamento deve permanecer ativo para este gateway.')
                        ->withInput();
                }
            }

            // Remover chaves ANTIGAS e lixo que não pertencem ao gateway
            $trashKeys = ['video_plyr_options_json', 'gateway_checkout_theme_selected', 'gateway_checkout_primary_color_hex'];
            foreach ($trashKeys as $trash) {
                if (isset($data[$trash])) {
                    unset($data[$trash]);
                }
            }

            \Log::info('[SETTINGS DEBUG] Gateway settings updated. MP enabled=' . ($mpEnabled ? 1 : 0) . ' SumUp enabled=' . ($sumupEnabled ? 1 : 0));
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value ?? '']);
        }

        // Spec: multi-provider-s3-storage
        // Se o request veio do form de Armazenamento (Tailwind/AdminLTE)
        // com chaves storage_* sem prefixo, copia para o namespace do
        // provedor ativo (idrive_*/wasabi_*/aws_*) para manter compatibilidade
        // com a leitura via StorageProviderRegistry.
        //
        // Importante: usamos $request->has() em vez de array_key_exists($data)
        // porque o bloco $groupBools acima sintetiza valores 0 para checkboxes
        // ausentes no payload. Sem este cuidado, um form prefixado puro
        // (que submete apenas {provider}_path_style) seria sobrescrito por um
        // storage_path_style=0 sintetizado, perdendo o valor real do usuario.
        if (
            $currentGroup === 'storage'
            && isset($data['storage_active_provider'])
            && in_array($data['storage_active_provider'], ['idrive', 'wasabi', 'aws'], true)
        ) {
            $providerKey = $data['storage_active_provider'];
            $legacyToPrefixed = [
                'storage_access_key' => $providerKey . '_access_key',
                'storage_secret_key' => $providerKey . '_secret_key',
                'storage_bucket'     => $providerKey . '_bucket',
                'storage_region'     => $providerKey . '_region',
                'storage_endpoint'   => $providerKey . '_endpoint',
                'storage_url'        => $providerKey . '_url',
                'storage_path_style' => $providerKey . '_path_style',
            ];
            foreach ($legacyToPrefixed as $legacyKey => $prefixedKey) {
                // Auto-copia apenas se a chave legada veio NO REQUEST (nao
                // do bloco de groupBools sintetizado) E a prefixada NAO veio
                // (o usuario nao quer sobrescrever a propria entrada).
                $legacyInRequest = $request->has($legacyKey);
                $prefixedInRequest = $request->has($prefixedKey);

                if ($legacyInRequest && !$prefixedInRequest) {
                    $val = (string) $request->input($legacyKey, '');
                    Setting::updateOrCreate(
                        ['key' => $prefixedKey],
                        ['value' => $val]
                    );
                }
            }
            // Garante o flush do cache estatico do Setting para que a proxima
            // chamada a UploadStorage::applyRuntimeConfig veja os valores novos.
            Setting::flushRuntimeCache();
        }

        // DEBUG: Confirmar o que foi salvo
        if ($currentGroup === 'gateway') {
            $savedTheme = Setting::where('key', 'gateway_checkout_theme')->value('value');
            $savedColor = Setting::where('key', 'gateway_checkout_primary_color')->value('value');
            \Log::info('[SETTINGS DEBUG] Apos salvar - theme: ' . ($savedTheme ?? 'NULL') . ' | color: ' . ($savedColor ?? 'NULL'));
        }

        // Se o Admin estiver salvando chaves globais do Mercado Pago, sincronizar com o gateway_account dele
        // para manter consistência entre o painel AdminLTE e o Marketplace
        if ($currentGroup === 'gateway') {
            $userId = Auth::id();

            // Determinar qual ambiente está ativo para pegar as chaves corretas
            $mpEnv = $data['mercadopago_env'] ?? Setting::where('key', 'mercadopago_env')->value('value') ?? 'sandbox';
            $prefix = $mpEnv === 'production' ? 'mercadopago_prod_' : 'mercadopago_sandbox_';

            $publicKey = $data[$prefix . 'public_key'] ?? null;
            $accessToken = $data[$prefix . 'access_token'] ?? null;

            $mpAccount = \App\Models\GatewayAccount::firstOrNew(['user_id' => $userId, 'provider' => 'mercadopago']);

            if (!empty($publicKey))
                $mpAccount->public_key = $publicKey;
            if (!empty($accessToken))
                $mpAccount->access_token = $accessToken;

            // Ativar/desativar baseado na configuração global
            $mpEnabled = isset($data['mercadopago_enabled']) ? (int) $data['mercadopago_enabled'] : 
                         (int) (Setting::where('key', 'mercadopago_enabled')->value('value') ?? 1);
            
            if ($mpAccount->public_key && $mpAccount->access_token) {
                $mpAccount->enabled = $mpEnabled === 1;
            } else {
                $mpAccount->enabled = false;
            }

            $mpAccount->save();
        }

        // Sincronizar credenciais SumUp com gateway_account (mesmo padrão do MercadoPago)
        if ($currentGroup === 'gateway') {
            $userId = Auth::id();

            $sumupApiKey      = $data['sumup_api_key'] ?? null;
            $sumupMerchantCode = $data['sumup_merchant_code'] ?? null;

            $sumupAccount = \App\Models\GatewayAccount::firstOrNew(['user_id' => $userId, 'provider' => 'sumup']);

            if (!empty($sumupApiKey))
                $sumupAccount->access_token = $sumupApiKey;

            $extra = $sumupAccount->extra ?? [];
            if (!empty($sumupMerchantCode))
                $extra['merchant_code'] = $sumupMerchantCode;
            $sumupAccount->extra = $extra;

            // Ativar/desativar baseado na configuração global
            $sumupEnabled = isset($data['sumup_enabled']) ? (int) $data['sumup_enabled'] : 
                            (int) (Setting::where('key', 'sumup_enabled')->value('value') ?? 0);

            if ($sumupAccount->access_token) {
                $sumupAccount->enabled = $sumupEnabled === 1;
            } else {
                $sumupAccount->enabled = false;
            }

            $sumupAccount->save();
        }

        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
        } catch (\Throwable $e) {
            \Log::error('Erro ao limpar cache nas configurações: ' . $e->getMessage());
        }

        // Audit log: alteração de configurações
        try {
            app(AuditLogService::class)->log(
                AuditLogService::ACTION_CONFIG_CHANGE,
                null,
                [],
                [],
                ['group' => $currentGroup]
            );
        } catch (\Throwable $e) { /* silent: audit nunca quebra o save */ }

        // Auto-save: Retornar JSON se for requisição AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Configurações salvas com sucesso.']);
        }

        // Form submit normal: redirecionar com flash message
        return redirect()->back()->with('success', 'Configurações salvas com sucesso.');

        try {
            Storage::disk('public')->putFileAs($relativeDir, $file, $name, ['visibility' => 'public']);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Não foi possível salvar a imagem em ' . $relativeDir . ': ' . $e->getMessage(), 0, $e);
        }

        return $relativeDir . '/' . $name;
    }

    private function replaceFile($key, $newPath)
    {
        $this->removeFile($key, false);
        Setting::updateOrCreate(['key' => $key], ['value' => $newPath]);
    }

    private function removeFile($key, bool $clearSetting = true)
    {
        $old = Setting::where('key', $key)->value('value');
        if (!$old) {
            if ($clearSetting) {
                Setting::updateOrCreate(['key' => $key], ['value' => '']);
            }
            return;
        }

        $old = trim((string) $old);
        if ($old === '') {
            if ($clearSetting) {
                Setting::updateOrCreate(['key' => $key], ['value' => '']);
            }
            return;
        }

        UploadStorage::delete($old);
        if ($clearSetting) {
            Setting::updateOrCreate(['key' => $key], ['value' => '']);
        }
    }

    private function ensurePublicDir($dir)
    {
        if (UploadStorage::isLocal()) {
            Storage::disk('public')->makeDirectory($dir);
        }
    }

    private function normalizeFileSettings(array $settings): array
    {
        $keyDirs = [
            'preloader_image' => ['uploads/imagens/preloader', 'uploads/imagens/geral', 'uploads/imagens'],
            'logo_image' => ['uploads/imagens/geral', 'uploads/imagens'],
            'logo_somos_unicas' => ['uploads/imagens/geral', 'uploads/imagens'],
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
            'marketplace_hero_slide_1_image_mobile' => ['uploads/imagens/marketplace/hero', 'uploads/imagens/marketplace', 'uploads/imagens'],
            'marketplace_hero_slide_2_image' => ['uploads/imagens/marketplace/hero', 'uploads/imagens/marketplace', 'uploads/imagens/frontend', 'uploads/imagens'],
            'marketplace_hero_slide_2_image_mobile' => ['uploads/imagens/marketplace/hero', 'uploads/imagens/marketplace', 'uploads/imagens'],
            'marketplace_hero_slide_3_image' => ['uploads/imagens/marketplace/hero', 'uploads/imagens/marketplace', 'uploads/imagens/frontend', 'uploads/imagens'],
            'marketplace_hero_slide_3_image_mobile' => ['uploads/imagens/marketplace/hero', 'uploads/imagens/marketplace', 'uploads/imagens'],
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

            if (UploadStorage::exists($value) || file_exists(public_path($value))) {
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

    private function watermarkFileIsSupported($file): bool
    {
        if (!$file || !$file->isValid()) {
            return false;
        }

        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension() ?: ''));

        if (!in_array($extension, ['png', 'webp'], true)) {
            return false;
        }

        return app(WatermarkService::class)->isTransparentWatermarkFile($file);
    }

    private function decodeEncodedSettingField(mixed $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $normalized = strtr($value, '-_', '+/');
        $padding = strlen($normalized) % 4;
        if ($padding > 0) {
            $normalized .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($normalized, true);

        return $decoded === false ? (string) $value : $decoded;
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
        $uploadsDisk = 'public';
        $isConfigured = true;

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
            's3_configured' => false,
            's3_bucket' => '-',
            's3_region' => '-',
            's3_endpoint' => '-',
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
            $logo = Setting::where('key', 'logo_admin')->value('value');
            if (!$logo)
                $logo = Setting::where('key', 'logo_front')->value('value');
            if (!$logo)
                $logo = Setting::where('key', 'logo_image')->value('value');

            $logoUrl = $logo
                ? Setting::getUrl('logo_admin', Setting::getUrl('logo_front', Setting::getUrl('logo_image', asset('img/logo.svg'))))
                : asset('img/logo.svg');

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

            app(\App\Services\Mail\SystemMailTemplateService::class)->send('smtp_test', $request->smtp_test_email, $data, [
                'name' => 'Teste de Configuração SMTP',
                'category' => 'sistema',
                'subject' => 'Teste de Envio SMTP - {{site.name}}',
                'body' => '<h1>Olá, {{user.name}}!</h1><p>Este é um e-mail de teste para validar as configurações SMTP de <strong>{{site.name}}</strong>.</p>',
                'is_active' => true,
                'locale' => 'pt-BR',
            ]);

            return response()->json(['success' => true, 'message' => 'E-mail de teste enviado com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao enviar e-mail: ' . $e->getMessage()], 500);
        }
    }

    public function testGateway(Request $request)
    {
        try {
            $data = $request->validate([
                'gateway'      => 'required|in:mercadopago,sumup',
                'env'          => 'nullable|in:sandbox,production',
                'access_token' => 'nullable|string',
                'token'        => 'nullable|string',
                'email'        => 'nullable|email',
            ]);

            $success = false;
            $message = '';

            if ($data['gateway'] === 'mercadopago') {
                $token = $data['access_token'];
                if (!$token) {
                    throw new \Exception('Access Token não informado.');
                }

                $response = Http::withToken($token)->get('https://api.mercadopago.com/users/me');

                if ($response->successful()) {
                    $json     = $response->json();
                    $userName = $json['first_name'] . ' ' . $json['last_name'];
                    $success  = true;
                    $message  = "Conexão com Mercado Pago [{$data['env']}] realizada com sucesso! Usuário: {$userName}";
                } else {
                    $error = $response->json()['message'] ?? 'Erro desconhecido';
                    throw new \Exception("Falha na conexão MP: {$error}");
                }
            }

            if ($data['gateway'] === 'sumup') {
                $apiKey = $data['access_token'];
                if (!$apiKey) {
                    throw new \Exception('API Key SumUp não informada.');
                }

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept'        => 'application/json',
                ])->get('https://api.sumup.com/v0.1/me');

                if ($response->successful()) {
                    $json        = $response->json();
                    $merchantCode = $json['merchant_profile']['merchant_code'] ?? $json['username'] ?? 'N/A';
                    $success     = true;
                    $message     = "Conexão com SumUp realizada com sucesso! Merchant Code: {$merchantCode}";
                } else {
                    $error = $response->json()['message'] ?? $response->json()['error_message'] ?? 'Erro desconhecido';
                    throw new \Exception("Falha na conexão SumUp: {$error}");
                }
            }

            return response()->json(['success' => $success, 'message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function preparePublicDir(string $dir): void
    {
        if (!UploadStorage::isLocal()) {
            $this->ensurePublicDir($dir);
            return;
        }

        $targetDir = public_path(trim(str_replace('\\', '/', $dir), '/'));

        if (!is_dir($targetDir) && !@mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new \RuntimeException('Nao foi possivel preparar o diretorio ' . $dir . ' para upload.');
        }
    }

    private function storeUploadedPublicFile($file, string $relativeDir): string
    {
        if (!UploadStorage::isLocal()) {
            return $this->storePublic($file, $relativeDir);
        }

        if (!$file || !$file->isValid()) {
            throw new \RuntimeException('Arquivo de imagem invalido ou corrompido.');
        }

        $relativeDir = trim(str_replace('\\', '/', $relativeDir), '/');
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $name = uniqid('', true) . '.' . $ext;

        $this->preparePublicDir($relativeDir);

        try {
            $file->move(public_path($relativeDir), $name);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Nao foi possivel salvar a imagem em ' . $relativeDir . ': ' . $e->getMessage(), 0, $e);
        }

        return $relativeDir . '/' . $name;
    }

    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240', // 10MB max
                'key' => 'required|string',
            ]);

            $file = $request->file('file');
            $key = $request->input('key');

            if ($key === 'watermark_image' && !$this->watermarkFileIsSupported($file)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Use um arquivo PNG ou WEBP com fundo transparente para a marca d\'agua.'
                ], 422);
            }

            // Define destination folder by setting key
            $directory = 'uploads/imagens/geral';
            if (in_array($key, ['hero_image', 'site_bg_image', 'logo_front'], true)) {
                $directory = 'uploads/imagens/frontend';
            }
            if (str_contains($key, 'marketplace'))
                $directory = 'uploads/imagens/marketplace';
            if (str_contains($key, 'marketplace_hero'))
                $directory = 'uploads/imagens/marketplace/hero';
            if (str_contains($key, 'marketplace_exit'))
                $directory = 'uploads/imagens/marketplace/exit';
            if (str_contains($key, 'pwa'))
                $directory = 'uploads/imagens/pwa';

            $this->preparePublicDir($directory);

            // Generate filename
            $path = $this->storeUploadedPublicFile($file, $directory);

            // Remove old file if exists
            $this->removeFile($key, false);

            // Update Database
            Setting::updateOrCreate(['key' => $key], ['value' => $path]);

            return response()->json([
                'success' => true,
                'path' => UploadStorage::url($path),
                'message' => 'Arquivo enviado com sucesso!'
            ]);

        } catch (\Throwable $e) {
            \Log::error('Settings Upload Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testa a conexao S3 usando as configuracoes salvas no banco.
     */
    public function testS3(Request $request)
    {
        $results = [];

        try {
            $key = Setting::get('storage_access_key', '');
            $secret = Setting::get('storage_secret_key', '');
            $region = Setting::get('storage_region', 'us-east-1') ?: 'us-east-1';
            $bucket = Setting::get('storage_bucket', '');
            $endpoint = Setting::get('storage_endpoint', '');
            $url = Setting::get('storage_url', '');
            $pathStyle = (bool) Setting::get('storage_path_style', 1);

            if (empty($key) || empty($secret) || empty($bucket)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Preencha ao menos Access Key, Secret Key e Bucket antes de testar.',
                    'results' => [],
                ]);
            }

            // Configurar disco S3 em runtime
            config([
                'filesystems.disks.s3_test' => [
                    'driver' => 's3',
                    'key' => $key,
                    'secret' => $secret,
                    'region' => $region,
                    'bucket' => $bucket,
                    'url' => $url ?: null,
                    'endpoint' => $endpoint ?: null,
                    'use_path_style_endpoint' => $pathStyle,
                    'visibility' => 'public',
                    'throw' => true,
                ],
            ]);

            $disk = Storage::disk('s3_test');
            $testFile = '_somos_unn_test_' . time() . '.txt';
            $testContent = 'SOMOS UNN S3 Test - ' . now()->toDateTimeString();

            // 1. Upload
            $disk->put($testFile, $testContent);
            $results[] = ['step' => 'Upload', 'status' => 'ok', 'detail' => $testFile];

            // 2. Exists
            $exists = $disk->exists($testFile);
            $results[] = ['step' => 'Verificar existencia', 'status' => $exists ? 'ok' : 'falha', 'detail' => $exists ? 'Arquivo encontrado' : 'Arquivo nao encontrado'];

            // 3. URL
            $fileUrl = $disk->url($testFile);
            $results[] = ['step' => 'Gerar URL', 'status' => 'ok', 'detail' => $fileUrl];

            // 3b. URL Publicamente acessivel (HEAD na URL pra detectar bucket privado)
            try {
                $headResp = \Illuminate\Support\Facades\Http::timeout(8)
                    ->withHeaders(['User-Agent' => 'SomosUNN-S3-Test/1.0'])
                    ->head($fileUrl);
                $statusCode = $headResp->status();
                if ($statusCode >= 200 && $statusCode < 400) {
                    $results[] = ['step' => 'Acesso publico (HTTP)', 'status' => 'ok', 'detail' => 'HTTP ' . $statusCode . ' — arquivo acessivel publicamente'];
                } else {
                    $results[] = ['step' => 'Acesso publico (HTTP)', 'status' => 'aviso', 'detail' => 'HTTP ' . $statusCode . ' — bucket pode estar PRIVADO. Habilite "public read" no IDrive e2 ou imagens nao aparecerao.'];
                }
            } catch (\Throwable $httpEx) {
                $results[] = ['step' => 'Acesso publico (HTTP)', 'status' => 'aviso', 'detail' => 'Nao foi possivel verificar a URL: ' . Str::limit($httpEx->getMessage(), 100)];
            }

            // 4. Read
            $readContent = $disk->get($testFile);
            $match = $readContent === $testContent;
            $results[] = ['step' => 'Leitura', 'status' => $match ? 'ok' : 'falha', 'detail' => $match ? 'Conteudo confere' : 'Conteudo divergente'];

            // 5. Delete
            $disk->delete($testFile);
            $deleted = !$disk->exists($testFile);
            $results[] = ['step' => 'Exclusao', 'status' => $deleted ? 'ok' : 'aviso', 'detail' => $deleted ? 'Arquivo removido' : 'Arquivo pode ainda existir (cache)'];

            return response()->json([
                'success' => true,
                'message' => 'Conexao S3 testada com sucesso!',
                'results' => $results,
            ]);
        } catch (\Throwable $e) {
            $results[] = ['step' => 'Erro', 'status' => 'falha', 'detail' => $e->getMessage()];

            return response()->json([
                'success' => false,
                'message' => 'Falha na conexao S3: ' . Str::limit($e->getMessage(), 200),
                'results' => $results,
            ]);
        }
    }

    public function migrateStorage(Request $request)
    {
        // Only superadmin can migrate
        if (!auth()->user() || auth()->user()->role !== 'superadmin') {
            return response()->json(['success' => false, 'message' => 'Apenas o superadmin pode executar migracoes.'], 403);
        }

        $path = $request->input('path', '');
        $deleteLocal = $request->boolean('delete_local', false);

        try {
            $localDisk = Storage::disk('public');
            $s3Disk = Storage::disk('s3');

            $files = $path ? $localDisk->allFiles($path) : $localDisk->allFiles('');

            if (empty($files)) {
                return response()->json(['success' => true, 'message' => 'Nenhum arquivo encontrado.', 'migrated' => 0]);
            }

            $migrated = 0;
            $failed = 0;
            $skipped = 0;

            foreach ($files as $file) {
                if ($s3Disk->exists($file)) {
                    // Marca no cache para que url() resolva pro S3 sem novo HEAD
                    UploadStorage::markAsOnS3($file);
                    $skipped++;
                    continue;
                }

                try {
                    $content = $localDisk->get($file);
                    $s3Disk->put($file, $content, 'public');

                    if ($s3Disk->exists($file)) {
                        $migrated++;
                        // Marca proativamente no cache: url() vai usar S3 imediatamente
                        UploadStorage::markAsOnS3($file);
                        if ($deleteLocal) {
                            $localDisk->delete($file);
                        }
                    } else {
                        $failed++;
                    }
                } catch (\Throwable $e) {
                    $failed++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Migracao concluida: {$migrated} migrados, {$skipped} ja existiam, {$failed} falharam.",
                'migrated' => $migrated,
                'skipped' => $skipped,
                'failed' => $failed,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function storageFolders()
    {
        if (!auth()->user() || auth()->user()->role !== 'superadmin') {
            return response()->json(['success' => false], 403);
        }

        $localDisk = Storage::disk('public');
        $directories = $localDisk->directories('');
        $folders = [];

        foreach ($directories as $dir) {
            $files = $localDisk->allFiles($dir);
            $size = 0;
            foreach ($files as $f) {
                $size += $localDisk->size($f);
            }
            $folders[] = [
                'name' => $dir,
                'files' => count($files),
                'size' => $size,
                'size_formatted' => $this->formatStorageBytes($size),
            ];
        }

        // Sort by size descending
        usort($folders, fn($a, $b) => $b['size'] - $a['size']);

        return response()->json(['success' => true, 'folders' => $folders]);
    }

    private function formatStorageBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    /* =========================================================================
     * Multi-Provider S3 Storage - integracao com a tela "Armazenamento" existente
     * Spec: .kiro/specs/multi-provider-s3-storage
     *
     * O select "Driver de Armazenamento" lista 4 opcoes:
     *   - public           (Local)
     *   - idrive           (IDrive e2)
     *   - wasabi           (Wasabi)
     *   - aws              (AWS S3)
     *
     * Quando a opcao S3 e escolhida, o form mostra os campos {provider}_*
     * (ex: idrive_access_key, wasabi_bucket, aws_region) - cada provedor
     * tem seu proprio conjunto de creds. Apenas o ativo e usado em runtime.
     * ========================================================================= */

    /**
     * Testa a conexao do provedor S3 informado SEM ativa-lo.
     * Endpoint AJAX usado pelo botao "Testar Conexao" da tela Armazenamento.
     *
     * Aceita parametro `provider` (idrive|wasabi|aws). Quando ausente,
     * usa o provedor ativo atual.
     *
     * Retorna JSON com lista detalhada de steps.
     */
    public function testStorageProvider(Request $request)
    {
        if (! $this->isSuperadmin()) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        $provider = strtolower(trim((string) $request->input('provider', '')));
        if ($provider === '' || $provider === 'public') {
            // Sem provedor explicito: usa o ativo atual.
            try {
                /** @var \App\Support\StorageProviderRegistry $registry */
                $registry = app(\App\Support\StorageProviderRegistry::class);
                $provider = $registry->activeProvider();
            } catch (\Throwable $e) {
                return response()->json(['error' => 'unable to resolve active provider: ' . $e->getMessage()], 500);
            }
        }

        if (! in_array($provider, \App\Support\StorageProviderRegistry::PROVIDERS, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Provedor invalido. Selecione idrive, wasabi ou aws.',
            ], 422);
        }

        try {
            /** @var \App\Support\StorageProviderRegistry $registry */
            $registry = app(\App\Support\StorageProviderRegistry::class);
            $result = $registry->testConnection($provider);

            $this->logAuditAction('settings.storage.test_connection', [
                'provider' => $provider,
                'status' => $result->status,
                'total_latency_ms' => $result->totalLatencyMs,
            ]);

            // Adapta para o formato esperado pela view legada (results array)
            return response()->json([
                'success' => $result->isSuccess(),
                'message' => $result->isSuccess()
                    ? 'Conexao validada com sucesso ('
                        . $registry->displayName($provider) . ')'
                    : ($result->errorMessage ?? 'Falha na conexao'),
                'provider' => $provider,
                'status' => $result->status,
                'total_latency_ms' => $result->totalLatencyMs,
                'results' => array_map(static function (array $step): array {
                    return [
                        'step' => $step['name'],
                        'status' => $step['status'] === 'success' ? 'ok' : 'erro',
                        'detail' => $step['detail'] . ' (' . $step['latency_ms'] . ' ms)',
                    ];
                }, $result->steps),
            ]);
        } catch (\Throwable $e) {
            \Log::channel('security')->error('storage.provider.test_failed', [
                'provider' => $provider,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erro inesperado: ' . $e->getMessage(),
                'provider' => $provider,
                'results' => [],
            ], 500);
        }
    }

    /**
     * Retorna true quando o usuario autenticado e superadmin.
     */
    private function isSuperadmin(): bool
    {
        $user = auth()->user();
        return $user !== null && (string) $user->role === 'superadmin';
    }

    /**
     * Registra acao de auditoria via AuditLogService quando disponivel.
     * Falla silenciosamente se o servico nao estiver disponivel.
     *
     * @param array<string, mixed> $context
     */
    private function logAuditAction(string $action, array $context): void
    {
        try {
            if (class_exists(\App\Services\AuditLogService::class)) {
                /** @var \App\Services\AuditLogService $audit */
                $audit = app(\App\Services\AuditLogService::class);
                $audit->log($action, null, [], [], $context);
            }
        } catch (\Throwable $e) {
            // Audit logging nunca pode bloquear operacao (fail-safe pattern)
        }
    }
}
