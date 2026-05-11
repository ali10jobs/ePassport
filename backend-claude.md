# Backend (Laravel API) — Sprint Plan

> Full project context: see `master-claude.md`. Source: `plan-files/01_Platform_Backend_Laravel.pdf`.

## Role
Single source of truth. REST API consumed by React web (Sanctum cookie), Flutter mobile (Sanctum PAT), and future ERP integrations (Sanctum PATs with abilities).

## Stack
- Laravel 11, PHP 8.3
- PostgreSQL 16 (not MySQL — better JSON, better analytics later)
- Redis 7 (cache, queues, idempotency keys, rate limit counters)
- Laravel Sanctum (cookie session for web + PAT for mobile + PATs-with-abilities for ERPs)
- Spatie: laravel-permission (RBAC), laravel-query-builder (filtering), laravel-medialibrary (uploads), laravel-activitylog (audit)
- **Scribe** for OpenAPI 3.1 generation (chosen over L5-Swagger)
- Intervention Image (EXIF stripping, image processing)
- Laravel Horizon (queue monitoring)
- Pest for tests
- Larastan / PHPStan for static analysis (target level 5 in week 1, not 6)

## Decisions locked (defaults — override on the fly)
- **OpenAPI generator:** Scribe
- **Hazard photos at rest:** S3 SSE-S3 only (no field-level encryption in v1.0)
- **API key auth:** Sanctum PATs with abilities
- **Webhook signing:** HMAC-SHA256 only (asymmetric is post-MVP)
- **Hosting:** Hetzner Cloud (or DigitalOcean — pick before day 5)
- **Storage:** local driver for dev, S3 (AWS Middle East) for production
- **Larastan target:** level 5 in week 1

## API conventions (non-negotiable)
- All endpoints under `/api/v1/` (route group with prefix and namespace)
- REST-first, plural noun resources, standard HTTP verbs
- OpenAPI spec served at `/api/v1/openapi.json`, interactive docs at `/api/v1/docs`
- Idempotency-Key header on POST/PUT/PATCH, 24h dedupe via Redis
- Webhook payloads HMAC-SHA256 signed, retry with exponential backoff via Laravel queues
- All list endpoints support cursor + offset pagination, Spatie query-builder filtering, `?fields=` selection
- Stable error code strings:
  ```json
  { "error": { "code": "CERT_EXPIRED", "message": "...", "details": {...}, "request_id": "..." } }
  ```
- Audit-logged: every state-changing action via Spatie activitylog. Sensitive payloads (hazard photos, biometric data) NOT logged in full.
- Service-layer separation: controllers thin, business logic in `App\Services\*`, reusable across REST/queue jobs/future GraphQL.
- Soft deletes only — never lose historical data.
- All timestamps UTC, rendered in Asia/Riyadh timezone.

## Architectural requirements (non-negotiable)
- Multi-party data model designed correctly day one. Schema production-ready even with hardcoded seed data for demo.
- Cleanly extensible to future features (toolbox talks, daily inspections, incident management, NFC, geofenced zones, etc.) without refactoring core entities.
- Authentication supports multiple organizations per user with role per organization (pivot table `user_organization_roles`).
- All state-changing actions produce immutable audit log entries.
- File uploads via Laravel Storage facade (signed URLs for serving).
- Photo EXIF stripping mandatory for hazard reports — verify post-strip bytes contain no EXIF.
- Hazard reports have NO `submitter_id` column at all (anonymity at schema level).

## Day-by-day plan

### Day 1 — Foundations
- Run Doc 1 kickoff prompt verbatim. Iterate on data model + API surface + folder structure (NO implementation code yet).
- Install: Laravel 11, PostgreSQL, Redis, Horizon, Sanctum, Spatie permission/query-builder/medialibrary/activitylog, Scribe, Intervention Image, Pest, Larastan.
- Migrations written for the entire data model (orgs, engagements, projects, sites, workers, equipment, certifications, permits, hazard reports, audit log, webhook subscriptions, OAuth/PAT tables).
- Models scaffolded with relationships and casts (no method bodies).
- Service layer skeleton (`App\Services\PermitService` etc.).
- Custom error response handler with stable codes.
- OpenAPI spec served at `/api/v1/openapi.json` (stubbed endpoints fine).
- **Verification:** `php artisan migrate:fresh` succeeds; `/api/v1/openapi.json` returns valid JSON.

### Day 2 — Worker + Equipment APIs
- Worker CRUD: `POST/GET/PATCH/DELETE /api/v1/workers`, `GET /api/v1/workers/{id}/passport`, `POST /api/v1/workers/{id}/certifications`, `POST /api/v1/workers/{id}/qr` (helmet + coverall QRs).
- Worker bulk import: `POST /api/v1/workers/bulk` with idempotency, per-record success/failure.
- Worker medical profile fields on `workers`: `blood_type`, `allergies`, `chronic_conditions`, `emergency_contact_name`, `emergency_contact_phone` — surfaced on gate-scan result for on-site medics. Distinct from `worker_medical_records` (per-exam fitness history driving MEDICAL_FAIL).
- Equipment CRUD + TPI inspection attach + operator pairing + QR generation.
- Database seeder with **PRE-1 friend's authentic Saudi data**: 4 orgs (one per type), 1 project, 1 site, 30 workers across contractors and subs with realistic Saudi-context certs (NEBOSH, IOSH, Aramco SAEP, scaffolding, working-at-heights, confined-space) including some intentionally expired, 10 pieces of equipment with TPI certs (TÜV Rheinland, Bureau Veritas, SGS), 3 user accounts (one per role).
- **Verification:** `curl -X POST /api/v1/workers` creates a worker with PRE-1 cert types; QR endpoint returns printable image.

### Day 3 — Scan Verify + Permits + Hazard
- Scan verify: `POST /api/v1/scans/verify` returning `{ status: 'green'|'red', subject_type, subject_id, reasons: [...] }`. All reason codes wired: CERT_EXPIRED, INDUCTION_MISSING, MEDICAL_FAIL, ORG_NOT_ENGAGED, IMPERSONATION_FLAG.
- Helmet+coverall cross-check (two QRs scanned in sequence; mismatch → IMPERSONATION_FLAG).
- Equipment scan: TPI valid, last inspection unexpired, authorized operator paired (if scan happened with worker scan).
- Scan event logging (`POST/GET /api/v1/scans`).
- Permit-to-Work: `POST /api/v1/permits` (draft), attach workers/equipment, `POST /api/v1/permits/{id}/submit` with hard-block validation (returns 422 with per-worker/per-equipment reasons).
- Permit review: GET, approve, reject, close, lifecycle history.
- Hazard report anonymous: `POST /api/v1/hazard-reports/anonymous` (no auth, multipart, EXIF stripped server-side, returns random UUID `anonymous_report_id`). NO IP, device fingerprint, or PII stored.
- Hazard report status check: `GET /api/v1/hazard-reports/anonymous/{id}` (no auth).
- **Verification:** scan verify returns green/red with reason codes; permit submit returns 422 when worker has expired cert.

### Day 4 — Dashboards + Webhooks + Cross-cutting
- Role-based dashboard endpoints: `GET /api/v1/dashboards/{client|main-contractor|consultant|subcontractor}/summary`.
- Drill-down via prefiltered list endpoints (e.g., `?expiring_within=30`).
- Reports: `/api/v1/reports/*` time-series endpoints (cut from week 1 if time tight — see master cut-list).
- Webhook subscriptions: `POST /api/v1/webhooks` registration, HMAC-SHA256 signed payloads, retry with exponential backoff via queues, full delivery log.
- Webhook events: scan.green, scan.red, scan.impersonation_flag, permit.{created,submitted,validated,approved,rejected,closed}, hazard_report.{submitted,status_changed,resolved}.
- Idempotency middleware live on all writes.
- Rate limiting per credential with X-RateLimit-* headers.
- API key issuance (Sanctum PATs with abilities scoped per ERP integration).
- **Verification:** webhook fires on permit submit; X-RateLimit-Remaining returned; idempotent retry returns same response.

### Day 5 — Tests, deploy
- Pest feature tests on every P0 endpoint's happy path + primary failure path.
- Larastan level 5 passes.
- `.env.example` documented; deployment guide written.
- Deploy to Hetzner (or DigitalOcean) with HTTPS via Let's Encrypt.
- OpenAPI docs publicly accessible at staging URL with working "Try it out" for at least scan-verify and dashboards.
- Sentry wired for errors. Laravel Telescope for local debug only.
- Sample data seeded on staging.
- **Verification:** staging URL responds over HTTPS; Postman collection runs end-to-end against staging.

### Day 6 — Friend review + bug fixes
- Friend reviews API surface (especially permit reason codes and Saudi terminology) and signs off.
- README covers: run locally, seed sample data, authenticate as each role, where OpenAPI docs are, how to subscribe to a webhook.
- Fix only what the friend flags. No new features.

### Day 7 — Buffer + demo

## Cut-list (cut bottom-up if time slips)
1. Equipment bulk import (BE-E5)
2. Reports time-series endpoints (BE-D6)
3. OAuth2 client credentials flow (BE-API2)
4. Sample Postman/PHP/Python integration code (BE-API9)
5. Bulk permit creation (BE-P10)
6. Dashboard subcontractor summary (BE-D4)
7. Pest tests beyond happy path
8. Larastan above level 5

## Demo-critical, never cut
- Worker e-Passport endpoint
- Scan verify with all reason codes
- Permit submit hard-block with detailed 422
- Anonymous hazard submit + status check
- One dashboard endpoint per role
- Idempotency key middleware
- Audit log entries on state changes

## What this codebase does NOT do
- Render any UI (web is separate; mobile is separate)
- Hold session state for clients beyond Sanctum
- Implement post-MVP features (ERP adapters, NFC, biometric, geofencing, etc.)

## First action
Run the Doc 1 kickoff prompt verbatim from `plan-files/01_Platform_Backend_Laravel.pdf` Part 1 in the new `ePassport-backend/` repo. Do NOT start implementation in the first response — review the proposed data model, API surface, folder structure, and conventions first. Iterate until solid, THEN start Day 1 migrations.
