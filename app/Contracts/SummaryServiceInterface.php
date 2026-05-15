<?php

namespace App\Contracts;

interface SummaryServiceInterface
{
    public function generateSummary(
        string $title,
        string $description,
        string $priority,
        string $category
    ): array;
}
