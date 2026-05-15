<?php

namespace App\Http\Requests;

use App\Enums\Category;
use App\Enums\Priority;
use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'required', 'string', 'min:5', 'max:200'],
            'description' => ['sometimes', 'required', 'string', 'min:10', 'max:5000'],
            'priority'    => ['sometimes', 'required', Rule::enum(Priority::class)],
            'category'    => ['sometimes', 'required', Rule::enum(Category::class)],
            'status'      => ['sometimes', Rule::enum(Status::class)],
            'due_at'      => ['nullable', 'date'],
        ];
    }
}
