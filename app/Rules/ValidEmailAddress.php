<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidEmailAddress implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = mb_strtolower(trim((string) $value));

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $fail('Informe um endereço de e-mail válido.');
            return;
        }

        $domain = mb_strtolower((string) substr(strrchr($email, '@') ?: '', 1));
        if ($domain === '') {
            $fail('Informe um endereço de e-mail válido.');
            return;
        }

        if (function_exists('idn_to_ascii')) {
            $variant = defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : 0;
            $asciiDomain = idn_to_ascii($domain, IDNA_DEFAULT, $variant);
            if (is_string($asciiDomain) && $asciiDomain !== '') {
                $domain = $asciiDomain;
            }
        }

        if (app()->environment('testing') && in_array($domain, ['example.com', 'example.test'], true)) {
            return;
        }

        if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A') && !checkdnsrr($domain, 'AAAA')) {
            $fail('O domínio informado no e-mail não existe ou não pode receber mensagens.');
        }
    }
}
