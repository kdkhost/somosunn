<?php

/**
 * @autor marcelo-brad rj
 * @contato Tel: 21 981325441
 * @contato Email: contato@kdkhost.com.br
 * @contato Telegram: @MARCELO_BRAD
 * @contato Instagram: @marcelobradrj
 * @contato WhatsApp: 21981325441
 */

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WhatsAppGroupLinkRule implements ValidationRule
{
    private const ALLOWED_HOSTS = [
        'chat.whatsapp.com',
        'www.chat.whatsapp.com',
    ];

    public static function passes(?string $url): bool
    {
        $url = trim((string) $url);

        if ($url === '') {
            return false;
        }

        if (strlen($url) > 2048 || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if ($scheme !== 'https') {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return in_array($host, self::ALLOWED_HOSTS, true);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $url = trim((string) $value);

        if ($url === '') {
            return;
        }

        if (!self::passes($url)) {
            $fail('Use somente links oficiais de grupo do WhatsApp.');
        }
    }
}
