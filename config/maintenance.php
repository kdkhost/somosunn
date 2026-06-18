<?php

/**
 * @autor marcelo-brad rj
 * @contato Tel: 21 981325441
 * @contato Email: contato@kdkhost.com.br
 * @contato Telegram: @MARCELO_BRAD
 * @contato Instagram: @marcelobradrj
 * @contato WhatsApp: 21981325441
 */

return [
    'allow_sensitive_routes' => env('ALLOW_MAINTENANCE_ROUTES', false),
    'allow_installer' => env('ALLOW_INSTALLER_ROUTES', false),
    'installer_token' => env('INSTALLER_TOKEN', ''),
    'installed_lock_path' => storage_path('app/installed.lock'),
];
