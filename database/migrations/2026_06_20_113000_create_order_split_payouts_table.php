<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('order_split_payouts')) {
            return;
        }

        Schema::create('order_split_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_split_id')->unique()->constrained('order_splits')->onDelete('cascade');
            $table->string('provider', 40)->default('manual');
            $table->enum('status', ['pending', 'processing', 'paid', 'failed'])->default('pending');
            $table->decimal('amount', 15, 2);
            $table->string('pix_key')->nullable();
            $table->string('external_id')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'provider']);
        });

        DB::table('order_splits')
            ->orderBy('id')
            ->select(['id', 'status', 'amount', 'pix_key', 'created_at', 'updated_at'])
            ->chunkById(200, function ($splits) {
                $rows = [];

                foreach ($splits as $split) {
                    $isPaid = (string) $split->status === 'paid';
                    $timestamp = now();

                    $rows[] = [
                        'order_split_id' => $split->id,
                        'provider' => $isPaid ? 'internal' : 'manual',
                        'status' => $isPaid ? 'paid' : 'pending',
                        'amount' => $split->amount,
                        'pix_key' => $split->pix_key,
                        'external_id' => null,
                        'attempts' => 0,
                        'last_error' => null,
                        'notes' => $isPaid ? 'Registro legado conciliado internamente na migracao.' : 'Registro legado aguardando repasse.',
                        'last_attempt_at' => null,
                        'processed_at' => $isPaid ? ($split->updated_at ?? $timestamp) : null,
                        'created_at' => $split->created_at ?? $timestamp,
                        'updated_at' => $split->updated_at ?? $timestamp,
                    ];
                }

                if ($rows !== []) {
                    DB::table('order_split_payouts')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_split_payouts');
    }
};
