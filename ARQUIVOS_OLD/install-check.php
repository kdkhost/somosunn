<?php
/**
 * Sistema UNN - Redirecionador de instalação
 *
 * Autor: George Marcelo (KDKHOST SOLUÇÕES)
 * Telefone: +55 (21) 98132-5441
 * Telegram: https://t.me/MARCELO_BRAD
 *
 * Copyright (c) 2026 Kdkhost Soluções. Todos os direitos reservados.
 *
 * AVISO LEGAL:
 * Este software e seu código-fonte são propriedade intelectual de kdkhost soluções.
 * É proibida a reprodução, distribuição, modificação, engenharia reversa ou uso não autorizado,
 * total ou parcial, sem autorização prévia e por escrito.
 *
 * Contato: contato@kdkhost.com.br
 * Licenciamento: Uso restrito conforme contrato/termos aplicáveis.
 */

function ensureEnvFile(): void
{
    $envPath = __DIR__ . '/backend/.env';
    $examplePath = __DIR__ . '/backend/.env.example';

    if (!file_exists($envPath)) {
        if (file_exists($examplePath)) {
            copy($examplePath, $envPath);
        } else {
            file_put_contents($envPath, defaultEnv());
        }
    }
}

function hasAppInstalled(): bool
{
    $envPath = __DIR__ . '/backend/.env';
    if (!file_exists($envPath)) {
        return false;
    }

    $content = file_get_contents($envPath);
    return stripos($content, 'APP_INSTALLED=true') !== false;
}

function defaultEnv(): string
{
    return <<<TXT
APP_NAME=UNN
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack

SESSION_DRIVER=file
SESSION_LIFETIME=120

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=unn_db
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="UNN"
TXT;
}

ensureEnvFile();

$target = hasAppInstalled() ? '/backend' : '/backend/install';
header('Location: ' . $target, true, 302);
exit;
