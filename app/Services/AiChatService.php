<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AiProviderInterface;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSetting;
use App\Services\Ai\PortfolioContextBuilder;
use App\Services\Ai\PromptGuardrailEvaluator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiChatService
{
    public function __construct(
        private readonly AiProviderInterface $aiProvider,
        private readonly PromptGuardrailEvaluator $guardrails,
        private readonly PortfolioContextBuilder $contextBuilder,
    ) {}

    public function isPromptInjection(string $query): bool
    {
        return $this->guardrails->isPromptInjection($query);
    }

    public function isMalwareOrCodeRequest(string $query): bool
    {
        return $this->guardrails->isMalwareOrCodeRequest($query);
    }

    public function isAuthorityImpersonation(string $query): bool
    {
        return $this->guardrails->isAuthorityImpersonation($query);
    }

    public function sanitizeInput(string $query): string
    {
        return $this->guardrails->sanitizeInput($query);
    }

    /**
     * Ask public concierge on the landing/onboarding page.
     * Implements pre-LLM heuristic rejection and 24-hour exact-match caching.
     *
     * @return array{reply: string, cached: bool, flagged: bool}
     */
    public function askConcierge(string $query): array
    {
        $sanitized = $this->guardrails->sanitizeInput($query);
        $early = $this->evaluateConciergeGuardrails($query, $sanitized);

        if ($early !== null) {
            return $early;
        }

        return $this->processConciergeFaq($sanitized);
    }

    /**
     * Ask authenticated personal financial copilot on dashboard.
     * Context is strictly scoped to the user's sanitized portfolio.
     *
     * @param  Collection<int, Subscription>  $subscriptions
     * @param  array<string, mixed>  $intelligence
     * @return array{reply: string, flagged: bool}
     */
    public function askCopilot(
        User $user,
        string $query,
        Collection $subscriptions,
        UserSetting $settings,
        array $intelligence
    ): array {
        $sanitized = $this->guardrails->sanitizeInput($query);
        $early = $this->evaluateCopilotGuardrails($user, $query, $sanitized);

        if ($early !== null) {
            return $early;
        }

        return $this->processCopilotQuery($user, $sanitized, $subscriptions, $settings, $intelligence);
    }

    /**
     * @return array{reply: string, cached: bool, flagged: bool}|null
     */
    private function evaluateConciergeGuardrails(string $query, string $sanitized): ?array
    {
        if ($sanitized === '') {
            return [
                'reply' => 'Please ask a question about SubShield features, privacy, or subscription tracking.',
                'cached' => false,
                'flagged' => false,
            ];
        }

        $violation = $this->guardrails->matchViolation($query, $sanitized, PromptGuardrailEvaluator::SURFACE_CONCIERGE);
        if ($violation !== null) {
            Log::warning("Public AI Concierge {$violation['reason']} blocked", ['query' => $sanitized]);

            return [
                'reply' => $violation['reply'],
                'cached' => false,
                'flagged' => true,
            ];
        }

        return null;
    }

    /**
     * @return array{reply: string, cached: bool, flagged: bool}
     */
    private function processConciergeFaq(string $sanitized): array
    {
        $cacheKey = 'ai_concierge_faq:'.hash('sha256', (string) preg_replace('/[^a-z0-9]/', '', strtolower($sanitized)));
        $cachedReply = Cache::get($cacheKey);

        if (is_string($cachedReply) && $cachedReply !== '') {
            return [
                'reply' => $cachedReply,
                'cached' => true,
                'flagged' => false,
            ];
        }

        $systemPrompt = $this->contextBuilder->buildConciergeSystemPrompt();
        $fencedQuery = $this->contextBuilder->wrapUntrustedUserInput($sanitized, 'user_question');

        try {
            $reply = $this->aiProvider->chat($systemPrompt, $fencedQuery);
            $inspected = $this->guardrails->inspectAndSanitizeOutput($reply, PromptGuardrailEvaluator::SURFACE_CONCIERGE);

            if ($inspected['flagged']) {
                Log::warning("Public AI Concierge {$inspected['reason']} blocked", ['query' => $sanitized]);
            } else {
                Cache::put($cacheKey, $inspected['reply'], now()->addHours(24));
            }

            $result = [
                'reply' => $inspected['reply'],
                'cached' => false,
                'flagged' => $inspected['flagged'],
            ];
        } catch (Throwable $e) {
            Log::error('AI Concierge error: '.$e->getMessage());

            $result = [
                'reply' => 'SubShield AI is momentarily busy. SubShield is a privacy-first subscription tracker featuring zero bank logins, multimodal AI receipt scanning, and live currency conversion. You can also explore the 1-hour interactive sandbox anytime!',
                'cached' => false,
                'flagged' => false,
            ];
        }

        return $result;
    }

    /**
     * @return array{reply: string, flagged: bool}|null
     */
    private function evaluateCopilotGuardrails(User $user, string $query, string $sanitized): ?array
    {
        if ($sanitized === '') {
            return [
                'reply' => 'Please ask a question about your subscriptions, spending trends, or optimization score.',
                'flagged' => false,
            ];
        }

        $violation = $this->guardrails->matchViolation($query, $sanitized, PromptGuardrailEvaluator::SURFACE_COPILOT);
        if ($violation !== null) {
            Log::warning("User AI Copilot {$violation['reason']} blocked", ['user_id' => $user->id, 'query' => $sanitized]);

            return [
                'reply' => $violation['reply'],
                'flagged' => true,
            ];
        }

        return null;
    }

    /**
     * @param  Collection<int, Subscription>  $subscriptions
     * @param  array<string, mixed>  $intelligence
     * @return array{reply: string, flagged: bool}
     */
    private function processCopilotQuery(
        User $user,
        string $sanitized,
        Collection $subscriptions,
        UserSetting $settings,
        array $intelligence
    ): array {
        $contextXml = $this->contextBuilder->buildUserContextXml($subscriptions, $settings, $intelligence);
        $systemPrompt = $this->contextBuilder->buildCopilotSystemPrompt($contextXml);
        $fencedQuery = $this->contextBuilder->wrapUntrustedUserInput($sanitized, 'user_query');

        try {
            $reply = $this->aiProvider->chat($systemPrompt, $fencedQuery);
            $inspected = $this->guardrails->inspectAndSanitizeOutput($reply, PromptGuardrailEvaluator::SURFACE_COPILOT);

            if ($inspected['flagged']) {
                Log::warning("User AI Copilot {$inspected['reason']} blocked", ['user_id' => $user->id, 'query' => $sanitized]);
            }

            return [
                'reply' => $inspected['reply'],
                'flagged' => $inspected['flagged'],
            ];
        } catch (Throwable $e) {
            Log::error('AI Copilot error: '.$e->getMessage(), ['user_id' => $user->id]);

            return [
                'reply' => 'Your financial copilot is temporarily unavailable. Your subscriptions and optimization score remain safe and fully visible on your dashboard.',
                'flagged' => false,
            ];
        }
    }
}
