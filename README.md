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
