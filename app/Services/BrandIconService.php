<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class BrandIconService
{
    private const GOOGLE_FAVICON_BASE = 'https://t2.gstatic.com/faviconV2';

    private const DUCKDUCKGO_ICON_BASE = 'https://icons.duckduckgo.com/ip3';

    private const ICON_HORSE_BASE = 'https://icon.horse/icon';

    /**
     * Multi-part country/public suffixes so subdomains collapse to the true registrable root.
     *
     * @var array<int, string>
     */
    private const MULTI_PART_SUFFIXES = [
        'com.my', 'net.my', 'org.my', 'edu.my', 'gov.my',
        'co.uk', 'org.uk', 'ac.uk', 'com.sg', 'com.au',
        'co.jp', 'co.kr', 'co.in', 'com.br', 'co.nz',
    ];

    /**
     * Curated commercial subscription catalog across Entertainment, Health & Fitness,
     * Finance, Education, Cloud/Dev, and Productivity.
     *
     * @return array<int, array{name: string, domain: string, category: string, cycle: string, aliases: array<int, string>}>
     */
    public function catalog(): array
    {
        return [
            // Entertainment & Streaming
            ['name' => 'Netflix', 'domain' => 'netflix.com', 'category' => 'Entertainment', 'cycle' => 'monthly', 'aliases' => ['netfix', 'netflx', 'neflix', 'netfilx', 'netflixx']],
            ['name' => 'Spotify', 'domain' => 'spotify.com', 'category' => 'Music', 'cycle' => 'monthly', 'aliases' => ['spotfy', 'spotifi', 'spotifay', 'spotfiy']],
            ['name' => 'YouTube Premium', 'domain' => 'youtube.com', 'category' => 'Entertainment', 'cycle' => 'monthly', 'aliases' => ['yutube', 'youtub', 'youtubepremium', 'ytpremium', 'youtbe']],
            ['name' => 'Disney+', 'domain' => 'disneyplus.com', 'category' => 'Entertainment', 'cycle' => 'monthly', 'aliases' => ['disny', 'disney', 'disneyplus', 'disnep']],
            ['name' => 'Amazon Prime', 'domain' => 'amazon.com', 'category' => 'Entertainment', 'cycle' => 'annual', 'aliases' => ['amzon', 'primevideo', 'amazonprime', 'amazn']],
            ['name' => 'HBO Max', 'domain' => 'max.com', 'category' => 'Entertainment', 'cycle' => 'monthly', 'aliases' => ['hbomax', 'hbo']],
            ['name' => 'Hulu', 'domain' => 'hulu.com', 'category' => 'Entertainment', 'cycle' => 'monthly', 'aliases' => ['hullu']],
            ['name' => 'Crunchyroll', 'domain' => 'crunchyroll.com', 'category' => 'Entertainment', 'cycle' => 'monthly', 'aliases' => ['crunchy', 'crunchroll', 'crunchyrool']],
            ['name' => 'Twitch Sub', 'domain' => 'twitch.tv', 'category' => 'Gaming', 'cycle' => 'monthly', 'aliases' => ['twich', 'twitch']],
            ['name' => 'Audible', 'domain' => 'audible.com', 'category' => 'Entertainment', 'cycle' => 'monthly', 'aliases' => ['audibl', 'audibleplus']],
            ['name' => 'Discord Nitro', 'domain' => 'discord.com', 'category' => 'Gaming', 'cycle' => 'monthly', 'aliases' => ['discrod', 'dicord', 'discordnitro']],
            ['name' => 'PlayStation Plus', 'domain' => 'playstation.com', 'category' => 'Gaming', 'cycle' => 'annual', 'aliases' => ['psplus', 'psn', 'playstation']],
            ['name' => 'Xbox Game Pass', 'domain' => 'xbox.com', 'category' => 'Gaming', 'cycle' => 'monthly', 'aliases' => ['gamepass', 'xbox']],
            ['name' => 'Nintendo Switch Online', 'domain' => 'nintendo.com', 'category' => 'Gaming', 'cycle' => 'annual', 'aliases' => ['nintendo']],

            // Health, Fitness & Wellness
            ['name' => 'Calm', 'domain' => 'calm.com', 'category' => 'Health & Fitness', 'cycle' => 'annual', 'aliases' => ['calmapp', 'calmsleep']],
            ['name' => 'Headspace', 'domain' => 'headspace.com', 'category' => 'Health & Fitness', 'cycle' => 'monthly', 'aliases' => ['headspce', 'head space']],
            ['name' => 'Strava', 'domain' => 'strava.com', 'category' => 'Health & Fitness', 'cycle' => 'annual', 'aliases' => ['strav', 'stravaapp']],
            ['name' => 'MyFitnessPal', 'domain' => 'myfitnesspal.com', 'category' => 'Health & Fitness', 'cycle' => 'monthly', 'aliases' => ['myfitness', 'fitnesspal', 'mfp']],
            ['name' => 'WHOOP', 'domain' => 'whoop.com', 'category' => 'Health & Fitness', 'cycle' => 'annual', 'aliases' => ['whoopband', 'whop']],
            ['name' => 'Oura Membership', 'domain' => 'ouraring.com', 'category' => 'Health & Fitness', 'cycle' => 'monthly', 'aliases' => ['oura', 'ouraring']],
            ['name' => 'Peloton', 'domain' => 'onepeloton.com', 'category' => 'Health & Fitness', 'cycle' => 'monthly', 'aliases' => ['peloton', 'peleton']],
            ['name' => 'Fitbit Premium', 'domain' => 'fitbit.com', 'category' => 'Health & Fitness', 'cycle' => 'monthly', 'aliases' => ['fitbit']],
            ['name' => 'Garmin Connect+', 'domain' => 'garmin.com', 'category' => 'Health & Fitness', 'cycle' => 'monthly', 'aliases' => ['garmin']],
            ['name' => 'Noom', 'domain' => 'noom.com', 'category' => 'Health & Fitness', 'cycle' => 'monthly', 'aliases' => ['noomweight']],
            ['name' => 'BetterHelp', 'domain' => 'betterhelp.com', 'category' => 'Health & Fitness', 'cycle' => 'monthly', 'aliases' => ['better help']],
            ['name' => 'ClassPass', 'domain' => 'classpass.com', 'category' => 'Health & Fitness', 'cycle' => 'monthly', 'aliases' => ['class pass']],
            ['name' => 'Anytime Fitness', 'domain' => 'anytimefitness.com', 'category' => 'Health & Fitness', 'cycle' => 'monthly', 'aliases' => ['anytimefitness', 'anytime']],
            ['name' => 'Fitness First', 'domain' => 'fitnessfirst.com.my', 'category' => 'Health & Fitness', 'cycle' => 'monthly', 'aliases' => ['fitnessfirst']],
            ['name' => 'Flo Health', 'domain' => 'flo.health', 'category' => 'Health & Fitness', 'cycle' => 'annual', 'aliases' => ['flohealth']],
            ['name' => 'AllTrails+', 'domain' => 'alltrails.com', 'category' => 'Health & Fitness', 'cycle' => 'annual', 'aliases' => ['alltrails']],
            ['name' => 'Sleep Cycle', 'domain' => 'sleepcycle.com', 'category' => 'Health & Fitness', 'cycle' => 'annual', 'aliases' => ['sleepcycle']],

            // AI, Software & Cloud
            ['name' => 'GitHub Copilot', 'domain' => 'github.com', 'category' => 'Software & Dev', 'cycle' => 'monthly', 'aliases' => ['githb', 'gitub', 'githubcopilot', 'copilot', 'ghcopilot']],
            ['name' => 'ChatGPT Plus', 'domain' => 'openai.com', 'category' => 'Productivity', 'cycle' => 'monthly', 'aliases' => ['chatgtp', 'chagpt', 'chatgbt', 'chatgpt', 'openai', 'open-ai']],
            ['name' => 'Claude Pro', 'domain' => 'anthropic.com', 'category' => 'Productivity', 'cycle' => 'monthly', 'aliases' => ['claude', 'claud', 'anthropic', 'claudeai', 'claudepro']],
            ['name' => 'Perplexity Pro', 'domain' => 'perplexity.ai', 'category' => 'Productivity', 'cycle' => 'monthly', 'aliases' => ['perplexity']],
            ['name' => 'Cursor Pro', 'domain' => 'cursor.com', 'category' => 'Software & Dev', 'cycle' => 'monthly', 'aliases' => ['cursorai']],
            ['name' => 'Apple iCloud+', 'domain' => 'apple.com', 'category' => 'Cloud Storage', 'cycle' => 'monthly', 'aliases' => ['icloud', 'icloude', 'aple', 'appletv', 'applemusic', 'appleone']],
            ['name' => 'Adobe Creative Cloud', 'domain' => 'adobe.com', 'category' => 'Software & Dev', 'cycle' => 'monthly', 'aliases' => ['adob', 'photoshop', 'illustrator', 'creativecloud', 'lightroom']],
            ['name' => 'Canva Pro', 'domain' => 'canva.com', 'category' => 'Software & Dev', 'cycle' => 'annual', 'aliases' => ['canav', 'canvapro', 'camva']],
            ['name' => 'Figma Professional', 'domain' => 'figma.com', 'category' => 'Software & Dev', 'cycle' => 'monthly', 'aliases' => ['figm', 'figmaa', 'figmapro']],
            ['name' => 'Notion Plus', 'domain' => 'notion.so', 'category' => 'Productivity', 'cycle' => 'monthly', 'aliases' => ['noton', 'notionapp', 'notionai']],
            ['name' => 'Google One', 'domain' => 'google.com', 'category' => 'Cloud Storage', 'cycle' => 'monthly', 'aliases' => ['googl', 'googleone', 'gsuite', 'googleworkspace', 'gdrive']],
            ['name' => 'Microsoft 365', 'domain' => 'microsoft.com', 'category' => 'Productivity', 'cycle' => 'annual', 'aliases' => ['microsft', 'office365', 'm365', 'msft', 'office']],
            ['name' => 'Midjourney', 'domain' => 'midjourney.com', 'category' => 'Software & Dev', 'cycle' => 'monthly', 'aliases' => ['midjourny', 'mid-journey']],
            ['name' => 'Slack Pro', 'domain' => 'slack.com', 'category' => 'Productivity', 'cycle' => 'monthly', 'aliases' => ['slak', 'slackpro']],
            ['name' => 'Zoom One', 'domain' => 'zoom.us', 'category' => 'Productivity', 'cycle' => 'monthly', 'aliases' => ['zoomone', 'zoompro']],
            ['name' => 'Dropbox Plus', 'domain' => 'dropbox.com', 'category' => 'Cloud Storage', 'cycle' => 'monthly', 'aliases' => ['dropbx', 'dropox']],
            ['name' => 'LinkedIn Premium', 'domain' => 'linkedin.com', 'category' => 'Productivity', 'cycle' => 'monthly', 'aliases' => ['linkdin', 'linkind', 'linkedinpremium']],
            ['name' => 'X Premium', 'domain' => 'x.com', 'category' => 'Entertainment', 'cycle' => 'monthly', 'aliases' => ['twitter', 'twiter', 'xpremium', 'twitterblue']],
            ['name' => '1Password', 'domain' => '1password.com', 'category' => 'Software & Dev', 'cycle' => 'annual', 'aliases' => ['onepassword', '1pass', 'onepass']],
            ['name' => 'Bitwarden', 'domain' => 'bitwarden.com', 'category' => 'Software & Dev', 'cycle' => 'annual', 'aliases' => ['bitwardn', 'bitwarder']],
            ['name' => 'NordVPN', 'domain' => 'nordvpn.com', 'category' => 'Software & Dev', 'cycle' => 'annual', 'aliases' => ['nordvpn', 'nord-vpn']],
            ['name' => 'ExpressVPN', 'domain' => 'expressvpn.com', 'category' => 'Software & Dev', 'cycle' => 'annual', 'aliases' => ['expressvpn', 'express-vpn']],
            ['name' => 'Surfshark', 'domain' => 'surfshark.com', 'category' => 'Software & Dev', 'cycle' => 'annual', 'aliases' => ['surfshark']],
            ['name' => 'Proton Mail', 'domain' => 'proton.me', 'category' => 'Productivity', 'cycle' => 'annual', 'aliases' => ['proton', 'protonmail', 'protonvpn']],
            ['name' => 'Vercel Pro', 'domain' => 'vercel.com', 'category' => 'Software & Dev', 'cycle' => 'monthly', 'aliases' => ['versel', 'vercel']],
            ['name' => 'Supabase Pro', 'domain' => 'supabase.com', 'category' => 'Software & Dev', 'cycle' => 'monthly', 'aliases' => ['superbase']],
            ['name' => 'DigitalOcean', 'domain' => 'digitalocean.com', 'category' => 'Software & Dev', 'cycle' => 'monthly', 'aliases' => ['docean', 'digitalocean']],
            ['name' => 'AWS', 'domain' => 'aws.amazon.com', 'category' => 'Software & Dev', 'cycle' => 'monthly', 'aliases' => ['amazonwebservices']],
            ['name' => 'Linear', 'domain' => 'linear.app', 'category' => 'Productivity', 'cycle' => 'monthly', 'aliases' => ['linar', 'linearapp']],
            ['name' => 'Grammarly Premium', 'domain' => 'grammarly.com', 'category' => 'Productivity', 'cycle' => 'annual', 'aliases' => ['grammerly', 'gramarly', 'gramerly']],
            ['name' => 'JetBrains', 'domain' => 'jetbrains.com', 'category' => 'Software & Dev', 'cycle' => 'annual', 'aliases' => ['phpstorm', 'intellij', 'webstorm']],

            // Education, Reading, Finance & Everyday Services
            ['name' => 'Duolingo Super', 'domain' => 'duolingo.com', 'category' => 'Education', 'cycle' => 'annual', 'aliases' => ['doulingo', 'duolingosuper']],
            ['name' => 'Coursera Plus', 'domain' => 'coursera.org', 'category' => 'News & Reading', 'cycle' => 'annual', 'aliases' => ['corsera', 'coursera']],
            ['name' => 'Udemy', 'domain' => 'udemy.com', 'category' => 'Education', 'cycle' => 'monthly', 'aliases' => ['udemypro']],
            ['name' => 'Skillshare', 'domain' => 'skillshare.com', 'category' => 'Education', 'cycle' => 'annual', 'aliases' => ['skillshare']],
            ['name' => 'MasterClass', 'domain' => 'masterclass.com', 'category' => 'Education', 'cycle' => 'annual', 'aliases' => ['masterclass']],
            ['name' => 'Brilliant', 'domain' => 'brilliant.org', 'category' => 'Education', 'cycle' => 'annual', 'aliases' => ['brilliant']],
            ['name' => 'Medium Membership', 'domain' => 'medium.com', 'category' => 'News & Reading', 'cycle' => 'monthly', 'aliases' => ['medum', 'medium']],
            ['name' => 'Substack', 'domain' => 'substack.com', 'category' => 'News & Reading', 'cycle' => 'monthly', 'aliases' => ['substak', 'substack']],
            ['name' => 'GrabUnlimited', 'domain' => 'grab.com', 'category' => 'Transport', 'cycle' => 'monthly', 'aliases' => ['grabunlimited', 'grabfood']],
            ['name' => 'Wise', 'domain' => 'wise.com', 'category' => 'Finance', 'cycle' => 'monthly', 'aliases' => ['transferwise']],
            ['name' => 'Revolut', 'domain' => 'revolut.com', 'category' => 'Finance', 'cycle' => 'monthly', 'aliases' => ['revolutmetal']],
            ['name' => 'YNAB', 'domain' => 'ynab.com', 'category' => 'Finance', 'cycle' => 'annual', 'aliases' => ['youneedabudget']],
            ['name' => 'AIA Health', 'domain' => 'aia.com.my', 'category' => 'Health & Fitness', 'cycle' => 'monthly', 'aliases' => ['aiamalaysia', 'aiainsurance']],
            ['name' => 'Prudential', 'domain' => 'prudential.com.my', 'category' => 'Health & Fitness', 'cycle' => 'monthly', 'aliases' => ['prudential']],
        ];
    }

    /**
     * Build the primary Google Favicon V2 URL for a clean domain.
     */
    public function googleFaviconUrl(string $domain, int $size = 128): string
    {
        return self::GOOGLE_FAVICON_BASE.'?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url='
            .urlencode("https://{$domain}")."&size={$size}";
    }

    /**
     * Build the DuckDuckGo icon proxy URL for a clean domain.
     */
    public function duckDuckGoIconUrl(string $domain): string
    {
        return self::DUCKDUCKGO_ICON_BASE."/{$domain}.ico";
    }

    /**
     * Extract clean candidate hostnames from a URL or domain string (exact host then root domain).
     *
     * @return array<int, string>
     */
    public function extractDomainCandidates(string $input): array
    {
        $host = $this->cleanHost($input);

        if ($host === '') {
            return [];
        }

        $candidates = [$host];
        $root = $this->extractRootDomain($host);

        if ($root !== '' && $root !== $host) {
            $candidates[] = $root;
        }

        return $candidates;
    }

    /**
     * Find a matching catalog entry by brand name, alias, or domain.
     *
     * @return array{name: string, domain: string, category: string, cycle: string, aliases: array<int, string>}|null
     */
    public function matchByName(string $name): ?array
    {
        $needle = strtolower(trim($name));

        if ($needle === '') {
            return null;
        }

        foreach ($this->catalog() as $entry) {
            if ($this->entryMatchesName($entry, $needle)) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Resolve the best working icon URL and optional brand metadata for any commercial domain.
     *
     * @return array{domain: string, logo_url: string, fallback_url: string, matched_brand: array{name: string, domain: string, category: string, cycle: string, aliases: array<int, string>}|null}|null
     */
    public function resolveForDomain(string $rawDomain): ?array
    {
        $candidates = $this->extractDomainCandidates($rawDomain);

        if ($candidates === []) {
            return null;
        }

        $primaryDomain = $candidates[0];
        $matchedBrand = $this->matchByDomainCandidates($candidates);
        $resolvedUrl = Cache::remember(
            'brand_icon:'.md5(implode('|', $candidates)),
            now()->addHours(24),
            fn (): string => $this->probeProviders($candidates)
        );

        return [
            'domain' => $primaryDomain,
            'logo_url' => $resolvedUrl,
            'fallback_url' => $this->duckDuckGoIconUrl($candidates[count($candidates) - 1]),
            'matched_brand' => $matchedBrand,
        ];
    }

    /**
     * Normalize a URL or hostname into a clean lowercase host without www.
     */
    public function cleanHost(string $input): string
    {
        $trimmed = strtolower(trim($input));

        if ($trimmed === '') {
            return '';
        }

        $withScheme = preg_match('~^https?://~i', $trimmed) ? $trimmed : "https://{$trimmed}";
        $host = parse_url($withScheme, PHP_URL_HOST) ?? '';
        $clean = (string) preg_replace('/^www\./i', '', $host);

        return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $clean) ? $clean : '';
    }

    /**
     * Probe Google Favicon V2, DuckDuckGo, and Icon Horse across domain candidates.
     *
     * @param  array<int, string>  $candidates
     */
    private function probeProviders(array $candidates): string
    {
        foreach ($candidates as $domain) {
            foreach ($this->providerUrls($domain) as $url) {
                if ($this->isReachableImage($url)) {
                    return $url;
                }
            }
        }

        return $this->googleFaviconUrl($candidates[0]);
    }

    /**
     * Build ordered icon provider URLs for a single candidate domain.
     *
     * @return array<int, string>
     */
    private function providerUrls(string $domain): array
    {
        return [
            $this->googleFaviconUrl($domain),
            $this->duckDuckGoIconUrl($domain),
            self::ICON_HORSE_BASE."/{$domain}",
        ];
    }

    /**
     * Verify via HTTP HEAD/GET that an icon provider URL returns a non-empty image response.
     */
    private function isReachableImage(string $url): bool
    {
        try {
            $response = Http::timeout(2)
                ->withHeaders(['User-Agent' => 'SubShield-IconResolver/1.0'])
                ->get($url);

            if (! $response->successful()) {
                return false;
            }

            $contentType = strtolower((string) $response->header('Content-Type'));

            return str_starts_with($contentType, 'image/') && strlen($response->body()) > 64;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Collapse subdomains (e.g. app.calm.com or portal.aia.com.my) to their root domain.
     */
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

    /**
     * Match a catalog brand against normalized domain candidates.
     *
     * @param  array<int, string>  $candidates
     * @return array{name: string, domain: string, category: string, cycle: string, aliases: array<int, string>}|null
     */
    private function matchByDomainCandidates(array $candidates): ?array
    {
        foreach ($this->catalog() as $entry) {
            if (in_array($entry['domain'], $candidates, true)) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Check whether a brand catalog entry matches a service name string.
     *
     * @param  array{name: string, domain: string, category: string, cycle: string, aliases: array<int, string>}  $entry
     */
    private function entryMatchesName(array $entry, string $needle): bool
    {
        $brandKey = strtolower(explode('.', $entry['domain'])[0]);
        $candidates = [strtolower($entry['name']), $brandKey];

        foreach ($candidates as $candidate) {
            if ($this->matchesToken($needle, $candidate)) {
                return true;
            }
        }

        foreach ($entry['aliases'] as $alias) {
            if ($this->matchesToken($needle, strtolower($alias))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Match long brand tokens by substring and short tokens (< 4 chars) by word boundary.
     */
    private function matchesToken(string $needle, string $token): bool
    {
        if (strlen($token) < 4) {
            return (bool) preg_match('/\b'.preg_quote($token, '/').'\b/i', $needle);
        }

        return str_contains($needle, $token);
    }
}
