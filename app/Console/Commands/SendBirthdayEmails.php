<?php

namespace App\Console\Commands;

use App\Jobs\SendGenericTemplateEmail;
use App\Models\MailTemplate;
use App\Models\Setting;
use App\Models\User;
use App\Services\Mail\SystemMailLayoutData;
use Illuminate\Console\Command;

class SendBirthdayEmails extends Command
{
    protected $signature = 'users:send-birthday-emails';
    protected $description = 'Envia emails de aniversário para usuários que fazem aniversário hoje';

    public function handle()
    {
        if ((int) Setting::get('cron_points_birthday_enabled', 1) !== 1) {
            $this->info('Cron de aniversário desativado.');
            return self::SUCCESS;
        }

        $today = now();
        $users = User::whereNotNull('birthday')
            ->whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->get();

        if ($users->isEmpty()) {
            $this->info('Nenhum aniversariante hoje.');
            return self::SUCCESS;
        }

        $template = MailTemplate::where('slug', 'aniversario')->where('is_active', true)->first();
        if (!$template) {
            $this->warn('Template "aniversario" não encontrado ou inativo.');
            return self::SUCCESS;
        }

        $layout = app(SystemMailLayoutData::class)->make();
        $count = 0;

        foreach ($users as $user) {
            $data = [
                'user' => ['name' => $user->name, 'email' => $user->email],
                'site' => ['name' => $layout['siteName'], 'primary_color' => $layout['primaryColor'], 'url' => url('/')],
            ];

            $rendered = (string) $template->body;
            $subject = (string) $template->subject;

            foreach ($data as $key => $values) {
                foreach ($values as $k => $v) {
                    $pattern = '/\{\{\s*' . $key . '\.' . $k . '\s*\}\}/';
                    $rendered = preg_replace($pattern, (string) $v, $rendered);
                    $subject = preg_replace($pattern, (string) $v, $subject);
                }
            }

            SendGenericTemplateEmail::dispatch($user->email, $subject, $rendered);
            $count++;
        }

        $this->info("Emails de aniversário enviados: {$count}");
        return self::SUCCESS;
    }
}
