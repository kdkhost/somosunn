<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\InvoiceOverdue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendInvoiceOverdueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-overdue-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for overdue invoices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for overdue invoices...');

        // Logic: Status is 'pending' or 'issued' AND due_at < now() AND not sent recently?
        // For simplicity, we'll send if it's overdue and hasn't been paid.
        // To avoid spamming, we should check if a reminder was already sent.
        // But the Invoice model doesn't have 'last_reminder_sent_at'.
        // We will stick to a simple logic: due date was YESTERDAY. This ensures it sends only once.

        $yesterday = now()->subDay()->format('Y-m-d');

        // Ensure we are comparing just the date part if possible
        $invoices = Invoice::where('status', 'pending') // or 'issued' depending on your business logic
            ->whereDate('due_at', $yesterday)
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('No invoices became overdue yesterday.');
            return;
        }

        $count = 0;
        foreach ($invoices as $invoice) {
            try {
                if ($invoice->user) {
                    $invoice->user->notify(new InvoiceOverdue($invoice));
                    $count++;
                }
            } catch (\Exception $e) {
                Log::error("Failed to send overdue reminder for invoice {$invoice->id}: " . $e->getMessage());
            }
        }

        $this->info("Sent $count overdue reminders.");
    }
}
