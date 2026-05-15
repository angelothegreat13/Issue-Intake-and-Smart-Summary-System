<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|min:5|max:200',
            'description' => 'required|string|min:10|max:5000',
            'priority'    => 'required|in:low,medium,high,critical',
            'category'    => 'required|string|max:100',
            'status'      => 'sometimes|in:open,in_progress,resolved,closed',
            'due_at'      => 'nullable|date',
        ];
    }
}
