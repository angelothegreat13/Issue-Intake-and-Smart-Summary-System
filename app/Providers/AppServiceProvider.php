<?php

namespace App\Providers;

use App\Contracts\SummaryServiceInterface;
use App\Services\AnthropicSummaryService;
use App\Services\RulesSummaryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SummaryServiceInterface::class, function () {
            if (config('anthropic.api_key')) {
                return new AnthropicSummaryService();
            }

            Log::warning('ANTHROPIC_API_KEY not set — using rules-based summary fallback.');

            return new RulesSummaryService();
        });
    }

    public function boot(): void {}
}
