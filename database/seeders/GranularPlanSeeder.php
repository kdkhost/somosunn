<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class GranularPlanSeeder extends Seeder
{
    public function run()
    {
        Plan::create([
            'name' => 'Plano Granular Completo',
            'permissions' => [
                'courses_access', 'courses_create', 'courses_edit', 'courses_delete',
                'mentorships_access', 'mentorships_create', 'mentorships_edit', 'mentorships_delete',
                'events_access', 'events_create', 'events_edit', 'events_delete', 'events_reserve',
                'marketplace_access', 'marketplace_create', 'marketplace_edit', 'marketplace_delete',
                'uploads_access', 'uploads_chunk', 'uploads_assemble',
                'certificates_access', 'certificates_create', 'certificates_generate',
                'points_access',
                'coupons_access',
                'plans_access',
                'permissions_access',
                'faqs_access',
                'fonts_access', 'fonts_create', 'fonts_delete',
                'mailtemplates_access', 'mailtemplates_create', 'mailtemplates_store', 'mailtemplates_edit', 'mailtemplates_update', 'mailtemplates_delete', 'mailtemplates_preview', 'mailtemplates_sendpreview',
                'orders_access', 'orders_refund',
                'invoices_access', 'invoices_issue', 'invoices_send', 'invoices_pdf',
                'ranking_access',
                'social_access', 'social_delete',
                'reviews_access', 'reviews_approve', 'reviews_reject', 'reviews_delete',
                'testimonials_access', 'testimonials_edit', 'testimonials_approve', 'testimonials_reject', 'testimonials_delete',
                'community_access', 'chat_access',
                'mailtest_access'
            ],
        ]);
    }
}
