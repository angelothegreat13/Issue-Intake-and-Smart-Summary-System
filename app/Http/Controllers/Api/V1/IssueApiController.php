<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Http\Resources\V1\IssueResource;
use App\Models\Issue;
use App\Services\IssueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IssueApiController extends Controller
{
    public function __construct(private readonly IssueService $issueService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['status', 'priority', 'category', 'escalated']);
        $issues  = $this->issueService->list($filters);

        return IssueResource::collection($issues);
    }

    public function store(StoreIssueRequest $request): JsonResponse
    {
        $issue = $this->issueService->create($request->validated());

        return (new IssueResource($issue))->response()->setStatusCode(201);
    }

    public function show(Issue $issue): IssueResource
    {
        return new IssueResource($issue);
    }

    public function update(UpdateIssueRequest $request, Issue $issue): IssueResource
    {
        $this->issueService->update($issue, $request->validated());

        return new IssueResource($issue->fresh());
    }
}
