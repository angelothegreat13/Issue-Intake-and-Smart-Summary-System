<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SummaryService
{
    public function generateSummary(string $title, string $description, string $priority, string $category): array
    {
        $apiKey = config('anthropic.api_key');

        if ($apiKey) {
            try {
                return $this->callAnthropicApi($title, $description, $priority, $category, $apiKey);
            } catch (\Exception $e) {
                Log::warning('Anthropic API failed, using rules-based fallback: ' . $e->getMessage());
            }
        }

        return $this->rulesFallback($title, $description, $priority, $category);
    }

    private function callAnthropicApi(
        string $title,
        string $description,
        string $priority,
        string $category,
        string $apiKey
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
            'x-api-key'         => $apiKey,
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

        $text   = $response->json('content.0.text');
        $parsed = json_decode($text, true);

        if (! isset($parsed['summary'], $parsed['suggested_action'])) {
            throw new \RuntimeException('Unexpected API response format');
        }

        return [
            'summary'          => $parsed['summary'],
            'suggested_action' => $parsed['suggested_action'],
        ];
    }

    private function rulesFallback(
        string $title,
        string $description,
        string $priority,
        string $category
    ): array {
        $lowerCategory = strtolower($category);

        $categoryLabels = [
            'bug'            => 'software defect',
            'feature'        => 'feature request',
            'infrastructure' => 'infrastructure issue',
            'performance'    => 'performance problem',
            'data'           => 'data issue',
            'security'       => 'security vulnerability',
        ];

        $label   = $categoryLabels[$lowerCategory] ?? "{$category} issue";
        $summary = ucfirst($priority) . " {$label}: {$title}";

        if ($lowerCategory === 'security') {
            $action = 'Escalate to the security team immediately and restrict access to affected systems.';
        } elseif ($priority === 'critical') {
            $action = 'Page the on-call engineer immediately and initiate incident response protocol.';
        } elseif ($priority === 'high') {
            $action = 'Assign to a senior engineer within the hour and provide a status update.';
        } elseif ($lowerCategory === 'bug') {
            $action = 'Reproduce the defect in staging and assign to the relevant dev team.';
        } elseif ($lowerCategory === 'feature') {
            $action = 'Add to the product backlog and schedule for upcoming sprint review.';
        } elseif ($lowerCategory === 'infrastructure') {
            $action = 'Escalate to the infrastructure team and assess system impact.';
        } elseif ($lowerCategory === 'performance') {
            $action = 'Profile the affected service and identify the bottleneck.';
        } elseif ($lowerCategory === 'data') {
            $action = 'Engage the data engineering team to investigate data integrity.';
        } else {
            $action = 'Review and assign to the appropriate team for resolution.';
        }

        return ['summary' => $summary, 'suggested_action' => $action];
    }
}
