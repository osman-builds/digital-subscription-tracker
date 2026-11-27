<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use App\Exceptions\AiChatException;
use App\Exceptions\ReceiptScanException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GroqProvider implements AiProviderInterface
{
    private const JSON_MIME = 'application/json';

    private const PDF_MIME = 'application/pdf';

    private const DEFAULT_BASE_URL = 'https://api.groq.com/openai/v1';

    private const DEFAULT_CHAT_MODEL = 'openai/gpt-oss-20b';

    private const DEFAULT_CHAT_FALLBACK = 'openai/gpt-oss-120b';

    private const DEFAULT_VISION_MODEL = 'qwen/qwen3.8-27b';

    /**
     * Execute a Groq chat completion with structural role separation and automatic model failover.
     */
    public function chat(string $systemPrompt, string $userMessage): string
    {
        $settings = $this->resolveSettings(12, 15);
        if ($settings['api_key'] === '') {
            throw new AiChatException('Groq API key is not configured.');
        }

        $payload = [
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => 0.25,
            'max_completion_tokens' => 400,
        ];

        $result = $this->executeWithFailover(
            $settings,
            $settings['chat_model'],
            $settings['chat_fallback_model'],
            $payload,
            5
        );
        $response = $result['response'];

        if (! $response->successful()) {
            $message = (string) ($response->json('error.message') ?? 'Groq service error: '.$response->status());
            throw new AiChatException($message, $response->status());
        }

        return (string) data_get($response->json(), 'choices.0.message.content', '');
    }

    /**
     * Extract structured subscription JSON from an image via Groq Vision (Qwen 3.8 27B),
     * or from a raw PDF stream via in-memory text extraction + Groq structured JSON chat.
     *
     * @return array{model: string, data: array<string, mixed>}
     */
    public function extractReceipt(string $mime, string $base64, string $prompt): array
    {
        $settings = $this->resolveSettings(25, null);
        if ($settings['api_key'] === '') {
            throw new ReceiptScanException('Groq API key is not configured.');
        }

        if ($mime === self::PDF_MIME) {
            return $this->extractFromPdfText($settings, $base64, $prompt);
        }

        $payload = [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $prompt],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mime};base64,{$base64}",
                            ],
                        ],
                    ],
                ],
            ],
            'temperature' => 0.1,
            'response_format' => ['type' => 'json_object'],
        ];

        $result = $this->executeWithFailover(
            $settings,
            $settings['vision_model'],
            $settings['vision_fallback_model'],
            $payload,
            15
        );
        $response = $result['response'];

        if (! $response->successful()) {
            $message = (string) ($response->json('error.message') ?? 'Groq AI service is temporarily unavailable.');
            throw new ReceiptScanException($message, $response->status());
        }

        $text = (string) data_get($response->json(), 'choices.0.message.content', '');

        return [
            'model' => $result['model'],
            'data' => $this->decodeJsonText($text),
        ];
    }

    /**
     * Fallback path when a raw PDF is uploaded directly without client-side PNG rasterization.
     *
     * @param  array{api_key: string, base_url: string, chat_model: string, chat_fallback_model: string, vision_model: string, vision_fallback_model: string, timeout: int}  $settings
     * @return array{model: string, data: array<string, mixed>}
     */
    private function extractFromPdfText(array $settings, string $base64, string $prompt): array
    {
        $binary = base64_decode($base64, true);
        $extractedText = is_string($binary) ? $this->extractPrintablePdfText($binary) : '';
        $escapedPdfText = htmlspecialchars(
            $extractedText !== '' ? $extractedText : '(empty pdf text)',
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        $payload = [
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => "<untrusted_pdf_document>\n{$escapedPdfText}\n</untrusted_pdf_document>"],
            ],
            'temperature' => 0.1,
            'response_format' => ['type' => 'json_object'],
        ];

        $result = $this->executeWithFailover(
            $settings,
            $settings['chat_model'],
            $settings['chat_fallback_model'],
            $payload,
            15
        );
        $response = $result['response'];

        if (! $response->successful()) {
            $message = (string) ($response->json('error.message') ?? 'Groq AI service is temporarily unavailable.');
            throw new ReceiptScanException($message, $response->status());
        }

        $text = (string) data_get($response->json(), 'choices.0.message.content', '');

        return [
            'model' => $result['model'],
            'data' => $this->decodeJsonText($text),
        ];
    }

    private function extractPrintablePdfText(string $binary): string
    {
        $chunks = [];
        if (preg_match_all('/\(([^()]{2,200})\)/', $binary, $matches)) {
            foreach ($matches[1] as $match) {
                $clean = trim((string) preg_replace('/[^\x20-\x7E]+/', ' ', $match));
                if ($clean !== '') {
                    $chunks[] = $clean;
                }
            }
        }

        return mb_substr(implode(' ', $chunks), 0, 6000);
    }

    /**
     * @return array{api_key: string, base_url: string, chat_model: string, chat_fallback_model: string, vision_model: string, vision_fallback_model: string, timeout: int}
     */
    private function resolveSettings(int $defaultTimeout, ?int $maxTimeout): array
    {
        $config = config('services.groq');
        $rawTimeout = max(5, (int) ($config['timeout'] ?? $defaultTimeout));
        $timeout = $maxTimeout !== null ? min($maxTimeout, $rawTimeout) : $rawTimeout;

        return [
            'api_key' => (string) ($config['key'] ?? ''),
            'base_url' => rtrim((string) ($config['base_url'] ?? self::DEFAULT_BASE_URL), '/'),
            'chat_model' => (string) ($config['chat_model'] ?? self::DEFAULT_CHAT_MODEL),
            'chat_fallback_model' => (string) ($config['chat_fallback_model'] ?? self::DEFAULT_CHAT_FALLBACK),
            'vision_model' => (string) ($config['vision_model'] ?? self::DEFAULT_VISION_MODEL),
            'vision_fallback_model' => (string) ($config['vision_fallback_model'] ?? ''),
            'timeout' => $timeout,
        ];
    }

    /**
     * @param  array{api_key: string, base_url: string, chat_model: string, chat_fallback_model: string, vision_model: string, vision_fallback_model: string, timeout: int}  $settings
     * @param  array<string, mixed>  $payload
     * @return array{model: string, response: Response}
     */
    private function executeWithFailover(
        array $settings,
        string $primaryModel,
        string $fallbackModel,
        array $payload,
        int $connectTimeout
    ): array {
        $usedModel = $primaryModel;
        $canFallback = $fallbackModel !== '' && $fallbackModel !== $usedModel;
        $shouldFallback = false;
        $response = null;

        try {
            $response = $this->sendRequest($settings, $usedModel, $payload, $connectTimeout);
            $shouldFallback = in_array($response->status(), [429, 503], true) && $canFallback;
        } catch (ConnectionException $e) {
            if (! $canFallback) {
                throw $e;
            }
            $shouldFallback = true;
        }

        if ($shouldFallback) {
            $usedModel = $fallbackModel;
            $response = $this->sendRequest($settings, $usedModel, $payload, $connectTimeout);
        }

        return [
            'model' => $usedModel,
            'response' => $response,
        ];
    }

    /**
     * @param  array{api_key: string, base_url: string, chat_model: string, chat_fallback_model: string, vision_model: string, vision_fallback_model: string, timeout: int}  $settings
     * @param  array<string, mixed>  $payload
     */
    private function sendRequest(array $settings, string $model, array $payload, int $connectTimeout): Response
    {
        $payload['model'] = $model;

        return Http::withOptions(['force_ip_resolve' => 'v4'])
            ->withHeaders([
                'Content-Type' => self::JSON_MIME,
                'Authorization' => 'Bearer '.$settings['api_key'],
            ])
            ->connectTimeout($connectTimeout)
            ->timeout($settings['timeout'])
            ->post("{$settings['base_url']}/chat/completions", $payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonText(string $rawText): array
    {
        $cleaned = trim($rawText);
        if (str_starts_with($cleaned, '```')) {
            $cleaned = (string) preg_replace('/^```(?:json)?\s*/i', '', $cleaned);
            $cleaned = (string) preg_replace('/\s*```$/', '', $cleaned);
        }

        $decoded = json_decode($cleaned, true);
        if (! is_array($decoded)) {
            throw new ReceiptScanException('Unable to parse structured subscription data from the AI response.');
        }

        return $decoded;
    }
}
