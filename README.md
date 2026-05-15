# Issue Intake & Smart Summary System

A Laravel 13 application for logging, triaging, and auto-summarising IT issues.  
Issues are automatically escalated and summarised — either by the Anthropic API (claude-3-haiku-20240307) or a built-in rules-based fallback when no API key is configured.

---

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Open **http://localhost:8000** in your browser.

**Optional:** to enable AI-powered summaries, add your key to `.env`:
```
ANTHROPIC_API_KEY=sk-ant-...
```

---

## API Endpoints

All endpoints are prefixed `/api/v1`. The `Accept: application/json` header is not required — the API always returns JSON.

### List issues

```bash
# All issues (paginated, 15 per page)
curl http://localhost:8000/api/v1/issues

# With filters: ?status=open&priority=high&category=bug&escalated=1
curl "http://localhost:8000/api/v1/issues?status=open&priority=critical"
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

### Update an issue (partial)

```bash
curl -X PATCH http://localhost:8000/api/v1/issues/1 \
  -H "Content-Type: application/json" \
  -d '{"status": "in_progress"}'
```

---

## Architecture & Key Decisions

```
routes/
  web.php          → Blade UI (GET/POST/PATCH /issues/*)
  api.php          → JSON API  (GET/POST/PATCH /api/v1/issues/*)

app/
  Enums/
    Priority.php   → low | medium | high | critical
    Status.php     → open | in_progress | resolved | closed
    Category.php   → bug | feature | infrastructure | performance | data | security

  Http/
    Controllers/
      IssueController.php           → web controller (returns views)
      Api/V1/IssueApiController.php → versioned API controller (returns JSON via Resources)
    Requests/
      StoreIssueRequest.php         → validation for create (Rule::enum for all enum fields)
      UpdateIssueRequest.php        → validation for update (all fields optional via sometimes)
    Resources/
      V1/IssueResource.php          → shapes the JSON response; exposes ->value strings

  Services/
    IssueService.php    → orchestrates create/update/list; calls SummaryService;
                          applies escalation rules before every save
    SummaryService.php  → calls Anthropic API; falls back to rule-based logic
                          when ANTHROPIC_API_KEY is absent or the call fails

  Models/
    Issue.php           → Eloquent model; casts priority/status/category to PHP enums,
                          escalated to bool, due_at to Carbon

database/
  migrations/           → issues table; priority/status/category as DB enum columns
  seeders/
    IssueSeeder.php     → 8 realistic sample issues (critical, security, overdue, etc.)

resources/views/issues/ → Bootstrap 5 Blade UI (layout, index, create, show, edit)
config/
  anthropic.php         → reads ANTHROPIC_API_KEY from .env
```

### Database: SQLite

SQLite was chosen deliberately for this submission:

- **Zero configuration** — a single file (`database/database.sqlite`), no server to install or start. The whole system runs with one command.
- **Perfect fit for the problem size** — issue tracking for a small ops team is a write-light, read-light workload. SQLite handles thousands of rows without complaint.
- **Easy to swap** — the only change needed to move to PostgreSQL or MySQL in production is three lines in `.env` (`DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`). Laravel's schema builder generates the correct DDL for any driver.

### PHP Enums for Priority, Status, and Category

All three fields are backed PHP enums enforced at three levels:

- **Database** — `enum` column type; the DB rejects any invalid value at write time.
- **Validation** — `Rule::enum(Priority::class)` in FormRequest classes; the API returns a structured 422 before any business logic runs.
- **Model** — Eloquent casts convert the stored string to the enum instance on read, so comparisons like `$issue->priority === Priority::Critical` are type-safe throughout the service layer.

### API Versioning

Controllers and API Resources are namespaced under `V1` (`Api/V1/IssueApiController`, `Resources/V1/IssueResource`). Routes are prefixed `/api/v1/`. When a breaking change is needed, a `V2` controller and resource are added alongside `V1` — existing clients are unaffected.

Validation request classes (`StoreIssueRequest`, `UpdateIssueRequest`) are **not** versioned because they express business rules for the `Issue` model, not API contract — both the web UI and API share the same validation logic.

### Two-service split: `IssueService` + `SummaryService`

All business logic lives in services, not controllers. The split between the two services is intentional:

- **`IssueService`** owns the lifecycle of an issue — creating, updating, listing, applying escalation. It has no knowledge of the AI provider.
- **`SummaryService`** owns the single concern of "given this text, produce a summary and action." It tries the Anthropic API and silently degrades to rules-based output. Swapping to a different LLM provider touches only this one class.

This makes each class independently testable and the AI layer replaceable without touching business logic.

### Escalation logic (`IssueService::applyEscalation`)

`escalated` is recalculated and persisted on every create and update, so it always reflects current state:

| Condition | Reason |
|-----------|--------|
| `priority === Priority::Critical` | Needs immediate attention regardless of status |
| `priority === Priority::High` AND `status === Status::Open` | High-severity and not yet acknowledged |
| `due_at` is in the past AND `status !== Status::Resolved` | Overdue and still open |

### Summary fallback (`SummaryService::rulesFallback`)

When no API key is configured (or the Anthropic call fails), the fallback produces deterministic output — a priority+category summary sentence and a `suggested_action` drawn from a decision tree: security → security team, critical → on-call page, high → senior engineer within the hour, and so on. The fallback is logged at `warning` level so operators can see when AI is unavailable.

### API JSON errors without `Accept` header

A `ForceJsonResponse` middleware is prepended to the API middleware group. It sets `Accept: application/json` on every `/api/*` request so Laravel's exception handler always returns JSON. Custom renderers in `bootstrap/app.php` additionally intercept `ValidationException` (422) and `NotFoundHttpException` (404) and return clean structured responses regardless of client headers.

---

## What I'd Improve With More Time

- **Authentication & roles** — use Laravel Sanctum (already installed) to restrict who can create/edit/resolve issues. Add role-based access (reporter vs. responder vs. admin).
- **Job queues** — offload Anthropic API calls to a background queue job so the HTTP response is instant and never blocked by a slow API.
- **Unit & feature tests** — test `IssueService`, `SummaryService`, escalation rules, and all four API endpoints with PHPUnit/Pest.
- **React/Inertia frontend** — replace Blade with a React SPA via Inertia.js for richer interactivity (real-time filter, inline status updates).
- **Audit log** — track every status change and escalation in an `issue_events` table so teams can see the history of each issue.
- **Webhook/notification** — when `escalated` flips to `true`, fire a Slack or email notification to the on-call team.
