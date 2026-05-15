# Issue Intake & Smart Summary System

A Laravel 13 application that lets a support or operations team log, triage, and track IT issues. When an issue is submitted, the system automatically generates a short summary and a suggested next action — either via the Anthropic API or a built-in rules-based fallback if no API key is available.

---

## Requirements

- PHP 8.2 or higher
- Composer
- SQLite (bundled with PHP — no separate install needed)

---

## Getting Started

Run these commands in order from the project root:

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Then open **http://localhost:8000** in your browser. The seeder loads 8 realistic sample issues covering a range of priorities, categories, and escalation states so you can see the system in action immediately.

### Optional: enable AI-powered summaries

By default the system uses a rules-based engine to generate summaries. To switch to the Anthropic API, add your key to `.env` before running the seeder:

```
ANTHROPIC_API_KEY=sk-ant-...
```

Without the key the app works fully — it just uses the built-in fallback and logs a warning to `storage/logs/laravel.log`.

---

## Using the Web UI

The Blade interface at **http://localhost:8000** covers the full workflow:

- **List** — browse all issues with filters for status, priority, category, and escalation flag
- **Create** — submit a new issue; summary and suggested action are generated automatically on save
- **View** — see the full issue detail, generated summary, and suggested action
- **Edit** — update any field; changing the description regenerates the summary

---

## API Reference

All endpoints are under `/api/v1`. The API always returns JSON — no `Accept` header required.

### List issues

```bash
curl http://localhost:8000/api/v1/issues
```

Filter by any combination of `status`, `priority`, `category`, or `escalated`:

```bash
curl "http://localhost:8000/api/v1/issues?status=open&priority=critical"
curl "http://localhost:8000/api/v1/issues?escalated=1"
```

### Create an issue

```bash
curl -X POST http://localhost:8000/api/v1/issues \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Login page throws 500 on mobile Safari",
    "description": "Users on iOS 17 + Safari report a blank screen after submitting the login form. The error log shows a CSRF token mismatch, but only on mobile. Reproducible 100% of the time with iPhone 15 Pro on cellular.",
    "priority": "high",
    "category": "bug",
    "status": "open"
  }'
```

### Get a single issue

```bash
curl http://localhost:8000/api/v1/issues/1
```

### Update an issue (partial update)

```bash
curl -X PATCH http://localhost:8000/api/v1/issues/1 \
  -H "Content-Type: application/json" \
  -d '{"status": "in_progress"}'
```

### Valid field values

| Field | Accepted values |
|---|---|
| `priority` | `low`, `medium`, `high`, `critical` |
| `category` | `bug`, `feature`, `infrastructure`, `performance`, `data`, `security` |
| `status` | `open`, `in_progress`, `resolved`, `closed` |

Invalid values return a structured `422` with field-level error messages.

---

## Running the Tests

```bash
php artisan test
```

The test suite covers:

- `RulesSummaryService` — unit tests for each branch of the rules engine
- `AnthropicSummaryService` — tests the happy path, HTTP failures, and bad response formats using `Http::fake()` (no real API call made)
- `IssueService` escalation logic — all three escalation conditions plus the non-escalation cases, using a fake `SummaryServiceInterface` injected directly
- API endpoints — create, validate, list, show, 404, update, and status filter

---

## Architecture & Key Decisions

```
routes/
  web.php          → Blade UI  (GET/POST/PATCH /issues/*)
  api.php          → JSON API  (GET/POST/PATCH /api/v1/issues/*)

app/
  Enums/
    Priority.php   → low | medium | high | critical
    Status.php     → open | in_progress | resolved | closed
    Category.php   → bug | feature | infrastructure | performance | data | security

  Contracts/
    SummaryServiceInterface.php  → contract shared by both summary implementations

  Services/
    IssueService.php             → creates, updates, and lists issues; applies escalation on every save
    AnthropicSummaryService.php  → calls Anthropic API (claude-3-haiku-20240307)
    RulesSummaryService.php      → deterministic rules-based fallback; no API key needed

  Http/
    Controllers/
      IssueController.php           → web controller (Blade views)
      Api/V1/IssueApiController.php → versioned API controller (JSON via Resources)
    Requests/
      StoreIssueRequest.php         → create validation (Rule::enum for all enum fields)
      UpdateIssueRequest.php        → update validation (all fields optional via sometimes)
    Resources/
      V1/IssueResource.php          → shapes the JSON response

  Models/
    Issue.php  → casts priority/status/category to PHP enums, escalated to bool, due_at to Carbon

database/
  migrations/     → issues table with enum columns for priority, status, and category
  seeders/
    IssueSeeder.php  → 8 realistic sample issues
  factories/
    IssueFactory.php → used by the test suite

resources/views/issues/  → Bootstrap 5 Blade UI (layout, index, create, show, edit)
tests/
  Unit/Services/   → RulesSummaryService, AnthropicSummaryService
  Feature/
    Services/      → IssueService escalation logic
    Api/V1/        → full API endpoint coverage
```

### Database — SQLite

SQLite was chosen deliberately for this submission. There is nothing to install or configure — the database is a single file and the whole system runs with one command. The workload (a small ops team logging issues) is a good fit for SQLite. Moving to PostgreSQL or MySQL in production requires only three lines in `.env`; the rest of the codebase is unchanged.

### PHP Enums for Priority, Status, and Category

All three fields are backed enums enforced at every layer:

- **Database** — `enum` column; the DB rejects invalid values at write time
- **Validation** — `Rule::enum(Priority::class)` in FormRequest; the API returns a 422 before any business logic runs
- **Model** — Eloquent casts convert the stored string to the enum instance on read, so comparisons like `$issue->priority === Priority::Critical` are type-safe in the service layer
- **Views** — selects are generated from `Priority::cases()` so adding a new enum case automatically appears in the UI

### Service layer and interface split

Business logic lives in services, not controllers. `IssueService` depends on `SummaryServiceInterface` — not on `AnthropicSummaryService` directly. `AppServiceProvider` decides which implementation to bind at boot time based on whether `ANTHROPIC_API_KEY` is set.

This means:
- Swapping to a different LLM provider means writing one new class that implements the interface — nothing else changes
- In tests, a fake implementation can be injected directly without touching the container or mocking framework magic

### Escalation logic

`escalated` is recalculated and saved on every create and update:

| Condition | Reason |
|---|---|
| `priority === Critical` | Needs immediate attention regardless of status |
| `priority === High` AND `status === Open` | High-severity and not yet acknowledged |
| `due_at` is in the past AND `status !== Resolved` | Overdue and still unresolved |

### API versioning

Controllers and Resources are namespaced under `V1`. Routes are prefixed `/api/v1/`. When a breaking change is needed, a `V2` controller and resource are added alongside `V1` without affecting existing clients.

FormRequest classes are not versioned — they express business rules for the `Issue` model, which are the same regardless of which API version is called.

---

## What I'd Improve With More Time

- **Authentication & roles** — use Laravel Sanctum (already installed) to gate who can create, edit, and resolve issues; add role-based access for reporter vs. responder vs. admin
- **Job queues** — move Anthropic API calls to a background queue job so the HTTP response is never blocked by a slow or unavailable external service
- **React/Inertia frontend** — replace the Blade UI with a React SPA via Inertia.js for inline status updates and real-time filtering without full page reloads
- **Audit log** — record every status change and escalation event in an `issue_events` table so teams can see the full history of an issue
- **Webhook notifications** — fire a Slack or email alert when `escalated` flips to `true` so the on-call team is notified immediately
