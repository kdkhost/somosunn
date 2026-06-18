<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 *
 * Sistema UNN - RunTestsCommand
 *
 * Atalho Artisan para executar o PHPUnit local sem alterar a
 * configuracao de banco definida no phpunit.xml do projeto.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RunTestsCommand extends Command
{
    protected $signature = 'test {phpunitArgs?* : Argumentos repassados ao PHPUnit}';

    protected $description = 'Executar a suite PHPUnit do projeto';

    protected $ignoreValidationErrors = true;

    public function handle(): int
    {
        $phpunit = base_path('vendor/bin/phpunit');

        if (!is_file($phpunit)) {
            $this->error('PHPUnit nao encontrado. Execute composer install antes de rodar os testes.');

            return Command::FAILURE;
        }

        $process = new Process(array_merge(
            [PHP_BINARY, $phpunit],
            array_map('strval', $this->argument('phpunitArgs') ?: [])
        ), base_path());
        $process->setTimeout(null);
        $process->run(function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });

        return $process->getExitCode() === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
