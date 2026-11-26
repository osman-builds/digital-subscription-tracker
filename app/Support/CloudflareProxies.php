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
        $envOverride = getenv('TRUSTED_PROXIES');
        if (is_string($envOverride) && trim($envOverride) !== '') {
            $parsed = array_values(array_filter(
                array_map('trim', explode(',', $envOverride)),
                static fn (string $cidr): bool => $cidr !== ''
            ));

            if ($parsed !== []) {
                return $parsed;
            }
        }

        $path = dirname(__DIR__, 2).'/config/cloudflare-proxies.json';
        if (! is_file($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        if (! is_array($decoded)) {
            return [];
        }

        $cidrs = [];
        foreach (['loopback', 'cloudflare_ipv4', 'cloudflare_ipv6'] as $group) {
            $entries = $decoded[$group] ?? [];
            if (is_array($entries)) {
                foreach ($entries as $entry) {
                    if (is_string($entry) && trim($entry) !== '') {
                        $cidrs[] = trim($entry);
                    }
                }
            }
        }

        return array_values(array_unique($cidrs));
    }
}
