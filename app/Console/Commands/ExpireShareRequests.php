<?php

namespace App\Console\Commands;

use App\Models\ShareRequest;
use Illuminate\Console\Command;

class ExpireShareRequests extends Command
{
    protected $signature   = 'share-requests:expire';
    protected $description = 'Expira solicitações de compartilhamento com mais de 7 dias sem resposta';

    public function handle(): int
    {
        $count = ShareRequest::expired()->update(['status' => 'expired']);

        if ($count > 0) {
            $this->info("$count solicitação(ões) de compartilhamento expirada(s).");
        }

        return 0;
    }
}
