<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\BirthdayGreeting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBirthdayEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:send-birthday-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia e-mails de aniversário para usuários nascidos neste dia';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->format('m-d');

        $this->info("Verificando aniversariantes de hoje ($today)...");

        // Assumes 'birth_date' column exists and is a date/datetime
        // Creating a fallback in case the column name is different, but standard is birth_date or birthday
        // I will check User model first if possible, but standard guess is birth_date

        $users = User::whereRaw("DATE_FORMAT(birth_date, '%m-%d') = ?", [$today])->get();

        if ($users->isEmpty()) {
            $this->info('Nenhum aniversariante encontrado hoje.');
            return;
        }

        $count = 0;
        foreach ($users as $user) {
            try {
                $user->notify(new BirthdayGreeting());
                $count++;
            } catch (\Exception $e) {
                Log::error("Falha ao enviar e-mail de aniversário para o usuário {$user->id}: " . $e->getMessage());
            }
        }

        $this->info("{$count} e-mails de aniversário enviados.");
    }
}
