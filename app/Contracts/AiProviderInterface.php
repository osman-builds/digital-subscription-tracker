<?php

declare(strict_types=1);

namespace App\Contracts;

interface AiProviderInterface
{
    /**
     * Execute a text chat completion with separated system instructions and user message.
     */
    public function chat(string $systemPrompt, string $userMessage): string;

    /**
     * Extract structured subscription JSON from a base64-encoded image or PDF receipt.
     *
     * @return array{model: string, data: array<string, mixed>}
     */
    public function extractReceipt(string $mime, string $base64, string $prompt): array;
}
