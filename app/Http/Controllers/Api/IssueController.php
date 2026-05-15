<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Models\Issue;
use App\Services\IssueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    public function __construct(private readonly IssueService $issueService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'priority', 'category', 'escalated']);
        $issues  = $this->issueService->list($filters);

        return response()->json($issues);
    }

    public function store(StoreIssueRequest $request): JsonResponse
    {
        $issue = $this->issueService->create($request->validated());

        return response()->json($issue, 201);
    }

    public function show(Issue $issue): JsonResponse
    {
        return response()->json($issue);
    }

    public function update(UpdateIssueRequest $request, Issue $issue): JsonResponse
    {
        $this->issueService->update($issue, $request->validated());

        return response()->json($issue->fresh());
    }
}
