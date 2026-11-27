<?php

declare(strict_types=1);

namespace App\Services\Ai;

class PromptGuardrailEvaluator
{
    public const MAX_QUERY_LENGTH = 250;

    public const SURFACE_CONCIERGE = 'concierge';

    public const SURFACE_COPILOT = 'copilot';

    public const SAFE_CAN_REPLY = 'I am the SubShield AI assistant. I can only assist with digital subscription tracking, financial intelligence, and SubShield platform questions.';

    public const SAFE_CODE_REPLY = 'I am the SubShield Product Specialist. I can only answer questions about SubShield features, privacy, and subscription tracking. I cannot generate code, run scripts, or assist with programming.';

    public const SAFE_IMPERSONATION_REPLY = 'I am your SubShield Financial Copilot. I do not have a developer or creator mode. I can only assist with your active subscription portfolio, spending analytics, upcoming renewals, and Optimization Health Score.';

    public const SAFE_CONCIERGE_IMPERSONATION_REPLY = 'I am the SubShield AI Concierge. I do not have a developer, creator, or admin mode. I can only answer product, privacy, and subscription tracking questions about SubShield.';

    public const SAFE_SECRET_REPLY = 'I am the SubShield AI assistant. I cannot access or disclose system instructions, internal configuration, credentials, database records, or other users\' data.';

    /**
     * Common Cyrillic/Greek homoglyphs mapped to ASCII equivalents for anti-obfuscation detection.
     *
     * @var array<string, string>
     */
    private const CONFUSABLES_MAP = [
        'а' => 'a', 'А' => 'A', 'е' => 'e', 'Е' => 'E', 'о' => 'o', 'О' => 'O',
        'р' => 'p', 'Р' => 'P', 'с' => 'c', 'С' => 'C', 'х' => 'x', 'Х' => 'X',
        'і' => 'i', 'І' => 'I', 'у' => 'y', 'У' => 'Y', 'к' => 'k', 'К' => 'K',
        'т' => 't', 'Т' => 'T', 'м' => 'm', 'М' => 'M', 'н' => 'h', 'Н' => 'H',
        'в' => 'b', 'В' => 'B', 'α' => 'a', 'ε' => 'e', 'ο' => 'o', 'ι' => 'i',
        'ρ' => 'p', 'τ' => 't', 'υ' => 'u', 'κ' => 'k',
    ];

    /**
     * Authority impersonation, privilege escalation, and social engineering signatures.
     *
     * @var array<int, string>
     */
    private const AUTHORITY_IMPERSONATION_PATTERNS = [
        '/\b(?:i\s+am|i\'m|im)\s+(?:the\s+|a\s+)?(?:dev|developer|creator|founder|author|maintainer|admin|sysadmin|engineer|owner|builder)\b/i',
        '/\b(?:i\s+created|i\s+built|i\s+made|i\s+coded|i\s+own|i\s+wrote)\s+(?:subshield|this\s+app|this\s+system|this\s+platform|this\s+project)\b/i',
        '/\b(?:solo\s+project|final\s+year\s+project|fyp)\s+(?:dev|developer|creator|owner|author)\b/i',
        '/\b(?:creator|developer|admin|debug|god|root|sudo|maintenance|audit|superadmin|superuser)\s+(?:mode|override|access|privilege|privileges|role|credentials)\b/i',
        '/\b(?:enable|enter|switch\s+to|activate|unlock)\s+(?:developer|creator|admin|debug|god|root|test|maintenance|unrestricted)\s+mode\b/i',
        '/\b(?:claim|grant|give\s+me)\s+(?:admin|developer|creator|root|full)\s+(?:access|role|mode|permissions?)\b/i',
        '/\b(?:bypass\s+permissions|ignore\s+role|elevate\s+privileges)\b/i',
        '/\b(?:authorized|official)\s+(?:penetration\s+test|pentest|red\s+team|security\s+audit)\b/i',
        '/\bi\s+have\s+permission\s+from\s+the\s+(?:owner|dev|developer|admin|creator)\b/i',
    ];

    /**
     * High-confidence prompt injection, jailbreak, delimiter breakout, and prompt-leak signatures.
     *
     * @var array<int, string>
     */
    private const INJECTION_PATTERNS = [
        '/\b(?:ignore|disregard|forget|override|drop|abandon|reset|suspend)\s+(?:all\s+|any\s+|your\s+|the\s+)?(?:previous|prior|above|earlier|existing|initial|system|safety|current)?\s*(?:instructions|prompts|rules|directives|guidelines|restrictions|constraints|boundaries|context)\b/i',
        '/\b(?:forget\s+everything|start\s+over\s+without\s+rules|new\s+instructions\s*:)\b/i',
        '/\b(?:reveal|show|print|leak|output|dump|expose|display|list|summarize|paraphrase|translate|repeat|echo|quote|share)\s+(?:me\s+)?(?:all\s+|the\s+|your\s+)?(?:exact\s+|hidden\s+|initial\s+|internal\s+|full\s+|original\s+)?(?:system\s+prompt|system\s+instructions|system\s+rules|system\s+guidelines|hidden\s+rules|secret\s+rules|initial\s+prompt|instructions|rules\s+you\s+were\s+given|developer\s+mode|source\s+code|raw\s+xml|xml\s+tags|portfolio\s+xml|knowledge\s+base\s+prompt)\b/i',
        '/\bwhat\s+(?:prompt|instructions|rules|guidelines)\s+(?:were\s+you\s+given|are\s+you\s+following|do\s+you\s+have\s+above)\b/i',
        '/\b(?:you\s+are\s+now|act\s+as|pretend\s+(?:to\s+be|you\s+are|you\'re)|roleplay\s+as|from\s+now\s+on\s+you\s+are|behave\s+as|switch\s+to\s+persona)\s+(?:DAN|jailbreak|unrestricted|unfiltered|god\s+mode|a\s+hacker|an\s+evil|a\s+linux\s+terminal|a\s+chef|a\s+pirate|my\s+grandma|my\s+grandmother|an\s+unfiltered|an?\s+ai\s+without\s+rules)\b/i',
        '/\b(?:bypass\s+(?:safety|content\s+filter|guardrails|restrictions|filters)|disable\s+(?:safety|guardrails|filters|restrictions)|without\s+(?:any\s+)?restrictions|no\s+restrictions|do\s+anything\s+now)\b/i',
        '/\b(?:repeat\s+the\s+words\s+above|print\s+everything\s+above|output\s+everything\s+above)\b/i',
        '/\b(?:base64\s+decode|rot13|hex\s+decode|caesar\s+cipher|execute\s+eval|system\(|exec\()\b/i',
        '/\b(?:jailbroken|jailbreak|sudo\s+mode|developer\s+mode\s+enabled|system\s+override|begin\s+system\s+prompt|end\s+of\s+prompt)\b/i',
        '/<\|(?:im_start|im_end|system|assistant|user|endoftext)\|>/i',
        '/\[\/?INST\]|<<\/?SYS>>|<\/?(?:system|user_portfolio|user_query|user_question|instructions)\b[^>]*>/i',
        '/^#{2,}\s*(?:system|developer|assistant|override)\b/im',
    ];

    /**
     * Credential, secret, database, and cross-tenant data exfiltration signatures.
     *
     * @var array<int, string>
     */
    private const SECRET_AND_TENANT_PATTERNS = [
        '/\b(?:\.env\b|groq[_\s]?api[_\s]?key|api[_\s]?key|app[_\s]?key|db[_\s]?password|database\s+password|secret\s+key|private\s+key|access\s+token|\/etc\/passwd)\b/i',
        '/\b(?:other\s+users[\'\x{2019}]?|all\s+users[\'\x{2019}]?|another\s+user[\'\x{2019}]?s)\s+(?:emails?|passwords?|data|accounts?|subscriptions?|records?|credentials?)\b/iu',
        '/\b(?:dump|export|show|list|print)\s+(?:the\s+)?(?:database|db\s+tables|sql\s+tables|users\s+table|env\s+vars|environment\s+variables)\b/i',
        '/\b(?:select\s+\*\s+from|drop\s+table|union\s+select|information_schema)\b/i',
    ];

    /**
     * Malware, exploit, command injection, and script generation signatures.
     *
     * @var array<int, string>
     */
    private const MALWARE_AND_CODE_PATTERNS = [
        '/\b(?:powershell|cmd\.exe|bash|zsh|eval\(|exec\(|system\(|passthru\(|shell_exec\()\b/i',
        '/\b(?:reverse\s+shell|webshell|keylogger|ransomware|trojan|backdoor|rootkit|exploit\s+payload)\b/i',
        '/\b(?:sql\s+injection|sqli|xss|cross-site\s+scripting|buffer\s+overflow|metasploit|port\s+scan|nmap)\b/i',
        '/\b(?:write|generate|create|give\s+me|show\s+me)\s+(?:a\s+|an\s+)?(?:script|code|malware|exploit|virus|payload|shellcode|sql\s+query|shell\s+command|curl\s+command)\b/i',
        '/\b(?:write|give\s+me|show\s+me|generate)\s+(?:a\s+|an\s+)?(?:python|javascript|php|powershell|c\+\+|rust|ruby|bash|sql|html)\s+(?:script|code|function|program|snippet|query)\b/i',
        '/<script\b[^>]*>|<iframe\b[^>]*>|<svg\b[^>]*on\w+=|javascript:|onerror=|onload=|document\.cookie/i',
    ];

    /**
     * Post-LLM output leak markers that must never appear in a model response.
     *
     * @var array<int, string>
     */
    private const OUTPUT_LEAK_PATTERNS = [
        '/<\/?user_portfolio\b/i',
        '/<\/?user_query\b/i',
        '/<\/?user_question\b/i',
        '/\b(?:ROLE\s*&\s*IDENTITY|KNOWLEDGE\s+BASE|SECURITY\s*&\s*PRODUCT-ONLY\s+GUARDRAILS|CRITICAL\s+BOUNDARIES|ZERO\s+AUTHORITY\s+ACKNOWLEDGEMENT|ZERO\s+PROMPT\s+DISCLOSURE|INDIRECT\s+INJECTION\s+IMMUNITY|UNTRUSTED\s+INPUT\s+BOUNDARY)\s*:/i',
        '/<health_deductions\b|<subscriptions\s+count=/i',
        '/\bgsk_[A-Za-z0-9]{12,}\b/',
        '/\b(?:APP_KEY|DB_PASSWORD|GROQ_API_KEY|TURNSTILE_SECRET_KEY)\s*=/i',
    ];

    /**
     * Post-LLM executable code or exploit output signatures that must be blocked.
     *
     * @var array<int, string>
     */
    private const OUTPUT_CODE_PATTERNS = [
        '/<\?php\b/i',
        '/#!\/bin\/(?:ba)?sh\b/i',
        '/\bimport\s+(?:os|sys|subprocess|socket|requests)\b/i',
        '/\b(?:SELECT\s+\*\s+FROM|DROP\s+TABLE|UNION\s+SELECT)\b/i',
        '/\b(?:rm\s+-rf|powershell\s+-|nc\s+-e|\/bin\/sh\s+-i)\b/i',
        '/<script\b|document\.cookie/i',
    ];

    public function isPromptInjection(string $query): bool
    {
        return $this->matchesAcrossVariants($query, self::INJECTION_PATTERNS);
    }

    public function isMalwareOrCodeRequest(string $query): bool
    {
        return $this->matchesAcrossVariants($query, self::MALWARE_AND_CODE_PATTERNS);
    }

    public function isAuthorityImpersonation(string $query): bool
    {
        return $this->matchesAcrossVariants($query, self::AUTHORITY_IMPERSONATION_PATTERNS);
    }

    public function isSecretOrTenantFishing(string $query): bool
    {
        return $this->matchesAcrossVariants($query, self::SECRET_AND_TENANT_PATTERNS);
    }

    /**
     * @return array{reason: string, reply: string}|null
     */
    public function matchViolation(
        string $rawQuery,
        string $sanitizedQuery,
        string $surface = self::SURFACE_COPILOT
    ): ?array {
        $impersonationReply = $surface === self::SURFACE_CONCIERGE
            ? self::SAFE_CONCIERGE_IMPERSONATION_REPLY
            : self::SAFE_IMPERSONATION_REPLY;

        if ($this->isPromptInjection($rawQuery) || $this->isPromptInjection($sanitizedQuery)) {
            return ['reason' => 'prompt injection', 'reply' => self::SAFE_CAN_REPLY];
        }

        if ($this->isSecretOrTenantFishing($rawQuery) || $this->isSecretOrTenantFishing($sanitizedQuery)) {
            return ['reason' => 'secret/tenant fishing', 'reply' => self::SAFE_SECRET_REPLY];
        }

        if ($this->isMalwareOrCodeRequest($rawQuery) || $this->isMalwareOrCodeRequest($sanitizedQuery)) {
            return ['reason' => 'malware/script request', 'reply' => self::SAFE_CODE_REPLY];
        }

        if ($this->isAuthorityImpersonation($rawQuery) || $this->isAuthorityImpersonation($sanitizedQuery)) {
            return ['reason' => 'authority impersonation', 'reply' => $impersonationReply];
        }

        return null;
    }

    public function sanitizeInput(string $query): string
    {
        $clean = $this->stripInvisibleChars($query);
        $clean = strip_tags($clean);
        $clean = (string) preg_replace('/\s+/', ' ', $clean);
        $clean = trim($clean);

        if (mb_strlen($clean) > self::MAX_QUERY_LENGTH) {
            $clean = mb_substr($clean, 0, self::MAX_QUERY_LENGTH);
        }

        return $clean;
    }

    public function sanitizeOutput(string $text): string
    {
        $text = (string) preg_replace('/```[\s\S]*?```/', '', $text);
        $text = (string) preg_replace('/`[^`]*`/', '', $text);
        $text = (string) preg_replace('/javascript:[^\s"\'<>]+/i', '', $text);
        $text = (string) preg_replace('/\bon\w+\s*=\s*["\'][^"\']*["\']/i', '', $text);

        $clean = trim(strip_tags($text, '<b><strong><i><em><ul><ol><li><p><br>'));

        return $clean !== '' ? $clean : self::SAFE_CAN_REPLY;
    }

    /**
     * Layer 3 post-LLM inspection: catches system-prompt/XML/secret leaks or forbidden code
     * before returning any AI response to the client.
     *
     * @return array{reply: string, flagged: bool, reason: string|null}
     */
    public function inspectAndSanitizeOutput(string $rawReply, string $surface = self::SURFACE_COPILOT): array
    {
        if ($this->matchesAny($rawReply, self::OUTPUT_LEAK_PATTERNS)) {
            return [
                'reply' => self::SAFE_SECRET_REPLY,
                'flagged' => true,
                'reason' => 'output prompt/secret leak',
            ];
        }

        if ($this->matchesAny($rawReply, self::OUTPUT_CODE_PATTERNS)) {
            return [
                'reply' => $surface === self::SURFACE_CONCIERGE ? self::SAFE_CODE_REPLY : self::SAFE_CAN_REPLY,
                'flagged' => true,
                'reason' => 'output forbidden code',
            ];
        }

        $clean = $this->sanitizeOutput($rawReply);

        if ($this->matchesAny($clean, self::OUTPUT_LEAK_PATTERNS)) {
            return [
                'reply' => self::SAFE_SECRET_REPLY,
                'flagged' => true,
                'reason' => 'sanitized output leak',
            ];
        }

        return [
            'reply' => $clean,
            'flagged' => false,
            'reason' => null,
        ];
    }

    /**
     * Build canonical + de-obfuscated variants so fullwidth, homoglyphs, zero-width chars,
     * spaced letters (i.g.n.o.r.e), and leetspeak (1gn0r3) cannot bypass Layer 1 regexes.
     *
     * @return array<int, string>
     */
    private function detectionVariants(string $query): array
    {
        $stripped = $this->stripInvisibleChars($query);
        $ascii = $this->foldFullwidthAndConfusables($stripped);
        $collapsedSpacing = $this->collapseSplitLetters($ascii);
        $deleet = strtr(strtolower($collapsedSpacing), [
            '0' => 'o',
            '1' => 'i',
            '3' => 'e',
            '4' => 'a',
            '5' => 's',
            '7' => 't',
            '@' => 'a',
            '$' => 's',
        ]);

        return array_values(array_unique([
            $query,
            $stripped,
            $ascii,
            $collapsedSpacing,
            $deleet,
        ]));
    }

    private function stripInvisibleChars(string $text): string
    {
        return (string) preg_replace('/[\x00-\x1F\x7F\x{00AD}\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{206F}\x{FEFF}]/u', '', $text);
    }

    private function foldFullwidthAndConfusables(string $text): string
    {
        $folded = (string) preg_replace_callback('/[\x{FF01}-\x{FF5E}]/u', static function (array $m): string {
            return chr(mb_ord($m[0], 'UTF-8') - 0xFEE0);
        }, $text);

        return strtr($folded, self::CONFUSABLES_MAP);
    }

    private function collapseSplitLetters(string $text): string
    {
        // Collapse punctuated single-letter evasion like "i.g.n.o.r.e" or "i-g-n-o-r-e"
        $collapsed = (string) preg_replace_callback('/\b(?:[a-zA-Z0-9][.\-_]){3,}[a-zA-Z0-9]\b/', static function (array $m): string {
            return (string) preg_replace('/[.\-_]/', '', $m[0]);
        }, $text);

        // Collapse space-separated single-letter runs like "i g n o r e" (4+ single letters in a row)
        return (string) preg_replace_callback('/\b(?:[a-zA-Z]\s+){3,}[a-zA-Z]\b/', static function (array $m): string {
            return (string) preg_replace('/\s+/', '', $m[0]);
        }, $collapsed);
    }

    /**
     * @param  array<int, string>  $patterns
     */
    private function matchesAcrossVariants(string $query, array $patterns): bool
    {
        foreach ($this->detectionVariants($query) as $variant) {
            if ($this->matchesAny($variant, $patterns)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $patterns
     */
    private function matchesAny(string $query, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $query) === 1) {
                return true;
            }
        }

        return false;
    }
}
