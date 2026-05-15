<?php

use App\Services\RulesSummaryService;

beforeEach(function () {
    $this->service = new RulesSummaryService();
});

it('returns summary and suggested_action keys', function () {
    $result = $this->service->generateSummary('Test', 'Description', 'low', 'bug');

    expect($result)->toHaveKeys(['summary', 'suggested_action']);
});

it('includes priority and title in the summary', function () {
    $result = $this->service->generateSummary('Login broken', 'Details here', 'high', 'bug');

    expect($result['summary'])->toContain('Login broken')
        ->and($result['summary'])->toContain('High');
});

it('escalates security issues to the security team', function () {
    $result = $this->service->generateSummary('XSS found', 'Details', 'low', 'security');

    expect($result['suggested_action'])->toContain('security team');
});

it('pages on-call engineer for critical priority', function () {
    $result = $this->service->generateSummary('DB down', 'Details', 'critical', 'infrastructure');

    expect($result['suggested_action'])->toContain('on-call');
});

it('assigns high priority bugs to a senior engineer', function () {
    $result = $this->service->generateSummary('Crash', 'Details', 'high', 'bug');

    expect($result['suggested_action'])->toContain('senior engineer');
});
