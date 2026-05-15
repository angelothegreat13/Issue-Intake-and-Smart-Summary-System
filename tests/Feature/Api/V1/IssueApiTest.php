<?php

use App\Contracts\SummaryServiceInterface;
use App\Models\Issue;

beforeEach(function () {
    $this->app->bind(SummaryServiceInterface::class, function () {
        return new class implements SummaryServiceInterface {
            public function generateSummary(string $title, string $description, string $priority, string $category): array
            {
                return ['summary' => 'Auto summary', 'suggested_action' => 'Take action'];
            }
        };
    });
});

it('creates an issue and returns 201', function () {
    $this->postJson('/api/v1/issues', [
        'title'       => 'Login page is broken on mobile',
        'description' => 'Users on iOS cannot log in after the latest update was deployed',
        'priority'    => 'high',
        'category'    => 'bug',
        'status'      => 'open',
    ])
        ->assertStatus(201)
        ->assertJsonPath('data.title', 'Login page is broken on mobile')
        ->assertJsonPath('data.priority', 'high')
        ->assertJsonPath('data.summary', 'Auto summary');
});

it('rejects invalid input with 422', function () {
    $this->postJson('/api/v1/issues', [
        'title'    => 'Hi',
        'priority' => 'urgent',
        'category' => 'unknown',
    ])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors']);
});

it('returns a paginated list of issues', function () {
    Issue::factory()->count(3)->create();

    $this->getJson('/api/v1/issues')
        ->assertStatus(200)
        ->assertJsonStructure(['data', 'meta', 'links']);
});

it('returns a single issue', function () {
    $issue = Issue::factory()->create();

    $this->getJson("/api/v1/issues/{$issue->id}")
        ->assertStatus(200)
        ->assertJsonPath('data.id', $issue->id);
});

it('returns 404 for a missing issue', function () {
    $this->getJson('/api/v1/issues/999')
        ->assertStatus(404);
});

it('updates an issue and returns the updated resource', function () {
    $issue = Issue::factory()->create(['status' => 'open']);

    $this->patchJson("/api/v1/issues/{$issue->id}", ['status' => 'resolved'])
        ->assertStatus(200)
        ->assertJsonPath('data.status', 'resolved');
});

it('filters issues by status', function () {
    Issue::factory()->create(['status' => 'open']);
    Issue::factory()->create(['status' => 'resolved']);

    $this->getJson('/api/v1/issues?status=open')
        ->assertStatus(200)
        ->assertJsonCount(1, 'data');
});
