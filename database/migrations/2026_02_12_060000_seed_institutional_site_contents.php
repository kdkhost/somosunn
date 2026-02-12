<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_contents')) {
            return;
        }

        $hasType = Schema::hasColumn('site_contents', 'type');
        $now = now();

        $pages = [
            [
                'slug' => 'institucional_sobre',
                'title' => 'Sobre a UNN - União Nacional de Networking',
                'body_view' => 'site.institucional._fallback.sobre',
            ],
            [
                'slug' => 'institucional_manifesto',
                'title' => 'Manifesto UNN - Nossa Visão',
                'body_view' => 'site.institucional._fallback.manifesto',
            ],
            [
                'slug' => 'institucional_quem_somos',
                'title' => 'Quem Somos - Equipe UNN',
                'body_view' => 'site.institucional._fallback.quem-somos',
            ],
            [
                'slug' => 'institucional_como_funciona',
                'title' => 'Como Funciona - UNN',
                'body_view' => 'site.institucional._fallback.como-funciona',
            ],
            [
                'slug' => 'institucional_valores',
                'title' => 'Nossos Valores - UNN',
                'body_view' => 'site.institucional._fallback.valores',
            ],
            [
                'slug' => 'institucional_contato',
                'title' => 'Contato - UNN',
                'body_view' => 'site.institucional._fallback.contato',
            ],
        ];

        $appUrl = rtrim((string) config('app.url'), '/');

        foreach ($pages as $page) {
            $slug = (string) $page['slug'];

            $body = '';
            try {
                $body = (string) view((string) $page['body_view'])->render();
            } catch (\Throwable $e) {
                $body = '';
            }

            // Evita gravar links absolutos fixos no DB (mantém relativo quando possível).
            if ($slug !== 'institucional_contato' && $appUrl !== '') {
                $body = str_replace($appUrl, '', $body);
            }

            $this->upsertIfEmptyOrMissing($slug, 'title', (string) $page['title'], $hasType ? 'text' : null, $now);
            $this->upsertIfEmptyOrMissing($slug, 'body', $body, $hasType ? 'html' : null, $now);
        }
    }

    private function upsertIfEmptyOrMissing(string $slug, string $key, string $value, ?string $type, $now): void
    {
        $existing = DB::table('site_contents')
            ->where('slug', $slug)
            ->where('key', $key)
            ->first();

        if (!$existing) {
            $insert = [
                'slug' => $slug,
                'key' => $key,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($type !== null) {
                $insert['type'] = $type;
            }

            DB::table('site_contents')->insert($insert);
            return;
        }

        if (trim((string) ($existing->value ?? '')) !== '') {
            return;
        }

        $update = [
            'value' => $value,
            'updated_at' => $now,
        ];

        if ($type !== null && trim((string) ($existing->type ?? '')) === '') {
            $update['type'] = $type;
        }

        DB::table('site_contents')->where('id', (int) $existing->id)->update($update);
    }

    public function down(): void
    {
        // no-op (safety)
    }
};

