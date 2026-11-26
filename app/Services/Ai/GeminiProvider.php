<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use App\Exceptions\AiChatException;
use App\Exceptions\ReceiptScanException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GeminiProvider implements AiProviderInterface
{
    private const JSON_MIME = 'application/json';

    private const DEFAULT_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    private const DEFAULT_PRIMARY_MODEL = 'gemini-flash-lite-latest';

    private const DEFAULT_FALLBACK_MODEL = 'gemini-flash-latest';

    /**
     * Execute a Gemini chat completion with structural role separation and model failover.
     */
    public function chat(string $systemPrompt, string $userMessage): string
    {
        $settings = $this->resolveSettings(12, 15);
        if ($settings['api_key'] === '') {
            throw new AiChatException('Gemini API key is not configured.');
        }

        $payload = [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemPrompt],
                ],
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userMessage],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.25,
                'maxOutputTokens' => 320,
            ],
        ];

        $result = $this->executeWithFailover($settings, $payload, 5);
        $response = $result['response'];

        if (! $response->successful()) {
            $message = (string) ($response->json('error.message') ?? 'Gemini service error: '.$response->status());
            throw new AiChatException($message, $response->status());
        }

        return (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
    }

    /**
     * Call Google Gemini multimodal generateContent endpoint with automatic timeout, 503, and 429 failover.
     *
     * @return array{model: string, data: array<string, mixed>}
     */
    public function extractReceipt(string $mime, string $base64, string $prompt): array
    {
        $settings = $this->resolveSettings(25, null);

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mime,
                                'data' => $base64,
                            ],
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'responseMimeType' => self::JSON_MIME,
            ],
        ];

        $result = $this->executeWithFailover($settings, $payload, 15);
        $response = $result['response'];

        if (! $response->successful()) {
            $message = (string) ($response->json('error.message') ?? 'Gemini AI service is temporarily unavailable.');
            throw new ReceiptScanException($message, $response->status());
        }

        $text = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');

        return [
            'model' => $result['model'],
            'data' => $this->decodeJsonText($text),
        ];
    }

    /**
     * @return array{api_key: string, base_url: string, primary_model: string, fallback_model: string, timeout: int}
     */
    private function resolveSettings(int $defaultTimeout, ?int $maxTimeout): array
    {
        $config = config('services.gemini');
        $rawTimeout = max(5, (int) ($config['timeout'] ?? $defaultTimeout));
        $timeout = $maxTimeout !== null ? min($maxTimeout, $rawTimeout) : $rawTimeout;

        return [
            'api_key' => (string) ($config['key'] ?? ''),
            'base_url' => rtrim((string) ($config['base_url'] ?? self::DEFAULT_BASE_URL), '/'),
            'primary_model' => (string) ($config['model'] ?? self::DEFAULT_PRIMARY_MODEL),
            'fallback_model' => (string) ($config['fallback_model'] ?? self::DEFAULT_FALLBACK_MODEL),
            'timeout' => $timeout,
        ];
    }

    /**
     * @param  array{api_key: string, base_url: string, primary_model: string, fallback_model: string, timeout: int}  $settings
     * @param  array<string, mixed>  $payload
     * @return array{model: string, response: Response}
     */
    private function executeWithFailover(array $settings, array $payload, int $connectTimeout): array
    {
        $usedModel = $settings['primary_model'];
        $fallbackModel = $settings['fallback_model'];
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
     * @param  array{api_key: string, base_url: string, primary_model: string, fallback_model: string, timeout: int}  $settings
     * @param  array<string, mixed>  $payload
     */
    private function sendRequest(array $settings, string $model, array $payload, int $connectTimeout): Response
    {
        return Http::withOptions(['force_ip_resolve' => 'v4'])
            ->withHeaders([
                'Content-Type' => self::JSON_MIME,
                'X-goog-api-key' => $settings['api_key'],
            ])
            ->connectTimeout($connectTimeout)
            ->timeout($settings['timeout'])
            ->post("{$settings['base_url']}/models/{$model}:generateContent", $payload);
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
