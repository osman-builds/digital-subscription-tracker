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
     * @param  (Closure(string): array<int, string>)|null  $ipResolver
     */
    public function __construct(
        private readonly ?Closure $ipResolver = null,
    ) {}

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
        $scheme = strtolower((string) parse_url($trimmed, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($trimmed, PHP_URL_HOST));

        if (! filter_var($trimmed, FILTER_VALIDATE_URL) || $scheme !== 'https') {
            $fail("The {$attribute} must use a secure HTTPS connection.");
        } elseif (! $this->isValidPublicHost($host)) {
            $fail("The {$attribute} cannot point to a local, private, or internal network address.");
        } else {
            $this->validateResolvedHost($attribute, $host, $fail);
        }
    }

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    private function validateResolvedHost(string $attribute, string $host, Closure $fail): void
    {
        $resolvedIps = $this->resolveHostIps($host);

        if ($resolvedIps === []) {
            $root = $this->extractRootDomain($host);
            if ($root !== '' && $root !== $host) {
                $resolvedIps = $this->resolveHostIps($root);
            }
        }

        if ($resolvedIps === []) {
            $fail("The {$attribute} must belong to an active, verified public domain.");

            return;
        }

        foreach ($resolvedIps as $ip) {
            if (! $this->isPublicIp($ip)) {
                $fail("The {$attribute} cannot point to a local, private, or internal network address.");

                return;
            }
        }
    }

    private function isValidPublicHost(string $host): bool
    {
        $hasDisallowedSuffix = false;
        foreach (self::DISALLOWED_SUFFIXES as $suffix) {
            if (str_ends_with($host, $suffix)) {
                $hasDisallowedSuffix = true;
                break;
            }
        }

        return $host !== ''
            && str_contains($host, '.')
            && ! filter_var($host, FILTER_VALIDATE_IP)
            && ! $hasDisallowedSuffix
            && (bool) preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)+$/', $host);
    }

    private function extractRootDomain(string $host): string
    {
        $parts = explode('.', $host);
        if (count($parts) <= 2) {
            return $host;
        }

        $lastTwo = implode('.', array_slice($parts, -2));
        if (in_array($lastTwo, self::MULTI_PART_SUFFIXES, true)) {
            return implode('.', array_slice($parts, -3));
        }

        return $lastTwo;
    }

    /**
     * Resolve all A and AAAA IP addresses for a hostname.
     *
     * @return array<int, string>
     */
    private function resolveHostIps(string $domain): array
    {
        if ($this->ipResolver !== null) {
            return ($this->ipResolver)($domain);
        }

        $ips = [];

        try {
            $records = @dns_get_record($domain, DNS_A | DNS_AAAA);
            if (is_array($records)) {
                foreach ($records as $record) {
                    $ip = $record['ip'] ?? $record['ipv6'] ?? null;
                    if (is_string($ip) && $ip !== '') {
                        $ips[] = $ip;
                    }
                }
            }

            if ($ips === []) {
                $fallback = @gethostbynamel($domain);
                if (is_array($fallback)) {
                    $ips = array_values(array_filter($fallback, static fn (string $ip): bool => $ip !== ''));
                }
            }
        } catch (Throwable) {
            return [];
        }

        return array_values(array_unique($ips));
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }
}
