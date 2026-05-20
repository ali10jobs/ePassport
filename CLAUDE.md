# ePassport — Operational Notes for Claude

Living instructions for future Claude sessions. Project-level planning docs are in `master-claude.md`, `backend-claude.md`, `frontend-claude.md`, `mobile-claude.md`.

## Production infrastructure

- **Backend:** DigitalOcean droplet, 1 vCPU / 1 GB RAM, Singapore region. Reachable at `https://168-144-128-76.nip.io`. SSH as `ali@168.144.128.76`.
- **Database:** Supabase free tier, **Singapore** (`aws-1-ap-southeast-1.pooler.supabase.com:6543`). Project name `epassport_singapore`. Old Tokyo project still exists as a fallback (delete when confident).
- **Cache / queue / session:** Redis on the droplet (`127.0.0.1:6379`).
- **Object storage:** Cloudflare R2 (presigned URLs from Laravel, 7-day TTL, cached in Redis for 6 days).
- **Mobile API default:** `API_BASE_URL=https://168-144-128-76.nip.io` baked into `mobile/lib/api/api_client.dart`. Always rebuild with the explicit `--dart-define` for release.

## Deploy command (backend)

The droplet pulls and rebuilds caches. Opcache has `validate_timestamps=0` so **php-fpm must be reloaded** for new code to take effect:

```bash
ssh ali@168.144.128.76 'cd /home/ali/code/ePassport && git pull origin main && cd backend && php artisan config:cache && php artisan route:cache && sudo systemctl reload php8.4-fpm'
```

The final `sudo systemctl reload php8.4-fpm` runs without prompting because `/etc/sudoers.d/ali-opcache` grants `ali` NOPASSWD for that exact command (+ writing the opcache.ini). Don't drop the reload — without it, code changes are invisible until a manual php-fpm restart.

Composer install / migrations are only needed when dependencies or migrations change:

```bash
# When composer.json/composer.lock changes
ssh ali@168.144.128.76 'cd /home/ali/code/ePassport/backend && composer install --no-dev --classmap-authoritative'

# When new migrations land
ssh ali@168.144.128.76 'cd /home/ali/code/ePassport/backend && php artisan migrate --force'
```

## Performance posture (do not regress)

- **Dashboard endpoints** use `Cache::remember(..., 30s)` keyed on org id. Each role's summary collapses ~20 separate `COUNT()`s into ~4 Postgres `FILTER`-aggregation queries. Don't put a list/scan back into a per-metric COUNT — keep the consolidated form.
- **`/me`** is cached for 60s per user id. Cache invalidation isn't wired to org-role mutations; if you start letting users switch their default org mid-session, bust the `me:user:{id}` key.
- **Worker `photo_url` accessor** caches the presigned R2 URL for 6 days keyed on `sha1(photo_path)`. Re-upload changes the path and busts cleanly.
- **PHP opcache JIT (tracing)** is enabled with `validate_timestamps=0`. Means every deploy MUST reload php-fpm. The deploy command above does this.

## Demo data

`DemoDataSeeder` seeds:
- 4 orgs, 1 project, 1 site, 3 engagements
- 3 user accounts (password is the literal string `password`):
  - `sara.client@epassport.local` (client safety lead)
  - `khalid.maincon@epassport.local` (main contractor HSE manager) — the most common test login
  - `nasser.consultant@epassport.local` (consultant)
- 30 workers (some certs intentionally expired for red-scan demos)
- 10 equipment items with TPI certs
- 4 demo permits (2 drafts + 2 awaiting consultant review)

`DatabaseSeeder` runs catalogs (`CertificationTypeSeeder`, `PermitTypeSeeder`, `RoleSeeder`) first; `DemoDataSeeder` depends on them. If `permit_types` is empty, the permit seeding silently no-ops — run `PermitTypeSeeder` then `DemoDataSeeder`.

## Things that need sudo on the droplet

`ali` has passwordless sudo ONLY for:
- `tee /etc/php/8.4/mods-available/opcache.ini`
- `cp /etc/php/8.4/mods-available/opcache.ini*`
- `systemctl reload php8.4-fpm`

Anything else (nginx config, package install, full restart) needs interactive `! ssh -t ali@…` from the user's terminal.
