<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Models\Issue;
use App\Services\IssueService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    public function __construct(private readonly IssueService $issueService) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'priority', 'category', 'escalated']);
        $issues  = $this->issueService->list($filters);

        return view('issues.index', compact('issues', 'filters'));
    }

    public function create(): View
    {
        return view('issues.create');
    }

    public function store(StoreIssueRequest $request): RedirectResponse
    {
        $issue = $this->issueService->create($request->validated());

        return redirect()->route('issues.show', $issue)->with('success', 'Issue created successfully.');
    }

    public function show(Issue $issue): View
    {
        return view('issues.show', compact('issue'));
    }

    public function edit(Issue $issue): View
    {
        return view('issues.edit', compact('issue'));
    }

    public function update(UpdateIssueRequest $request, Issue $issue): RedirectResponse
    {
        $this->issueService->update($issue, $request->validated());

        return redirect()->route('issues.show', $issue)->with('success', 'Issue updated successfully.');
    }
}
