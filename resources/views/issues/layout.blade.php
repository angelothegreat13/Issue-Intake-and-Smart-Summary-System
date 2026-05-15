<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Issue Tracker')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f3f5; }
        .navbar-brand { font-weight: 700; letter-spacing: .5px; }
        .badge-priority-critical { background-color: #dc3545; }
        .badge-priority-high     { background-color: #e67e22; }
        .badge-priority-medium   { background-color: #3498db; }
        .badge-priority-low      { background-color: #6c757d; }
        .badge-status-open        { background-color: #0d6efd; }
        .badge-status-in_progress { background-color: #ffc107; color: #212529 !important; }
        .badge-status-resolved    { background-color: #198754; }
        .badge-status-closed      { background-color: #6c757d; }
        .card { box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        pre { white-space: pre-wrap; word-break: break-word; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-lg">
        <a class="navbar-brand" href="{{ route('issues.index') }}">Issue Tracker</a>
        <a class="btn btn-sm btn-outline-light" href="{{ route('issues.create') }}">+ New Issue</a>
    </div>
</nav>

<div class="container-lg pb-5">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
