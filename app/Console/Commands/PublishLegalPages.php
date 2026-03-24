<?php

namespace App\Console\Commands;

use Database\Seeders\Support\LegalPagesPublisher;
use Illuminate\Console\Command;

class PublishLegalPages extends Command
{
    protected $signature = 'pages:publicar-legais {--forcar : Sobrescreve os campos padrao das paginas legais}';

    protected $description = 'Publica ou sincroniza as paginas legais no CMS';

    public function handle(): int
    {
        $force = (bool) $this->option('forcar');

        if ($force) {
            LegalPagesPublisher::publish();
            $this->info('Paginas legais republicadas com sobrescrita dos campos padrao.');

            return self::SUCCESS;
        }

        LegalPagesPublisher::publishMissing();
        $this->info('Paginas legais sincronizadas sem sobrescrever edicoes existentes.');

        return self::SUCCESS;
    }
}
