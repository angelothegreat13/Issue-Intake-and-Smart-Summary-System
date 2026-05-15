<?php

namespace App\Services;

use App\Models\Issue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class IssueService
{
    public function __construct(private readonly SummaryService $summaryService) {}

    public function create(array $data): Issue
    {
        $summary = $this->summaryService->generateSummary(
            $data['title'],
            $data['description'],
            $data['priority'],
            $data['category'],
        );

        $issue = new Issue(array_merge($data, $summary));
        $this->applyEscalation($issue);
        $issue->save();

        return $issue;
    }

    public function update(Issue $issue, array $data): Issue
    {
        $descriptionChanged = isset($data['description']) && $data['description'] !== $issue->description;

        $issue->fill($data);

        if ($descriptionChanged) {
            $summary = $this->summaryService->generateSummary(
                $issue->title,
                $issue->description,
                $issue->priority,
                $issue->category,
            );
            $issue->fill($summary);
        }

        $this->applyEscalation($issue);
        $issue->save();

        return $issue;
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Issue::query()->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['category'])) {
            $query->where('category', 'like', '%' . $filters['category'] . '%');
        }

        if (isset($filters['escalated']) && $filters['escalated'] !== '') {
            $query->where('escalated', (bool) $filters['escalated']);
        }

        return $query->paginate($perPage);
    }

    private function applyEscalation(Issue $issue): void
    {
        $issue->escalated =
            $issue->priority === 'critical'
            || ($issue->priority === 'high' && $issue->status === 'open')
            || ($issue->due_at !== null && $issue->due_at->isPast() && $issue->status !== 'resolved');
    }
}
