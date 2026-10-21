<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CorporateEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = strtolower((string) $value);

        if (! str_contains($email, '@')) {
            $fail('Ingresá un correo electrónico válido.');

            return;
        }

        $domain = explode('@', $email)[1] ?? '';

        if ($domain === '') {
            $fail('Ingresá un correo electrónico válido.');

            return;
        }

        if (in_array($domain, config('descuentonic.free_email_domains'), true)) {
            $fail('Usá un correo corporativo (@tuempresa.com). No se aceptan Gmail, Outlook, Yahoo ni similares.');
        }
    }
}
