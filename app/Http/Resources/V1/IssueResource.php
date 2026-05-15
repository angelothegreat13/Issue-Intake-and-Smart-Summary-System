<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IssueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'description'      => $this->description,
            'priority'         => $this->priority,
            'category'         => $this->category,
            'status'           => $this->status,
            'escalated'        => $this->escalated,
            'summary'          => $this->summary,
            'suggested_action' => $this->suggested_action,
            'due_at'           => $this->due_at?->toIso8601String(),
            'created_at'       => $this->created_at->toIso8601String(),
            'updated_at'       => $this->updated_at->toIso8601String(),
        ];
    }
}
