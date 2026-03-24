<?php

use App\Support\LegalPageContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pages')) {
            return;
        }

        $now = now();

        foreach (LegalPageContent::definitions() as $page) {
            $existing = DB::table('pages')
                ->where('slug', $page['slug'])
                ->first(['id', 'data']);

            $existingData = [];

            if (is_string($existing?->data) && $existing->data !== '') {
                $decoded = json_decode($existing->data, true);
                if (is_array($decoded)) {
                    $existingData = $decoded;
                }
            }

            $data = array_replace($existingData, $page['data']);
            $payload = [
                'title' => $page['title'],
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => $now,
            ];

            if ($existing !== null) {
                DB::table('pages')->where('id', $existing->id)->update($payload);
                continue;
            }

            DB::table('pages')->insert($payload + [
                'slug' => $page['slug'],
                'created_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Sem reversão automática para não sobrescrever
        // ajustes editoriais posteriores no conteúdo legal.
    }
};
