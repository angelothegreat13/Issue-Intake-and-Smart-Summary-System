# Issue Intake & Smart Summary System

A Laravel 13 application for logging, triaging, and auto-summarising IT issues.  
Issues are automatically escalated and summarised — either by the Anthropic API (claude-3-haiku-20240307) or a built-in rules-based fallback when no API key is configured.

---

## Setup (5 commands)

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

Open **http://localhost:8000** in your browser.

**Optional:** to enable AI-powered summaries, add your key to `.env`:
```
ANTHROPIC_API_KEY=sk-ant-...
```

---

## API Endpoints

All endpoints are prefixed `/api`. Send `Accept: application/json` for JSON error responses.

### List issues

```bash
# All issues (paginated, 15 per page)
curl http://localhost:8000/api/issues \
  -H "Accept: application/json"

# With filters: ?status=open&priority=high&category=bug&escalated=1
curl "http://localhost:8000/api/issues?status=open&priority=critical" \
  -H "Accept: application/json"
```

### Create an issue

```bash
curl -X POST http://localhost:8000/api/issues \
  -H "Accept: application/json" \
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
curl http://localhost:8000/api/issues/1 \
  -H "Accept: application/json"
```

### Update an issue (partial)

```bash
curl -X PATCH http://localhost:8000/api/issues/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"status": "in_progress"}'
```

---

## Architecture & Key Decisions

```
routes/
  web.php          → Blade UI (GET/POST/PATCH /issues/*)
  api.php          → JSON API  (GET/POST/PATCH /api/issues/*)

app/
  Http/
    Controllers/
      IssueController.php         → web controller (returns views)
      Api/IssueController.php     → API controller (returns JSON)
    Requests/
      StoreIssueRequest.php       → validation for create
      UpdateIssueRequest.php      → validation for update (all fields optional)

  Services/
    IssueService.php    → orchestrates create/update/list; calls SummaryService;
                          applies escalation rules before every save
    SummaryService.php  → calls Anthropic API; falls back to rule-based logic
                          when ANTHROPIC_API_KEY is absent or the call fails

  Models/
    Issue.php           → Eloquent model with casts (escalated → bool, due_at → Carbon)

database/
  migrations/           → issues table schema
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

### Two-service split: `IssueService` + `SummaryService`

All business logic lives in services, not controllers. The split between the two services is intentional:

- **`IssueService`** owns the lifecycle of an issue — creating, updating, listing, applying escalation. It has no knowledge of the AI provider.
- **`SummaryService`** owns the single concern of "given this text, produce a summary and action." It tries the Anthropic API and silently degrades to rules-based output. Swapping to a different LLM provider touches only this one class.

This makes each class independently testable and the AI layer replaceable without touching business logic.

### Escalation logic (`IssueService::applyEscalation`)

`escalated` is recalculated and persisted on every create and update, so it always reflects current state:

| Condition | Reason |
|-----------|--------|
| `priority === 'critical'` | Needs immediate attention regardless of status |
| `priority === 'high'` AND `status === 'open'` | High-severity and not yet acknowledged |
| `due_at` is in the past AND `status !== 'resolved'` | Overdue and still open |

### Summary fallback (`SummaryService::rulesFallback`)

When no API key is configured (or the Anthropic call fails), the fallback produces deterministic output — a priority+category summary sentence and a `suggested_action` drawn from a decision tree: security → security team, critical → on-call page, high → senior engineer within the hour, and so on. The fallback is logged at `warning` level so operators can see when AI is unavailable.

### API JSON errors without `Accept` header

The exception handler in `bootstrap/app.php` intercepts `ValidationException` and `ModelNotFoundException` for any `api/*` route and returns structured JSON (`message` + `errors`) with the correct HTTP status, regardless of whether the caller sends `Accept: application/json`. This makes the API predictable for any HTTP client.

---

## What I'd Improve With More Time

- **Authentication & roles** — use Laravel Sanctum (already installed) to restrict who can create/edit/resolve issues. Add role-based access (reporter vs. responder vs. admin).
- **Job queues** — offload Anthropic API calls to a background queue job so the HTTP response is instant and never blocked by a slow API.
- **Unit & feature tests** — test `IssueService`, `SummaryService`, escalation rules, and all four API endpoints with PHPUnit/Pest.
- **React/Inertia frontend** — replace Blade with a React SPA via Inertia.js for richer interactivity (real-time filter, inline status updates).
- **Audit log** — track every status change and escalation in an `issue_events` table so teams can see the history of each issue.
- **Webhook/notification** — when `escalated` flips to `true`, fire a Slack or email notification to the on-call team.
