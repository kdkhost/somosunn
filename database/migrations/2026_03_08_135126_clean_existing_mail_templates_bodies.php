<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $templates = \App\Models\MailTemplate::all();

        foreach ($templates as $template) {
            $body = $template->body;

            // Se contém tags que indicam um layout completo, vamos limpar
            if (str_contains($body, '<html>') || str_contains($body, '<body') || str_contains($body, '<style')) {

                // Tenta extrair apenas o conteúdo do <body> se existir
                if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $body, $matches)) {
                    $newBody = $matches[1];
                } else {
                    // Remove tags globais mas mantém tags de formatação básica
                    $newBody = preg_replace('/<style.*?>.*?<\/style>/is', '', $body);
                    $newBody = preg_replace('/<html.*?>|<\/html>|<head.*?>.*?<\/head>|<body.*?>|<\/body>/is', '', $newBody);
                }

                $newBody = trim($newBody);

                if ($newBody !== $body && !empty($newBody)) {
                    $template->body = $newBody;
                    $template->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nao há como reverter a limpeza sem um backup dos dados antigos.
    }
};
