# e-Passport Construction Safety Platform — Master Plan

## Context

3-codebase MVP for a Saudi construction safety platform. Centerpiece: QR-scannable e-Passport for workers and equipment, validated at site gates. Multi-party model (Client / Main Contractor / Consultant / Subcontractor) baked in from day one. Bilingual Arabic+English with full RTL throughout.

**Primary constraint:** 7 calendar days from start to demo. PDFs estimate ~720h; 7 days × ~14h sustainable = ~98h solo + Claude Code aggressive execution. The gap is acknowledged. Plan trades depth for breadth on a thin demo slice and uses parallel Claude Code sessions to compress wall-clock time.

Source planning PDFs in `plan-files/`:
- `01_Platform_Backend_Laravel.pdf`
- `02_Web_Frontend_React.pdf`
- `03_Mobile_Flutter.pdf`

Per-codebase detailed plans (each used as the kickoff context in its own Claude Code session):
- `backend-claude.md` — Laravel 11 API
- `frontend-claude.md` — React 18 + Vite SPA
- `mobile-claude.md` — Flutter iOS + Android

---

## Decisions locked

1. **Architecture:** separate Laravel API + React SPA + Flutter mobile (no Inertia). The API is a first-class product because mobile + ERPs (SAP, Oracle EBS, Maximo, Primavera) consume it.
2. **Repo layout:** three sibling repos — `ePassport-backend/`, `ePassport-web/`, `ePassport-mobile/`. Each in its own Claude Code session.
3. **Offline support:** deferred to post-1.0. v1.0 online-only on both web and mobile, but Flutter code architected so v1.1 offline can be added without rewrites.
4. **PRE-1 deep-dive:** done. Saudi terminology (NEBOSH/IOSH, Aramco SAEP, scaffolding/working-at-heights/confined-space permit bodies, TPI bodies, equipment types, project org charts) is captured.
5. **`files.zip`:** ignored for now.
6. **Timeline:** hard one-week deadline, full scope attempted. Risk accepted.

---

## Master schedule

| Day | Backend | Web | Mobile |
|---|---|---|---|
| 1 | Foundations, data model, OpenAPI scaffold | Foundations, shell, RTL setup | Foundations, Apple/Google enrollment submitted |
| 2 | Worker + Equipment APIs + seeder | Auth + Worker list | dio + auth interceptor + types generated |
| 3 | Scan verify + Permits + Hazard anonymous | Worker detail + Equipment + Scan page | Auth flow + app shell |
| 4 | Dashboards + Webhooks + Idempotency | Permits + Hazards + Dashboards | Gate scan + Result screens |
| 5 | Tests + Larastan-5 + Hetzner deploy | RTL pass + Vercel deploy | Hazard report + Permit attach + Build configs |
| 6 | Bug fixes from friend review | Bug fixes | TestFlight + Internal Testing upload |
| 7 | Buffer + demo | Buffer + demo | Buffer + demo |

---

## Wall-clock blockers — start day 1

| Item | Why it blocks |
|---|---|
| Apple Developer Program enrollment ($99/yr) | 24–48h verification. Without it, no TestFlight on day 6. |
| Google Play Console ($25 one-time) | Required for Internal Testing track. |
| Domain registration + DNS | Sanctum cookie auth needs `app.X` + `api.X` on same registrable domain. |
| Hetzner Cloud (or DigitalOcean) account | Backend staging deploy day 5. |
| Vercel account | Web deploy day 5. |

---

## Cross-cutting decisions (apply to all three)

### Backend authentication for each consumer
- **Web:** Sanctum cookie session (same registrable domain required, e.g. `api.example.com` + `app.example.com`)
- **Mobile:** Sanctum personal access token, stored in `flutter_secure_storage`
- **ERPs (post-MVP):** Sanctum PATs with abilities (chosen over dedicated API key table for unified auth)

### Bilingual + RTL strategy
- Backend stores translations in JSON columns; frontends render
- Web uses `tailwindcss-rtl` plugin with `dir` attribute toggle
- Mobile uses Flutter intl + ARB files with Material 3 directionality
- RTL tested alongside every feature, not deferred to a polish phase

### Idempotency
- Every POST/PUT/PATCH accepts `Idempotency-Key` header from day 1
- Backend dedupes within 24h via Redis
- Web and mobile clients send fresh UUIDs per command in v1.0
- Mobile v1.1 offline queue replays the same key on retry — no backend change needed

### Error format (all consumers)
```json
{ "error": { "code": "CERT_EXPIRED", "message": "...", "details": {...}, "request_id": "..." } }
```
Stable error code strings, not just HTTP statuses. Frontends map codes to localized messages.

### Audit logging
Every state-changing action logs an immutable entry via Spatie activitylog. Hazard photos and biometric data NOT logged in full.

---

## Architectural rules so Flutter offline can be added in v1.1 without rewrites

1. **Repository pattern.** Every feature exposes a repository interface. v1.0 has `RemoteXRepository`. v1.1 adds `OfflineFirstXRepository` without touching consumers.
2. **Write operations modeled as commands.** v1.0 executes synchronously; v1.1 routes through a queue. Same shape, same result types.
3. **Idempotency-Key header on every POST/PUT/PATCH from day 1.**
4. **No business logic in widgets.** All cert/induction/scan reasoning is server-side in v1.0.
5. **Connectivity awareness wired from day 1** via `connectivity_plus` Riverpod provider.
6. **Sync-status banner space reserved** in app shell. v1.0 shows network state only.
7. **Dart domain models match drift's eventual shapes** to avoid future DTO/entity split.

---

## Cut-list — when (not if) the week slips

Cut from the bottom up. Every item below loses nothing the demo audience will notice in the first 20 minutes.

1. Mobile permit closure (M-P5)
2. Mobile worker/equipment lookup (M-L1, M-L2, M-L4)
3. Web bulk-import UIs (FE-W7, FE-E5)
4. Web subcontractor dashboard (FE-D4)
5. Webhook delivery log UI (web side)
6. Equipment bulk import backend (BE-E5)
7. Backend reports/* time-series endpoints (BE-D6)
8. Mobile permit approve from field (M-P6)
9. Web Playwright tests beyond 4 critical flows
10. Mobile integration test (M-N7)

**Demo-critical, never cut:** gate scan green/red, permit hard-block screen, worker e-Passport view, anonymous hazard submission (mobile), one role's dashboard, RTL working on the demo path.

---

## Risks accepted

- iOS App Store / TestFlight slip — fallback: web on phone covers scan demo
- Flutter learning curve (M-PRE3 tutorial cut) — mitigation: minimal mobile feature scope
- Bilingual/RTL polish debt — some screens ship LTR-only, get translated day 5
- PostgreSQL backups, full PHPStan-6, accessibility audit, cross-browser pass — all deferred
- Friend's review cycle compressed to one pass on day 6
- Cost: ~$150–200 setup + ~$30/mo

---

## What we are NOT doing in v1.0

- ERP integrations beyond auth/webhook scaffolding
- SAML / Microsoft Entra SSO
- GraphQL/gRPC layer
- NFC / biometric / rotating QR / BLE / IoT interlock
- Toolbox talks, daily inspections, incident management module
- All Doc 3 post-MVP items
- Full Larastan level 6, full Playwright suite, full a11y audit
- Offline support on either client

---

## Critical paths

- `/home/ali10jobs/code/LaravelApps/ePassport-backend/` — Laravel 11
- `/home/ali10jobs/code/LaravelApps/ePassport-web/` — Vite React SPA
- `/home/ali10jobs/code/LaravelApps/ePassport-mobile/` — Flutter
