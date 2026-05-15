<?php

use App\Services\AnthropicSummaryService;
use Illuminate\Support\Facades\Http;

it('returns summary and suggested_action from the Anthropic API', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [
                ['text' => '{"summary": "Login bug on iOS Safari.", "suggested_action": "Assign to mobile team immediately."}'],
            ],
        ], 200),
    ]);

    $service = new AnthropicSummaryService();
    $result  = $service->generateSummary('Login broken', 'Details here', 'high', 'bug');

    expect($result['summary'])->toBe('Login bug on iOS Safari.')
        ->and($result['suggested_action'])->toBe('Assign to mobile team immediately.');
});

it('throws a RuntimeException when the API returns a non-200 status', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([], 500),
    ]);

    $service = new AnthropicSummaryService();
    $service->generateSummary('Title', 'Description', 'low', 'bug');
})->throws(RuntimeException::class, 'Anthropic API returned HTTP 500');

it('throws a RuntimeException when the response JSON is missing expected keys', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [
                ['text' => '{"unexpected_key": "value"}'],
            ],
        ], 200),
    ]);

    $service = new AnthropicSummaryService();
    $service->generateSummary('Title', 'Description', 'low', 'bug');
})->throws(RuntimeException::class, 'Unexpected Anthropic API response format');
