<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class NotFreeEmail implements ValidationRule
{
    /**
     * Common public and disposable email domains blocked for corporate accounts.
     *
     * @var array<int, string>
     */
    private const BLOCKED_DOMAINS = [
        'gmail.com',
        'yahoo.com',
        'hotmail.com',
        'outlook.com',
        'icloud.com',
        'aol.com',
        'protonmail.com',
        'mail.com',
        'yandex.com',
        'zoho.com',
        'gmx.com',
    ];

    /**
     * Execute the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! str_contains($value, '@')) {
            $fail('The :attribute must be a valid corporate email address.');
            return;
        }

        $domain = strtolower(substr((string) strrchr($value, '@'), 1));

        if (in_array($domain, self::BLOCKED_DOMAINS, true)) {
            $fail('Corporate registration requires an official business domain (e.g., user@company.com).');
        }
    }
}