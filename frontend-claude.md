# Frontend (React Web SPA) — Sprint Plan

> Full project context: see `master-claude.md`. Source: `plan-files/02_Web_Frontend_React.pdf`.

## Role
Web admin and dashboard application for office-based roles: HSE managers, safety engineers, consultants, client safety leadership. Field roles use the Flutter mobile app — not this. Consumes the Laravel API exclusively. Bilingual Arabic+English with full RTL.

## Stack
- React 18 + TypeScript 5.x (strict mode)
- Vite (build tool)
- React Router v6
- TanStack Query (server state, caching, mutations)
- Tailwind CSS + tailwindcss-rtl + tailwindcss-animate
- shadcn/ui (built on Radix; install components as needed)
- React Hook Form + Zod
- react-i18next (Arabic + English namespaces per feature)
- openapi-typescript (vendored snapshot, regenerated manually after backend deploys)
- ofetch (HTTP client wrapped in typed wrappers) — Sanctum CSRF + cookie handling
- @zxing/browser (QR scanning via getUserMedia)
- date-fns + date-fns/locale/ar-SA
- ESLint + Prettier + TypeScript ESLint
- Vitest + React Testing Library
- Playwright (E2E for: login, gate scan, permit issuance, hazard list)

## Decisions locked (defaults)
- **Type generation:** vendored OpenAPI JSON snapshot, manual regen after backend deploys
- **QR scanner:** `@zxing/browser`
- **RTL:** `tailwindcss-rtl` plugin (faster than logical properties everywhere)
- **Theme:** light only for v1.0
- **Auth state:** TanStack Query only (no Zustand layer)
- **Hosting:** Vercel

## Architectural requirements
- TypeScript strict. No `any` except where genuinely necessary, justified with a comment.
- All API calls go through a single typed API client wrapping ofetch. Types from openapi-typescript.
- Component layers: `pages/` (route-level), `features/` (domain-specific), `components/ui/` (shadcn), `components/shared/`, `hooks/`, `lib/`, `api/`, `i18n/`.
- No business logic in pages — pages compose feature components and hooks.
- All forms use RHF + Zod schemas matching backend validation.
- Routing: React Router v6 with typed routes, route-level code splitting, `ProtectedRoute` wrapper checking auth + role.
- Tailwind config includes RTL plugin and proper logical properties usage. RTL must look identical-quality to LTR.
- shadcn customized via CSS variable theming + custom design tokens. Status colors green/red/amber are non-negotiable for verification screens.
- i18n: react-i18next with namespaces per feature. Arabic translations required for ALL strings in v1.0. Numbers/dates via Intl APIs respecting locale.
- Accessibility: keyboard nav, ARIA where needed, WCAG AA contrast. Workers in PPE may use these screens with gloves — large hit targets, big readable type.
- Error handling: every API error mapped from backend's stable error code to localized message. Toast (sonner) for global + inline form errors. No silent failures.
- Loading states: skeleton or spinner on every async op. No layout shift.
- Mobile responsiveness: desktop-first for most screens, BUT gate-scan page and permit-attach-worker-by-scan flow must work flawlessly on phones in landscape and portrait.

## NOT in scope (do not implement)
- Anonymous hazard report SUBMISSION (mobile only)
- Offline support (mobile only — see master plan)
- Push notifications
- Native device features beyond browser camera

## Day-by-day plan
*(Web work officially starts day 1 in parallel with backend, mocking against OpenAPI until live endpoints exist.)*

### Day 1 — Foundations
- Run Doc 2 kickoff prompt verbatim. Iterate on folder structure, routing, API client architecture, conventions (NO implementation yet).
- Vite + React 18 + TS strict + ESLint + Prettier + path aliases (`@/`).
- Tailwind + tailwindcss-rtl + tailwindcss-animate. Custom design tokens (status colors, typography scale, spacing).
- shadcn/ui initialized.
- TanStack Query configured (sensible defaults: stale time, retry, refetch on focus disabled).
- React Router v6: AuthLayout, AppLayout (sidebar + topnav), ProtectedRoute. Empty routes for every MVP screen.
- react-i18next: English + Arabic namespaces per feature, language switcher toggling document `dir`.
- RHF + Zod form pattern with reusable wrapped primitives (Input, Select, DatePicker, FileUpload).
- Error mapping layer: backend stable codes → localized messages.
- openapi-typescript types generated from backend's stub OpenAPI spec (vendored).
- Typed API client (ofetch) with Sanctum CSRF + cookie handling.
- **Verification:** project builds, dev server runs, language switcher flips RTL on a placeholder page.

### Day 2 — Auth + Worker list
- Login page (email + password, language switcher, platform logo). Establishes Sanctum session, routes to role's dashboard.
- Organization switcher in topnav (for users with multiple memberships).
- AppLayout shell: sidebar with role-appropriate nav, topbar with user menu (profile, settings, logout, language toggle).
- Logout: session destroyed, TanStack Query caches cleared, redirected to login.
- Session refresh handled gracefully — expired sessions show re-auth prompt instead of crashing.
- Worker list page: paginated, filterable (cert status, expiring within N days, employer org), sortable, search by name/employee ID.
- **Verification:** login works end-to-end; worker list renders against live backend.

### Day 3 — Worker detail + Equipment + Scan page
- Worker detail / e-Passport view: identity, photo, employer, all certs with expiry dates and status badges (green/amber/red), medical fitness, induction status, scan history, permits they're named on.
- Worker create (multi-step form: identity → certs → medical), validation matching backend rules, file upload via signed URL flow.
- Worker edit + add certs post-creation.
- Generate + print QR codes for a worker (helmet + coverall), print-optimized layout for adhesive labels.
- Soft-delete worker with confirmation modal; soft-deleted list with restore option.
- Equipment list, detail, create/edit, attach TPI certs, pair authorized operators (worker selector filtering by relevant cert), generate + print QR.
- Scan page: full-screen camera viewport via @zxing/browser, optimized for landscape + portrait, clear QR alignment frame.
- **Verification:** worker e-Passport renders all details; scan page reads QR codes on a phone via dev tunnel.

### Day 4 — Permits + Hazards + Dashboards
- Permit list: filtered by status, type, date range, location, named worker, named equipment. Pagination + sorting.
- Permit create wizard: type → date/location/scope → attach workers → attach equipment → review → submit.
- Permit hard-block error screen on validation failure: shows exactly which worker has which expired cert, which equipment has expired TPI. Each issue actionable (link to worker profile to fix the cert).
- Consultant approve/reject UI.
- Permit lifecycle history view + close with notes.
- Hazard report list (authenticated users only): photo thumbnails, severity badges, status, submission time, click for full details.
- Status update (under_review → action_issued → resolved), internal notes (NOT visible to anonymous submitter), public update notes (visible).
- Public anonymous status check page (no auth) at `/hazard-status?id=...`.
- Role-based dashboards:
  - Client: cross-project metrics (workers across contractors, certs expiring 30/60/90 days, active permits, hazard reports MTD, incident counts). Charts via Recharts.
  - Main contractor: scoped to org.
  - Consultant: permits awaiting review, supervised hazard reports.
  - Subcontractor: narrow scope (own workers, equipment, named permits).
- All dashboard metrics clickable for drill-down via prefiltered list endpoints.
- **Verification:** permit submission with bad cert shows the hard-block screen with correct worker named.

### Day 5 — Bilingual pass + RTL + deploy
- Full pass through every shipped screen in Arabic; verify RTL is correct (sidebar flips, icons mirror where appropriate, text alignment correct, charts render right-aligned).
- Mobile-responsive pass on real phones (iOS Safari + Android Chrome) for scan and hazard pages.
- Accessibility audit (axe DevTools): no critical issues, keyboard nav works, color contrast WCAG AA.
- Deploy to Vercel pointing at staging API URL. Environment-specific config.
- **Verification:** every screen works in both languages; staging URL serves the app.

### Day 6 — Friend review + bug fixes
- Friend reviews UI in both languages; signs off on terminology and workflow authenticity.
- Fix only flagged issues.
- 12-slide pitch deck with screenshots from this app + 20-minute demo script with backup plans for failure modes.

### Day 7 — Buffer + demo

## Cut-list (cut bottom-up if time slips)
1. Bulk worker import UI (FE-W7) — backend supports it; UI is post-MVP
2. Bulk equipment import UI (FE-E5)
3. Subcontractor dashboard (FE-D4) — most users won't be subs
4. Soft-delete restore UI (just hide soft-deleted)
5. Print-optimized QR layout (use plain QR for demo)
6. Playwright tests beyond login + scan + permit + hazard
7. Vitest unit tests on hooks
8. Mobile-responsive on devices beyond demo phones
9. Accessibility audit polish

## Demo-critical, never cut
- Login + role-aware shell
- Worker list + e-Passport detail
- Permit create wizard + hard-block screen
- Scan page (functional on phone)
- One role's dashboard
- RTL working on the demo path
- Language toggle working

## First action
Run the Doc 2 kickoff prompt verbatim from `plan-files/02_Web_Frontend_React.pdf` Part 1 in the new `ePassport-web/` repo. Do NOT start implementation in the first response — review proposed folder structure, routing, API client architecture, conventions. Iterate until solid, THEN start Day 1 scaffolding.
