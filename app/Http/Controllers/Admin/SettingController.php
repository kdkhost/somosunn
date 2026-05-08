<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
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
            'value' => 'required|boolean',
        ]);

        $key = $request->input('key');
        $value = $request->boolean('value') ? 1 : 0;

        Setting::set($key, $value);

        return response()->json(['success' => true, 'message' => 'Configuração atualizada.']);
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
                $data[$checkbox] = $request->has($checkbox) ? 1 : 0;
            }

            // VALIDAÇÃO: MercadoPago ativo deve ter ao menos um método
            $mpEnabled = (int) ($data['mercadopago_enabled'] ?? 0) === 1;

            if ($mpEnabled) {
                $mpMethodsActive = (int) ($data['mercadopago_method_credit_card'] ?? 0)
                    + (int) ($data['mercadopago_method_debit_card'] ?? 0)
                    + (int) ($data['mercadopago_method_pix'] ?? 0)
                    + (int) ($data['mercadopago_method_ticket'] ?? 0)
                    + (int) ($data['mercadopago_method_mercadopago'] ?? 0);

                if ($mpMethodsActive === 0) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['message' => 'Ao menos um método de pagamento deve permanecer ativo para o MercadoPago.'], 422);
                    }
                    return redirect()->back()
                        ->with('error', 'Ao menos um método de pagamento deve permanecer ativo para o MercadoPago.')
                        ->withInput();
                }
            }

            // SumUp: se ativo, garantir que pelo menos um método esteja ativo
            $sumupEnabled = (int) ($data['sumup_enabled'] ?? 0) === 1;
            if ($sumupEnabled) {
                $sumupMethodsActive = (int) ($data['sumup_method_card'] ?? 0)
                    + (int) ($data['sumup_method_pix'] ?? 0);

                // Se nenhum método SumUp está marcado, ativar card por padrão
                if ($sumupMethodsActive === 0) {
                    $data['sumup_method_card'] = 1;
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

        // Auto-save: Retornar JSON se for requisição AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Configurações salvas automaticamente.']);
        }

        return response()->json(['reload' => true, 'message' => 'Configuracoes salvas']);

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
}
