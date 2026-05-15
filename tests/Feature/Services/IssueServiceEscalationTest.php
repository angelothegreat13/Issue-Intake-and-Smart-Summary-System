<?php

use App\Contracts\SummaryServiceInterface;
use App\Enums\Priority;
use App\Enums\Status;
use App\Services\IssueService;

beforeEach(function () {
    $fake = new class implements SummaryServiceInterface {
        public function generateSummary(string $title, string $description, string $priority, string $category): array
        {
            return ['summary' => 'Test summary', 'suggested_action' => 'Test action'];
        }
    };

    $this->service = new IssueService($fake);
});

it('escalates critical priority issues', function () {
    $issue = $this->service->create([
        'title'       => 'Critical issue',
        'description' => 'Something very bad happened here right now',
        'priority'    => Priority::Critical->value,
        'category'    => 'bug',
        'status'      => Status::Open->value,
    ]);

    expect($issue->escalated)->toBeTrue();
});

it('escalates high priority open issues', function () {
    $issue = $this->service->create([
        'title'       => 'High priority issue',
        'description' => 'Something bad happened that needs attention soon',
        'priority'    => Priority::High->value,
        'category'    => 'bug',
        'status'      => Status::Open->value,
    ]);

    expect($issue->escalated)->toBeTrue();
});

it('does not escalate high priority resolved issues', function () {
    $issue = $this->service->create([
        'title'       => 'High priority resolved',
        'description' => 'Something bad that was already fixed and closed out',
        'priority'    => Priority::High->value,
        'category'    => 'bug',
        'status'      => Status::Resolved->value,
    ]);

    expect($issue->escalated)->toBeFalse();
});

it('escalates overdue unresolved issues', function () {
    $issue = $this->service->create([
        'title'       => 'Overdue issue',
        'description' => 'This issue is past its due date and still not resolved',
        'priority'    => Priority::Low->value,
        'category'    => 'feature',
        'status'      => Status::Open->value,
        'due_at'      => now()->subDay()->toDateTimeString(),
    ]);

    expect($issue->escalated)->toBeTrue();
});

it('does not escalate low priority non-overdue issues', function () {
    $issue = $this->service->create([
        'title'       => 'Low priority issue',
        'description' => 'Minor cosmetic fix that can wait for next sprint cycle',
        'priority'    => Priority::Low->value,
        'category'    => 'feature',
        'status'      => Status::Open->value,
    ]);

    expect($issue->escalated)->toBeFalse();
});
