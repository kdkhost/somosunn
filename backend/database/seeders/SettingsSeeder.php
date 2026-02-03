<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $defaults = [
            'app_name' => 'UNN',
            'pwa_enabled' => '1',
            'site_theme' => 'light',
            'video_max_mb' => '1024', // 1GB default
            'document_max_mb' => '50',
            'allowed_video_formats' => 'mp4,webm,mkv',
            'allowed_document_formats' => 'pdf,docx,pptx',
            'payments.mercadopago.fee_percentage' => '4.99',
            'payments.mercadopago.fee_fixed' => '0',
            'payments.mercadopago.pass_fee' => '1',
            'payments.pagseguro.fee_percentage' => '5.49',
            'payments.pagseguro.fee_fixed' => '0',
            'payments.pagseguro.pass_fee' => '1',
        ];

        foreach($defaults as $k => $v){
            Setting::set($k, $v);
        }
    }
}