<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

/**
 * Gera o arquivo .cursor/mcp.json com o Access Token atual do banco de dados.
 *
 * Uso:
 *   php artisan mcp:configure
 *
 * O token é escolhido automaticamente de acordo com o ambiente ativo:
 *   - mercadopago_env = 'production' → mercadopago_prod_access_token
 *   - mercadopago_env = 'sandbox'    → mercadopago_sandbox_access_token
 */
class ConfigureMcpServer extends Command
{
    protected $signature = 'mcp:configure';
    protected $description = 'Gera .cursor/mcp.json com o Access Token do MercadoPago lido do banco de dados';

    public function handle(): int
    {
        // Ambiente ativo (sandbox ou production)
        $env = Setting::get('mercadopago_env', 'sandbox');

        // Escolhe o token correto de acordo com o ambiente
        if ($env === 'production') {
            $token = Setting::get('mercadopago_prod_access_token', '');
            $label = 'produção';
        } else {
            $token = Setting::get('mercadopago_sandbox_access_token', '');
            $label = 'sandbox';
        }

        if (empty($token)) {
            $this->error("Nenhum Access Token encontrado para o ambiente \"{$env}\".");
            $this->line('Configure as credenciais em: Painel Admin → Configurações → Pagamentos → MercadoPago');
            return self::FAILURE;
        }

        // Conteúdo do mcp.json
        $config = [
            'mcpServers' => [
                'mercadopago' => [
                    'url' => 'https://mcp.mercadopago.com/mcp',
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                    ],
                ],
            ],
        ];

        // Garante que a pasta .cursor existe
        $dir = base_path('.cursor');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Escreve o arquivo
        $path = $dir . '/mcp.json';
        file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

        $integratorId = Setting::get('mercadopago_integrator_id', '');

        $this->info("✅ .cursor/mcp.json gerado com sucesso!");
        $this->line("   Ambiente : <fg=yellow>{$env}</> ({$label})");
        $this->line("   Token    : <fg=yellow>" . substr($token, 0, 12) . '…</> (ocultado para segurança)');
        if ($integratorId) {
            $this->line("   Integrador: <fg=yellow>{$integratorId}</>");
        }
        $this->line("   Arquivo  : <fg=cyan>{$path}</>");
        $this->newLine();
        $this->comment('Reinicie o Cursor para o servidor MCP ser ativado.');

        return self::SUCCESS;
    }
}
