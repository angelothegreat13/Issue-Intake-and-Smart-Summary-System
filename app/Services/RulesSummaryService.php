<?php

namespace App\Services;

use App\Contracts\SummaryServiceInterface;

class RulesSummaryService implements SummaryServiceInterface
{
    public function generateSummary(
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

        $action = match(true) {
            $lowerCategory === 'security'  => 'Escalate to the security team immediately and restrict access to affected systems.',
            $priority === 'critical'       => 'Page the on-call engineer immediately and initiate incident response protocol.',
            $priority === 'high'           => 'Assign to a senior engineer within the hour and provide a status update.',
            $lowerCategory === 'bug'       => 'Reproduce the defect in staging and assign to the relevant dev team.',
            $lowerCategory === 'feature'   => 'Add to the product backlog and schedule for upcoming sprint review.',
            $lowerCategory === 'infrastructure' => 'Escalate to the infrastructure team and assess system impact.',
            $lowerCategory === 'performance'    => 'Profile the affected service and identify the bottleneck.',
            $lowerCategory === 'data'      => 'Engage the data engineering team to investigate data integrity.',
            default                        => 'Review and assign to the appropriate team for resolution.',
        };

        return ['summary' => $summary, 'suggested_action' => $action];
    }
}
