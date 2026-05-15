@extends('issues.layout')

@section('title', 'Issues')

@php
    $priorityClass = fn($p) => match($p) {
        'critical' => 'badge-priority-critical',
        'high'     => 'badge-priority-high',
        'medium'   => 'badge-priority-medium',
        default    => 'badge-priority-low',
    };
    $statusClass = fn($s) => 'badge-status-' . $s;
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">All Issues <span class="text-muted fs-6">({{ $issues->total() }})</span></h4>
    <a href="{{ route('issues.create') }}" class="btn btn-primary btn-sm">+ New Issue</a>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('issues.index') }}" class="row g-2 align-items-end">
            <div class="col-sm-3">
                <label class="form-label mb-1 small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach(['open','in_progress','resolved','closed'] as $s)
                        <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>
                            {{ ucfirst(str_replace('_', ' ', $s)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <label class="form-label mb-1 small fw-semibold">Priority</label>
                <select name="priority" class="form-select form-select-sm">
                    <option value="">All priorities</option>
                    @foreach(['low','medium','high','critical'] as $p)
                        <option value="{{ $p }}" @selected(($filters['priority'] ?? '') === $p)>
                            {{ ucfirst($p) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label mb-1 small fw-semibold">Category</label>
                <input type="text" name="category" class="form-control form-control-sm"
                       placeholder="Search category…" value="{{ $filters['category'] ?? '' }}">
            </div>
            <div class="col-sm-2">
                <label class="form-label mb-1 small fw-semibold">Escalated</label>
                <select name="escalated" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="1" @selected(($filters['escalated'] ?? '') === '1')>Escalated</option>
                    <option value="0" @selected(($filters['escalated'] ?? '') === '0')>Not escalated</option>
                </select>
            </div>
            <div class="col-sm-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-dark flex-fill">Filter</button>
                <a href="{{ route('issues.index') }}" class="btn btn-sm btn-outline-secondary flex-fill">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-muted" style="width:50px">#</th>
                    <th>Title</th>
                    <th>Priority</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Escalated</th>
                    <th>Due</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($issues as $issue)
                    <tr>
                        <td class="text-muted small">{{ $issue->id }}</td>
                        <td>
                            <a href="{{ route('issues.show', $issue) }}" class="text-decoration-none fw-semibold text-dark">
                                {{ $issue->title }}
                            </a>
                        </td>
                        <td>
                            <span class="badge {{ $priorityClass($issue->priority) }}">
                                {{ ucfirst($issue->priority) }}
                            </span>
                        </td>
                        <td class="small">{{ $issue->category }}</td>
                        <td>
                            <span class="badge {{ $statusClass($issue->status) }}">
                                {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                            </span>
                        </td>
                        <td>
                            @if($issue->escalated)
                                <span class="badge bg-danger">Yes</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="small">
                            @if($issue->due_at)
                                <span class="{{ $issue->due_at->isPast() && $issue->status !== 'resolved' ? 'text-danger fw-semibold' : '' }}">
                                    {{ $issue->due_at->format('M d, Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $issue->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No issues found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($issues->hasPages())
    <div class="mt-3">
        {{ $issues->withQueryString()->links() }}
    </div>
@endif
@endsection
