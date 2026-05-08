<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupExpiredCartItems extends Command
{
    protected $signature = 'cart:cleanup-expired';
    protected $description = 'Remove itens de carrinho expirados (mais de 24h, configurável em cart_expiration_hours)';

    public function handle()
    {
        if (!Schema::hasTable('seller_product_cart_items')) {
            $this->info('Tabela de carrinho persistente ainda não foi criada.');
            return self::SUCCESS;
        }

        $deleted = DB::table('seller_product_cart_items')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("Removidos {$deleted} itens expirados do carrinho.");

        return self::SUCCESS;
    }
}
