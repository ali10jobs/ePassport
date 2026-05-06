# Mobile (Flutter iOS + Android) — Sprint Plan

> Full project context: see `master-claude.md`. Source: `plan-files/03_Mobile_Flutter.pdf`.

## Role
Field application for safety engineers, supervisors, and (via the public anonymous flow) workers themselves. Optimized for fast scanning at site gates and anonymous hazard reporting that workers use without any login. Targets: iOS 13+ and Android 8+ (minSdk 26).

## Stack (final)
- Flutter 3.x stable, Dart 3.x (sound null safety, records, patterns, sealed classes)
- Riverpod (with riverpod_generator)
- dio + retrofit OR generated client from OpenAPI (decision: openapi-generator-cli, Dart Dio target)
- flutter_secure_storage (Sanctum token in Keychain/Keystore)
- mobile_scanner (QR — recommended over qr_code_scanner)
- camera or image_picker (hazard photos)
- go_router (typed routes)
- intl + ARB files (English + Arabic, Material 3 directionality)
- flutter_native_splash, flutter_launcher_icons
- connectivity_plus (network status — wired from day 1 even though offline is deferred)
- very_good_analysis (lints)
- Sentry (sentry_flutter) — **deferred to post-1.0 to save setup time**
- drift — **NOT in v1.0 (offline deferred)**

## Decisions locked (defaults)
- **OpenAPI client:** openapi-generator-cli (Dart Dio target)
- **drift:** not in v1.0
- **Anonymous hazard flow:** separate route tree outside auth shell
- **Sentry:** deferred
- **Flutter version:** latest stable
- **M-PRE3 throwaway tutorial:** cut to fit the week (acknowledged risk)

## Critical wall-clock blockers — submit DAY 1 HOUR 1
- **Apple Developer Program** ($99/yr, 24–48h verification)
- **Google Play Console** ($25 one-time)
- Without these, no TestFlight or Internal Testing track on day 6.

## Architectural rules (must hold so v1.1 offline can be added without rewrites)

1. **Repository pattern, not direct dio in widgets/providers.**
   Each feature exposes a repository interface (`WorkerRepository`, `ScanRepository`, etc.). v1.0 has one implementation: `RemoteXRepository` (dio-backed). v1.1 will add `OfflineFirstXRepository` wrapping remote + local. Widgets and providers never change.

2. **Write operations modeled as commands**, not direct API calls. E.g., `SubmitScanCommand`, `CreateHazardReportCommand`. v1.0 executes synchronously over HTTP. v1.1 routes through a queue. Same shape, validation, result types.

3. **Idempotency-Key header sent from day 1** on every POST/PUT/PATCH (backend supports it). v1.0 generates fresh UUID per command; v1.1 queue replays the same key on retry.

4. **No business logic in widgets.** All cert-validity / induction-status / scan-result reasoning lives server-side in v1.0. v1.1 local validation slots into the same `ScanResult` shape and reason codes.

5. **Riverpod provider boundaries match feature boundaries** so a feature's repository implementation can be swapped without touching consumers.

6. **Sync-status banner space reserved in shell.** v1.0 shows network state only ("Offline — reconnect to scan"). v1.1 grows to "X scans pending, last synced 5 min ago." No layout shift between versions.

7. **Connectivity awareness from day 1.** `connectivity_plus` integrated, network state exposed as a Riverpod provider. v1.0 uses it for offline banners + disabling scan/submit. v1.1 uses same provider to gate sync.

8. **Dart domain models match the shapes drift would mirror in v1.1.** No "API DTO vs local entity" split.

## Mobile-specific requirements (first mobile app — take seriously)
- **Permissions just-in-time, not at app start.** Camera (QR + hazard photos), location (optional, only for hazard reports), notifications (P1). Handle denial gracefully.
- **iOS Info.plist usage descriptions for every permission, in English AND Arabic.** Apple WILL reject the app if these strings are weak or missing.
- **Android minSdk 26.** Target latest stable SDK. ProGuard rules as needed.
- **App icon and splash screen designed for both LTR and RTL** Saudi market context.
- **Battery and performance:** safe to leave open all day at a gate without draining the phone.
- **Memory:** avoid massive in-memory image lists; use thumbnails.
- **Network:** show offline banner clearly via connectivity_plus. v1.0 disables scan/submit when offline (no queueing).
- **Accessibility:** large hit targets (workers wear gloves), high contrast, support for system dynamic type, semantic labels for screen readers.

## What v1.0 explicitly does NOT include
- drift / SQLite / SQLCipher — offline deferred
- Sync queue / conflict resolution
- Permit drafting offline
- Cached site roster
- Cache TTL / max-staleness logic
- Remote cache wipe protocol
- Sentry crash reporting (deferred to v1.0.1)
- Push notifications (FCM) — P1 in source, deferred
- M-PRE3 throwaway tutorial app (we're learning live, accepted risk)

## Day-by-day plan

### Day 1 — Foundations + enrollment
- **Submit Apple Developer + Google Play applications IMMEDIATELY.**
- Run Doc 3 kickoff prompt verbatim. Iterate on folder structure, routing, API client architecture, conventions, error handling, theming, RTL approach (NO implementation yet).
- Initialize Flutter project with agreed structure: `lib/main.dart`, `lib/app.dart`, `lib/core/{config,network,storage,theme,l10n}/`, `lib/features/{auth,scan,permits,hazard_reports,workers,equipment,dashboard}/{data,domain,presentation}/`.
- very_good_analysis lints configured.
- go_router with typed routes, auth-protected vs public, placeholder for each MVP screen.
- Riverpod with riverpod_generator, sample feature provider working end-to-end.
- intl + ARB for English + Arabic, language switcher toggling Material 3 directionality.
- Material 3 theme with custom design tokens.
- **Verification:** `flutter run` builds and launches on iOS Simulator + Android Emulator with the language switcher working.

### Day 2 — Auth + API client
- dio configured: auth header from secure storage, retry on transient errors, error mapping to typed exceptions, request ID injection.
- OpenAPI types generated from backend spec into Dart classes via openapi-generator-cli.
- Typed API service wired up against staging backend.
- flutter_secure_storage wrapping Sanctum token storage.
- connectivity_plus integrated as Riverpod provider.
- Repository pattern stubs for every feature.
- **Verification:** API call hits backend with auth header; offline banner appears when wifi disabled.

### Day 3 — Auth flow + App shell
- Launch screen with app logo, login button (authenticated users), "Report Hazard Anonymously" button (no login). Language switcher visible.
- Login: email + password → Sanctum token stored securely → user/orgs loaded → routed to app shell.
- Org switcher in top bar.
- App shell: bottom nav (Scan, Permits, Hazards, Profile or per-role variation). Top bar with org switcher + language toggle.
- Logout: token cleared, routed to launch screen.
- Session expiry: graceful re-auth prompt.
- **Verification:** login works on real device against staging; org switch refreshes data.

### Day 4 — Gate scan flow (the demo centerpiece)
- Tap Scan in bottom nav → full-screen camera viewport with QR alignment overlay. Landscape + portrait. Camera permission requested in-context with clear explanation.
- Scan worker QR → big GREEN result screen within 1 second showing worker name, photo, employer, all valid certs — OR big RED screen showing exactly which certs are expired/missing, induction status failures, with localized reason codes.
- After worker scan, prompt to scan coverall QR for cross-check. Mismatch triggers prominent IMPERSONATION FLAG screen.
- Equipment QR scan → green/red based on TPI validity and operator pairing.
- Every scan logged locally, uploaded to backend (synchronously in v1.0), shown in recent scan history (last 50).
- Manual fallback: enter worker employee ID or equipment ID if QR scan fails. Manual entries flagged in scan log.
- Gloves-friendly hit targets, high contrast, works in Arabic with RTL.
- **Verification:** scan a printed worker QR on device, see correct green/red within 1 second.

### Day 5 — Hazard report + Permit attach + Build configs
- **Anonymous hazard flow** (no login, public route tree):
  - Tap "Report Hazard Anonymously" on launch → photo capture (camera or gallery fallback), EXIF stripped client-side before upload (verify post-strip)
  - Pick category from icon-driven choices (fall, electrical, fire, working at heights, lifting, housekeeping, PPE, environmental, other) — icons over text for low-literacy users
  - Pick severity (low/medium/high/critical) via color-coded buttons
  - Optional typed description; optional GPS with explicit consent ("Where is the hazard?")
  - Submit → success screen with large copyable `anonymous_report_id`
  - **NO PII** captured or sent: no IP, device fingerprint, account info — verified with tests
- Authenticated hazard list (supervisor/contractor): photos, status updates, internal notes.
- Public "Check Report Status" page: enter `anonymous_report_id`, see public update notes only.
- Permit attach worker/equipment by QR scan (reuses scan logic in attach mode — does NOT create scan events).
- iOS bundle ID, signing certificates, provisioning profiles, app icons, splash screens, Info.plist usage descriptions in English + Arabic.
- Android applicationId, signing keys (kept secure), app icons, splash, AndroidManifest.
- **Verification:** anonymous flow submits and returns ID; build runs on physical iOS + Android devices.

### Day 6 — Test distribution + friend review
- Privacy policy written (covers anonymous reporting + biometric/location handling) and hosted publicly. Required by both stores.
- App Store Connect listing (description, screenshots, keywords) prepared and submitted to TestFlight Internal Testing track. Apple review NOT in critical path for demo — TestFlight internal is sufficient.
- Google Play Console listing prepared and uploaded to Internal Testing track. Google review for internal is fast (hours).
- Friend installs both builds via TestFlight + Google Internal Testing. Reviews on real device in both languages.
- Widget tests for: gate scan result screen, hazard submission flow, permit validation error screen (the moments most likely to break in the demo).
- Fix friend-flagged issues only.
- **Verification:** friend's phone has both builds installed and they can complete a happy-path scan against staging.

### Day 7 — Buffer + demo
- 20-minute demo script with backup plans for camera/network failure modes.
- Rehearse on real device.
- Do NOT add features.

## Cut-list (cut bottom-up if time slips)
1. Worker/equipment lookup screens (M-L1, M-L2, M-L4) — supervisors can use the web app
2. Permit closure (M-P5)
3. Permit approve from field (M-P6) — consultants can use the web app
4. Scan history view (M-S5) — backend logs it; not demo-critical
5. Manual entry fallback (M-S6) — pretend the camera always works in demo
6. Integration test (M-N7)
7. Push notifications (M-N2) — already P1 in source, fully cut
8. App Store / Play Store public submission — TestFlight + Internal Testing only

## Demo-critical, never cut
- Launch screen with anonymous flow accessible
- Login + app shell
- Gate scan green/red with reason codes (the centerpiece)
- Helmet + coverall cross-check
- Anonymous hazard report submission with EXIF strip + no-PII verification
- iOS + Android builds running on real devices
- Arabic + RTL on the demo path

## Risks specific to mobile (acknowledged)
- **Apple verification slip beyond 48h** — fallback: web on phone covers scan demo
- **Flutter learning curve hits day 3 hard** — mitigation: keep mobile feature scope minimal; backend + web carry the demo if mobile slips
- **TestFlight build processing slower than expected** — mitigation: upload day 5 night, not day 6 morning
- **iOS signing / provisioning issues** (notoriously painful first time) — budget extra hours day 5

## First action
**Submit Apple Developer + Google Play enrollments immediately — these have wall-clock minimums that will block day 6 if delayed.** Then run the Doc 3 kickoff prompt verbatim from `plan-files/03_Mobile_Flutter.pdf` Part 1 in the new `ePassport-mobile/` repo. Do NOT start implementation in the first response — review folder structure, routing, API client, offline-deferral architectural rules, theme + RTL approach. Iterate until solid, THEN start Day 1 scaffolding.
