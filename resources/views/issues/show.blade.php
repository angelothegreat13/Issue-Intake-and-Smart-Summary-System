@extends('issues.layout')

@section('title', $issue->title)

@php
    use App\Enums\Status;

    $priorityColors = [
        'critical' => 'danger',
        'high'     => 'warning',
        'medium'   => 'info',
        'low'      => 'secondary',
    ];
    $statusColors = [
        'open'        => 'primary',
        'in_progress' => 'warning',
        'resolved'    => 'success',
        'closed'      => 'secondary',
    ];
    $priorityColor = $priorityColors[$issue->priority->value] ?? 'secondary';
    $statusColor   = $statusColors[$issue->status->value] ?? 'secondary';
@endphp

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">

        {{-- Header --}}
        <div class="d-flex align-items-start gap-2 mb-3 flex-wrap">
            <a href="{{ route('issues.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Back</a>
            <div class="flex-fill">
                <h4 class="mb-1">{{ $issue->title }}</h4>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge bg-{{ $priorityColor }} {{ $issue->priority->value === 'high' ? 'text-dark' : '' }}">
                        {{ $issue->priority->label() }} priority
                    </span>
                    <span class="badge bg-{{ $statusColor }} {{ $issue->status->value === 'in_progress' ? 'text-dark' : '' }}">
                        {{ $issue->status->label() }}
                    </span>
                    <span class="badge bg-light text-dark border">{{ $issue->category->label() }}</span>
                    @if($issue->escalated)
                        <span class="badge bg-danger">Escalated</span>
                    @endif
                    @if($issue->due_at)
                        <span class="small {{ $issue->due_at->isPast() && $issue->status !== Status::Resolved ? 'text-danger fw-semibold' : 'text-muted' }}">
                            Due: {{ $issue->due_at->format('M d, Y H:i') }}
                            @if($issue->due_at->isPast() && $issue->status !== Status::Resolved)
                                (overdue)
                            @endif
                        </span>
                    @endif
                </div>
            </div>
            <a href="{{ route('issues.edit', $issue) }}" class="btn btn-sm btn-outline-primary">Edit</a>
        </div>

        {{-- Description --}}
        <div class="card mb-3">
            <div class="card-header fw-semibold bg-white">Description</div>
            <div class="card-body">
                <pre class="mb-0" style="font-family: inherit; font-size: .925rem;">{{ $issue->description }}</pre>
            </div>
        </div>

        {{-- AI Summary --}}
        @if($issue->summary)
            <div class="card mb-3 border-primary border-opacity-25">
                <div class="card-header fw-semibold bg-white text-primary">
                    Summary
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $issue->summary }}</p>
                </div>
            </div>
        @endif

        {{-- Suggested Action --}}
        @if($issue->suggested_action)
            <div class="card mb-3 border-success border-opacity-25">
                <div class="card-header fw-semibold bg-white text-success">
                    Suggested Action
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $issue->suggested_action }}</p>
                </div>
            </div>
        @endif

        {{-- Meta --}}
        <div class="text-muted small mt-1">
            Issue #{{ $issue->id }} &middot;
            Created {{ $issue->created_at->format('M d, Y \a\t H:i') }} &middot;
            Updated {{ $issue->updated_at->diffForHumans() }}
        </div>

    </div>
</div>
@endsection
