<?php

declare(strict_types=1);

namespace App\Support;

final class CloudflareProxies
{
    /**
     * Load trusted local loopback and Cloudflare edge CIDR ranges from configuration data
     * (or an explicit comma-separated TRUSTED_PROXIES environment override).
     *
     * @return array<int, string>
     */
    public static function cidrs(): array
    {
        $override = self::parseEnvOverride();
        if ($override !== []) {
            return $override;
        }

        return self::loadFromConfigFile();
    }

    /**
     * @return array<int, string>
     */
    private static function parseEnvOverride(): array
    {
        $envOverride = getenv('TRUSTED_PROXIES');
        if (! is_string($envOverride) || trim($envOverride) === '') {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $envOverride)),
            static fn (string $cidr): bool => $cidr !== ''
        ));
    }

    /**
     * @return array<int, string>
     */
    private static function loadFromConfigFile(): array
    {
        $path = dirname(__DIR__, 2).'/config/cloudflare-proxies.json';
        $raw = is_file($path) ? file_get_contents($path) : false;
        $decoded = is_string($raw) ? json_decode($raw, true) : null;

        if (! is_array($decoded)) {
            return [];
        }

        $cidrs = [];
        foreach (['loopback', 'cloudflare_ipv4', 'cloudflare_ipv6'] as $group) {
            $cidrs = array_merge($cidrs, self::extractGroupCidrs($decoded[$group] ?? null));
        }

        return array_values(array_unique($cidrs));
    }

    /**
     * @return array<int, string>
     */
    private static function extractGroupCidrs(mixed $entries): array
    {
        if (! is_array($entries)) {
            return [];
        }

        $clean = [];
        foreach ($entries as $entry) {
            if (is_string($entry) && trim($entry) !== '') {
                $clean[] = trim($entry);
            }
        }

        return $clean;
    }
}
