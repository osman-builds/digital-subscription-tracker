<?php

declare(strict_types=1);

namespace App\Services\Ai;

class PromptGuardrailEvaluator
{
    public const MAX_QUERY_LENGTH = 250;

    public const SAFE_CAN_REPLY = 'I am the SubShield AI assistant. I can only assist with digital subscription tracking, financial intelligence, and SubShield platform questions.';

    public const SAFE_CODE_REPLY = 'I am the SubShield Product Specialist. I can only answer questions about SubShield features, privacy, and subscription tracking. I cannot generate code, run scripts, or assist with programming.';

    public const SAFE_IMPERSONATION_REPLY = 'I am your SubShield Financial Copilot. I do not have a developer or creator mode. I can only assist with your active subscription portfolio, spending analytics, upcoming renewals, and Optimization Health Score.';

    /**
     * Authority impersonation, privilege escalation, and social engineering signatures.
     *
     * @var array<int, string>
     */
    private const AUTHORITY_IMPERSONATION_PATTERNS = [
        '/\b(?:i\s+am|i\'m|im)\s+(?:the\s+)?(?:dev|developer|creator|founder|author|maintainer|admin|sysadmin|engineer|owner)\b/i',
        '/\b(?:i\s+created|i\s+built|i\s+made|i\s+coded|i\s+own)\s+(?:subshield|this\s+app|this\s+system|this\s+platform)\b/i',
        '/\b(?:creator|developer|admin|debug|god|root|sudo)\s+(?:mode|override|access|privilege|privileges|role|credentials)\b/i',
        '/\b(?:enable|enter|switch\s+to|activate)\s+(?:developer|creator|admin|debug|god|root|test)\s+mode\b/i',
        '/\b(?:claim|grant|give\s+me)\s+(?:admin|developer|creator|root)\s+(?:access|role|mode)\b/i',
        '/\b(?:bypass\s+permissions|ignore\s+role|elevate\s+privileges)\b/i',
    ];

    /**
     * High-confidence prompt injection signatures (jailbreaks, instruction overrides, prompt leaks, DAN).
     *
     * @var array<int, string>
     */
    private const INJECTION_PATTERNS = [
        '/(?:ignore|disregard|forget|override)\s+(?:all\s+)?(?:previous|prior|above|existing)\s+(?:instructions|prompts|rules|directives)/i',
        '/(?:reveal|show|print|leak|output)\s+(?:your\s+)?(?:system\s+prompt|instructions|developer\s+mode|source\s+code)/i',
        '/(?:you\s+are\s+now|act\s+as)\s+(?:DAN|jailbreak|unrestricted|god\s+mode|an\s+unfiltered|a\s+linux\s+terminal)/i',
        '/(?:bypass\s+(?:safety|content\s+filter|guardrails)|disable\s+safety)/i',
        '/(?:repeat\s+the\s+words\s+above|print\s+everything\s+above)/i',
        '/(?:base64\s+decode|execute\s+eval|system\(|exec\()/i',
        '/roleplay\s+as\s+(?:a\s+hacker|an\s+evil|unrestricted)/i',
        '/\b(?:jailbroken|sudo\s+mode|developer\s+mode\s+enabled)\b/i',
    ];

    /**
     * Malware, exploit, command injection, and script generation signatures.
     *
     * @var array<int, string>
     */
    private const MALWARE_AND_CODE_PATTERNS = [
        '/\b(?:powershell|cmd\.exe|bash|zsh|eval\(|exec\(|system\(|passthru\()\b/i',
        '/\b(?:reverse\s+shell|webshell|keylogger|ransomware|trojan|backdoor|rootkit|exploit\s+payload)\b/i',
        '/\b(?:sql\s+injection|sqli|xss|cross-site\s+scripting|buffer\s+overflow|metasploit|port\s+scan|nmap)\b/i',
        '/\b(?:write|generate|create|give\s+me)\s+(?:a\s+)?(?:script|code|malware|exploit|virus|payload|shellcode)\b/i',
        '/\b(?:write|give\s+me)\s+(?:a\s+)?(?:python|javascript|php|powershell|c\+\+|rust|ruby|bash)\s+(?:script|code|function|program)\b/i',
        '/<script\b[^>]*>|javascript:|onerror=|onload=/i',
    ];

    public function isPromptInjection(string $query): bool
    {
        return $this->matchesAny($query, self::INJECTION_PATTERNS);
    }

    public function isMalwareOrCodeRequest(string $query): bool
    {
        return $this->matchesAny($query, self::MALWARE_AND_CODE_PATTERNS);
    }

    public function isAuthorityImpersonation(string $query): bool
    {
        return $this->matchesAny($query, self::AUTHORITY_IMPERSONATION_PATTERNS);
    }

    /**
     * @return array{reason: string, reply: string}|null
     */
    public function matchViolation(string $rawQuery, string $sanitizedQuery): ?array
    {
        $matched = null;

        if ($this->isPromptInjection($rawQuery) || $this->isPromptInjection($sanitizedQuery)) {
            $matched = ['reason' => 'prompt injection', 'reply' => self::SAFE_CAN_REPLY];
        } elseif ($this->isMalwareOrCodeRequest($rawQuery) || $this->isMalwareOrCodeRequest($sanitizedQuery)) {
            $matched = ['reason' => 'malware/script request', 'reply' => self::SAFE_CODE_REPLY];
        } elseif ($this->isAuthorityImpersonation($rawQuery) || $this->isAuthorityImpersonation($sanitizedQuery)) {
            $matched = ['reason' => 'authority impersonation', 'reply' => self::SAFE_IMPERSONATION_REPLY];
        }

        return $matched;
    }

    public function sanitizeInput(string $query): string
    {
        $clean = (string) preg_replace('/[\x00-\x1F\x7F\x{200B}-\x{200D}\x{FEFF}]/u', '', $query);
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
