<?php

use App\Models\MailTemplate;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$templates = MailTemplate::all();
$cleanedCount = 0;

foreach ($templates as $template) {
    $body = $template->body;

    // Check if it looks like a full HTML document
    if (str_contains($body, '<html>') || str_contains($body, '<body') || str_contains($body, '<style')) {
        echo "Limpando template: {$template->slug} ({$template->name})\n";

        // Let's try to extract content between <body> tags if they exist
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $body, $matches)) {
            $newBody = $matches[1];
        } else {
            // If body tag not found but style/html is there, let's just strip common tags but keep the rest
            $newBody = strip_tags($body, '<p><a><strong><em><ul><ol><li><br><img><table><tr><td><th><tbody><thead><h1><h2><h3><h4><h5><span><div><style><center>');

            // If it has a huge <style> block at the beginning, let's try to be smarter
            $newBody = preg_replace('/<style.*?>.*?<\/style>/is', '', $newBody);
            $newBody = preg_replace('/<html.*?>|<\/html>|<head.*?>.*?<\/head>|<body.*?>|<\/body>/is', '', $newBody);
        }

        $newBody = trim($newBody);

        if ($newBody !== $body && !empty($newBody)) {
            $template->body = $newBody;
            $template->save();
            $cleanedCount++;
            echo "SUCCESS: Template {$template->slug} limpo.\n";
        } else {
            echo "WARNING: Nao foi possivel limpar automaticamente {$template->slug} ou o resultado ficou vazio.\n";
        }
    }
}

echo "\nTotal de templates limpos: $cleanedCount\n";
