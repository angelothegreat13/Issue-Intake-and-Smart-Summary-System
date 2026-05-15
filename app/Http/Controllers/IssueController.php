<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Models\Issue;
use App\Services\IssueService;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    public function __construct(private readonly IssueService $issueService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'priority', 'category', 'escalated']);
        $issues  = $this->issueService->list($filters);

        return view('issues.index', compact('issues', 'filters'));
    }

    public function create()
    {
        return view('issues.create');
    }

    public function store(StoreIssueRequest $request)
    {
        $issue = $this->issueService->create($request->validated());

        return redirect()->route('issues.show', $issue)->with('success', 'Issue created successfully.');
    }

    public function show(Issue $issue)
    {
        return view('issues.show', compact('issue'));
    }

    public function edit(Issue $issue)
    {
        return view('issues.edit', compact('issue'));
    }

    public function update(UpdateIssueRequest $request, Issue $issue)
    {
        $this->issueService->update($issue, $request->validated());

        return redirect()->route('issues.show', $issue)->with('success', 'Issue updated successfully.');
    }
}
