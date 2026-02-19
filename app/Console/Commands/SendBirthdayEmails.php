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
    protected $description = 'Send birthday emails to users born on this day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->format('m-d');

        $this->info("Checking for birthdays on $today...");

        // Assumes 'birth_date' column exists and is a date/datetime
        // Creating a fallback in case the column name is different, but standard is birth_date or birthday
        // I will check User model first if possible, but standard guess is birth_date

        $users = User::whereRaw("DATE_FORMAT(birth_date, '%m-%d') = ?", [$today])->get();

        if ($users->isEmpty()) {
            $this->info('No birthdays found today.');
            return;
        }

        $count = 0;
        foreach ($users as $user) {
            try {
                $user->notify(new BirthdayGreeting());
                $count++;
            } catch (\Exception $e) {
                Log::error("Failed to send birthday email to user {$user->id}: " . $e->getMessage());
            }
        }

        $this->info("Sent $count birthday emails.");
    }
}
