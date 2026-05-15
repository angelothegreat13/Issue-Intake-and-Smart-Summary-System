<?php

namespace App\Services;

use App\Contracts\SummaryServiceInterface;
use Illuminate\Support\Facades\Http;

class AnthropicSummaryService implements SummaryServiceInterface
{
    public function generateSummary(
        string $title,
        string $description,
        string $priority,
        string $category
    ): array {
        $prompt = <<<EOT
You are an IT issue triage assistant. Analyze the issue below and respond ONLY with valid JSON in this exact format (no markdown, no explanation):
{"summary": "one sentence summary", "suggested_action": "one sentence recommended action"}

Title: {$title}
Priority: {$priority}
Category: {$category}
Description: {$description}
EOT;

        $response = Http::withHeaders([
            'x-api-key'         => config('anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(15)->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-3-haiku-20240307',
            'max_tokens' => 256,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Anthropic API returned HTTP ' . $response->status());
        }

        $parsed = json_decode($response->json('content.0.text'), true);

        if (! isset($parsed['summary'], $parsed['suggested_action'])) {
            throw new \RuntimeException('Unexpected Anthropic API response format');
        }

        return [
            'summary'          => $parsed['summary'],
            'suggested_action' => $parsed['suggested_action'],
        ];
    }
}
