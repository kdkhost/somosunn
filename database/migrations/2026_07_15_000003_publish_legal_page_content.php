<?php

use Database\Seeders\Support\LegalPagesPublisher;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        LegalPagesPublisher::publish();
    }

    public function down(): void
    {
        // Sem reversao automatica para nao sobrescrever
        // ajustes editoriais posteriores no conteudo legal.
    }
};
