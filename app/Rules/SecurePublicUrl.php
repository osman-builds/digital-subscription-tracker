<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Throwable;

class SecurePublicUrl implements ValidationRule
{
    private const DISALLOWED_SUFFIXES = [
        '.local',
        '.internal',
        '.localhost',
        '.test',
        '.invalid',
        '.arpa',
        '.lan',
    ];

    private const MULTI_PART_SUFFIXES = [
        'com.my', 'net.my', 'org.my', 'edu.my', 'gov.my',
        'co.uk', 'org.uk', 'ac.uk', 'com.sg', 'com.au',
        'co.jp', 'co.kr', 'co.in', 'com.br', 'co.nz',
    ];

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $trimmed = trim($value);

        if (! filter_var($trimmed, FILTER_VALIDATE_URL)) {
            $fail("The {$attribute} must be a valid URL.");

            return;
        }

        $scheme = strtolower((string) parse_url($trimmed, PHP_URL_SCHEME));
        if ($scheme !== 'https') {
            $fail("The {$attribute} must use a secure HTTPS connection.");

            return;
        }

        $host = strtolower((string) parse_url($trimmed, PHP_URL_HOST));
        if (! $this->isValidPublicHost($host)) {
            $fail("The {$attribute} cannot point to a local, private, or internal network address.");

            return;
        }

        if (! $this->domainExists($host)) {
            $fail("The {$attribute} must belong to an active, verified public domain.");
        }
    }

    private function isValidPublicHost(string $host): bool
    {
        if ($host === '' || ! str_contains($host, '.')) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return false;
        }

        foreach (self::DISALLOWED_SUFFIXES as $suffix) {
            if (str_ends_with($host, $suffix)) {
                return false;
            }
        }

        return (bool) preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/', $host);
    }

    private function domainExists(string $host): bool
    {
        if ($this->hasDnsRecord($host)) {
            return true;
        }

        $root = $this->extractRootDomain($host);

        return $root !== '' && $root !== $host && $this->hasDnsRecord($root);
    }

    private function extractRootDomain(string $host): string
    {
        $parts = explode('.', $host);
        if (count($parts) <= 2) {
            return $host;
        }

        $lastTwo = implode('.', array_slice($parts, -2));
        if (in_array($lastTwo, self::MULTI_PART_SUFFIXES, true) && count($parts) >= 3) {
            return implode('.', array_slice($parts, -3));
        }

        return $lastTwo;
    }

    private function hasDnsRecord(string $domain): bool
    {
        try {
            return checkdnsrr($domain, 'A')
                || checkdnsrr($domain, 'AAAA')
                || checkdnsrr($domain, 'CNAME');
        } catch (Throwable) {
            return false;
        }
    }
}
