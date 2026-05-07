# e-Passport Construction Safety Platform

Multi-codebase MVP for a Saudi construction safety platform. Centerpiece: QR-scannable e-Passport for workers and equipment, validated at site gates. Multi-party model (Client / Main Contractor / Consultant / Subcontractor). Bilingual Arabic + English with full RTL.

## Repo layout (monorepo)

- `backend/` — Laravel 11 API (single source of truth)
- `web/` — React 18 + Vite SPA *(coming day 1 in parallel)*
- `mobile/` — Flutter iOS + Android *(coming day 1 in parallel)*
- `plan-files/` — original planning PDFs
- `master-claude.md` — cross-cutting plan and decisions
- `backend-claude.md` — backend sprint plan
- `frontend-claude.md` — web sprint plan
- `mobile-claude.md` — mobile sprint plan

## Stack

| Layer | Tech |
|---|---|
| Backend | Laravel 11 + PHP 8.4 + PostgreSQL 16 + Redis |
| Web | React 18 + TypeScript + Vite + Tailwind + shadcn/ui |
| Mobile | Flutter 3.x + Riverpod + dio + go_router |

## Development status

7-day sprint to demo. See `master-claude.md` for the day-by-day schedule.

## Backend status (Days 1-5 complete)

The Laravel backend is feature-complete for the demo:

- 21 entity tables + 21 Eloquent models
- 60+ versioned API endpoints under `/api/v1/`
- All 5 reason codes (CERT_EXPIRED, MEDICAL_FAIL, INDUCTION_MISSING, ORG_NOT_ENGAGED, IMPERSONATION_FLAG, EQUIPMENT_TPI_EXPIRED, OPERATOR_NOT_AUTHORIZED, UNKNOWN_QR) wired through scan verification + permit submit
- Webhook delivery with HMAC-SHA256 + retry with exponential backoff
- Per-credential rate limiting with X-RateLimit-* headers
- API key issuance with scoped abilities for ERP integrations
- 34 Pest feature tests passing
- Larastan level 5 (with baseline)
- Demo data: 4 orgs, 30 workers (4 with red-scan candidates), 10 equipment

Quick start: see `backend/README.md`. Deploy: see `backend/DEPLOYMENT.md`.
