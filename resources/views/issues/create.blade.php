@extends('issues.layout')

@section('title', 'New Issue')

@php
    use App\Enums\Priority;
    use App\Enums\Status;
    use App\Enums\Category;
@endphp

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center mb-3 gap-2">
            <a href="{{ route('issues.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Back</a>
            <h4 class="mb-0">New Issue</h4>
        </div>

        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('issues.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" placeholder="Brief, descriptive title (5–200 chars)" autofocus>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                        <textarea name="description" rows="5"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Detailed description of the issue (10–5000 chars)">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select @error('priority') is-invalid @enderror">
                                @foreach(Priority::cases() as $p)
                                    <option value="{{ $p->value }}" @selected(old('priority', 'medium') === $p->value)>
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
                                <option value="">Select category…</option>
                                @foreach(Category::cases() as $c)
                                    <option value="{{ $c->value }}" @selected(old('category') === $c->value)>
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
                                    <option value="{{ $s->value }}" @selected(old('status', 'open') === $s->value)>
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
                               value="{{ old('due_at') }}">
                        @error('due_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Create Issue</button>
                        <a href="{{ route('issues.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-muted small mt-2">
            A summary and suggested action will be auto-generated after submission.
        </p>
    </div>
</div>
@endsection
