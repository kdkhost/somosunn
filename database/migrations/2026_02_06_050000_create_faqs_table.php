<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('faqs')) {
            Schema::create('faqs', function (Blueprint $table) {
                $table->id();

                // Onde essa pergunta aparece (ex: premium, contato, geral)
                $table->string('context', 30)->default('general');

                $table->string('question', 255);
                $table->text('answer');

                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);

                $table->timestamps();

                $table->index(['context', 'is_active', 'sort_order'], 'faqs_context_active_order_idx');
            });
        }

        // Seed inicial (mantém o site igual após deploy) — só se estiver vazio.
        try {
            if (DB::table('faqs')->count() > 0) {
                return;
            }

            $now = now();

            DB::table('faqs')->insert([
                // Contato
                [
                    'context' => 'contact',
                    'question' => 'Como faço para me tornar membro?',
                    'answer' => 'Basta se cadastrar no site. O plano básico é gratuito e já dá acesso à comunidade.',
                    'sort_order' => 10,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'context' => 'contact',
                    'question' => 'Os eventos são presenciais ou online?',
                    'answer' => 'Realizamos eventos nos dois formatos! Temos encontros presenciais em diversas cidades e webinars semanais.',
                    'sort_order' => 20,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'context' => 'contact',
                    'question' => 'Posso cancelar minha assinatura a qualquer momento?',
                    'answer' => 'Sim! Não temos fidelidade. Você pode cancelar quando quiser sem taxas adicionais.',
                    'sort_order' => 30,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'context' => 'contact',
                    'question' => 'Como funciona o sistema de indicações?',
                    'answer' => 'Membros podem indicar outros membros para oportunidades de negócio. Facilitamos e acompanhamos cada conexão.',
                    'sort_order' => 40,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],

                // Premium
                [
                    'context' => 'premium',
                    'question' => 'Posso cancelar a qualquer momento?',
                    'answer' => 'Sim! Não temos taxa de cancelamento ou fidelidade. Você pode cancelar quando quiser pelo próprio painel.',
                    'sort_order' => 10,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'context' => 'premium',
                    'question' => 'Como funciona o pagamento?',
                    'answer' => 'Aceitamos cartão de crédito, PIX e boleto. O pagamento é recorrente mensal ou anual, conforme sua escolha.',
                    'sort_order' => 20,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'context' => 'premium',
                    'question' => 'O que acontece se eu fizer downgrade?',
                    'answer' => 'Você perde acesso aos benefícios premium imediatamente, mas mantém seu perfil e conexões feitas.',
                    'sort_order' => 30,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'context' => 'premium',
                    'question' => 'Posso migrar do mensal para o anual?',
                    'answer' => 'Sim! A migração é simples e você ganha o desconto proporcional ao período restante.',
                    'sort_order' => 40,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        } catch (\Throwable $e) {
            // ignore: em alguns ambientes, migrar primeiro e popular depois pode ser preferível
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
