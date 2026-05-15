@extends('issues.layout')

@section('title', 'Edit Issue #' . $issue->id)

@php
    use App\Enums\Priority;
    use App\Enums\Status;
    use App\Enums\Category;
@endphp

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center mb-3 gap-2">
            <a href="{{ route('issues.show', $issue) }}" class="btn btn-sm btn-outline-secondary">&larr; Back</a>
            <h4 class="mb-0">Edit Issue <span class="text-muted">#{{ $issue->id }}</span></h4>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('issues.update', $issue) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $issue->title) }}">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Description <span class="text-danger">*</span>
                            <span class="text-muted fw-normal small">(changing this regenerates the summary)</span>
                        </label>
                        <textarea name="description" rows="5"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $issue->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror">
                                @foreach(Priority::cases() as $p)
                                    <option value="{{ $p->value }}" @selected(old('priority', $issue->priority->value) === $p->value)>
                                        {{ $p->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror">
                                @foreach(Category::cases() as $c)
                                    <option value="{{ $c->value }}" @selected(old('category', $issue->category->value) === $c->value)>
                                        {{ $c->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                @foreach(Status::cases() as $s)
                                    <option value="{{ $s->value }}" @selected(old('status', $issue->status->value) === $s->value)>
                                        {{ $s->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Due Date <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="datetime-local" name="due_at"
                               class="form-control @error('due_at') is-invalid @enderror"
                               value="{{ old('due_at', $issue->due_at?->format('Y-m-d\TH:i')) }}">
                        @error('due_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="{{ route('issues.show', $issue) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
