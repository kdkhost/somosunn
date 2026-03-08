<?php

use App\Models\MailTemplate;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tpl = MailTemplate::where('slug', 'job_apply_owner')->first();
if ($tpl) {
    echo "SLUG: {$tpl->slug}\n";
    echo "--- BODY FULL START ---\n";
    echo $tpl->body . "\n";
    echo "--- BODY FULL END ---\n";
} else {
    echo "Template nao encontrado.\n";
}
