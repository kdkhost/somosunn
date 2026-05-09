<?php

namespace App\Jobs;

use App\Services\Mail\SystemMailLayoutData;
use App\Support\EmailQueueSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendGenericTemplateEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $toEmail;
    public string $subject;
    public string $htmlContent;

    public function __construct(string $toEmail, string $subject, string $htmlContent)
    {
        $this->toEmail = $toEmail;
        $this->subject = $subject;
        $this->htmlContent = $htmlContent;
        $this->onConnection(EmailQueueSettings::connection());
        $this->onQueue(EmailQueueSettings::queueName());
    }

    public function handle(): void
    {
        $layout = app(SystemMailLayoutData::class)->make();

        Mail::send('emails.system', array_merge($layout, ['content' => $this->htmlContent]), function ($message) {
            $message->to($this->toEmail)->subject($this->subject);
        });
    }
}
