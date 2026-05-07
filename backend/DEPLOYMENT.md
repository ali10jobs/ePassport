# Deployment Guide

Target: a public staging URL (`https://api.epassport.example`) reachable by the React web app and the Flutter mobile app, with HTTPS and a Postgres + Redis stack behind it.

## Recommended stack for staging

| Component | Choice | Why |
|---|---|---|
| Compute | Hetzner Cloud CPX21 (3 vCPU, 4 GB) | €6/mo, AWS Middle-East-equivalent latency from Saudi |
| OS | Ubuntu 24.04 LTS | Forge default; modern PHP/PG packages |
| Web server | Nginx + PHP-FPM 8.4 | Forge-managed |
| Database | Managed Postgres 16 (Hetzner / Neon / Supabase) OR same VPS | Managed simpler; vault of backups |
| Cache/queue | Redis 7 (same VPS for staging) | Cheap, fast |
| TLS | Let's Encrypt via Forge | Free, auto-renew |
| Provisioning | Laravel Forge | Hides 90% of the ops surface |
| File storage | AWS S3 (`me-south-1`) | PDPL — keep data in Saudi region |
| Errors | Sentry | Free tier covers staging |

For production, switch DB to managed Postgres with daily snapshots, and add at least one read replica.

## Prerequisites

1. A registered domain (e.g. `epassport.example`) with DNS access.
2. Hetzner Cloud account + a project + a payment method.
3. Laravel Forge account ($12/mo).
4. AWS account with an S3 bucket in `me-south-1` and an IAM key.
5. (Optional) Sentry account.

## One-time provisioning

### 1. Create the Hetzner server via Forge

In Forge → Servers → Create Server → "Hetzner". Pick CPX21, Ubuntu 24.04, region `nbg1` (Falkenstein) or `hel1` (Helsinki). PHP 8.4. Database: PostgreSQL.

Wait ~5 min for provisioning. Forge installs Nginx, PHP-FPM 8.4, Postgres, Redis, supervisor, Composer.

### 2. Point DNS at the server

Add an A record in your DNS provider:

```
api.epassport.example.    A    <forge-server-ip>
```

### 3. Create the site in Forge

Forge → site name `api.epassport.example` → app type `General PHP/Laravel` → web directory `/public`.

In the site → "Apps" → Install Repository → connect GitHub → repo `ali10jobs/ePassport` → branch `main` → **subdirectory: `backend`**.

### 4. Environment variables

Forge → site → "Environment". Use this template (filled in):

```ini
APP_NAME=ePassport
APP_ENV=staging
APP_KEY=base64:GENERATE_VIA_php_artisan_key:generate
APP_DEBUG=false
APP_URL=https://api.epassport.example
APP_TIMEZONE=UTC

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=epassport
DB_USERNAME=forge
DB_PASSWORD=<forge-generated>

SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_DOMAIN=.epassport.example     # so app.epassport.example shares the cookie

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Sanctum: comma-separated origins of the SPA(s) that may use cookie auth
SANCTUM_STATEFUL_DOMAINS=app.epassport.example,localhost:5173

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=<iam-key>
AWS_SECRET_ACCESS_KEY=<iam-secret>
AWS_DEFAULT_REGION=me-south-1
AWS_BUCKET=epassport-staging
AWS_USE_PATH_STYLE_ENDPOINT=false

MAIL_MAILER=log

# Sentry (optional)
SENTRY_LARAVEL_DSN=

# Internal scribe auth-by-default placeholder
SCRIBE_AUTH_KEY=
```

Key points:
- `SESSION_DOMAIN=.epassport.example` lets `app.epassport.example` and `api.epassport.example` share the Sanctum session cookie.
- `SANCTUM_STATEFUL_DOMAINS` must include the SPA's host (no protocol).

### 5. Issue Let's Encrypt cert

Forge → site → "SSL" → "Let's Encrypt" → add `api.epassport.example` → activate.

### 6. First deploy

Forge → site → "Deployment" → set the deploy script:

```bash
cd $FORGE_SITE_PATH

git pull origin $FORGE_SITE_BRANCH

cd backend
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Reload PHP-FPM
( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan scribe:generate
```

Click "Deploy Now". Watch the deploy log; on success the site responds at `https://api.epassport.example`.

### 7. Seed the staging demo data

Forge → site → "Commands" → run:

```bash
cd backend && php artisan db:seed --class=Database\\Seeders\\DatabaseSeeder --force
```

(Only do this once on staging; never in prod.)

### 8. Queue worker (Horizon)

Forge → site → "Daemons" → Add daemon:

```
Command:   php artisan horizon
Directory: $FORGE_SITE_PATH/backend
User:      forge
```

This keeps the queue worker alive; Forge restarts it on deploy.

For Horizon's web UI: `https://api.epassport.example/horizon` (defaults are gated by the Horizon auth gate; configure via `app/Providers/HorizonServiceProvider.php`).

### 9. Smoke test the deploy

```bash
curl https://api.epassport.example/api/v1/health
# {"status":"ok","service":"ePassport API",...}

curl https://api.epassport.example/api/v1/openapi.json | head -c 200
# {"openapi":"3.1.0",...}
```

Login as a seeded user (token mode) and exercise a full demo flow:

```bash
curl -X POST https://api.epassport.example/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"khalid.maincon@epassport.local","password":"password","mode":"token","device_name":"smoke-test"}'
```

## Production cutover (post-MVP)

Differences from staging:

1. Separate Postgres instance with daily backups + point-in-time recovery (Hetzner managed PG, or Neon).
2. `APP_DEBUG=false`, `LOG_LEVEL=warning`.
3. Real email driver (Postmark / SES via Mailgun in `me-south-1` if available).
4. Disable demo seeders. Provision real organizations + users via a separate admin interface or SQL migration.
5. Rotate the `APP_KEY` and all webhook secrets at cutover.
6. Add a status page / uptime monitor (Better Uptime / UptimeRobot).
7. Sentry alerts wired to Slack.
8. CDN in front for static `/docs` assets (optional; Scribe is a few KB).

## CORS for the SPA

The web SPA at `https://app.epassport.example` calls `https://api.epassport.example`. Add an allow-list in `config/cors.php`:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => [
    'https://app.epassport.example',
    'http://localhost:5173',
],
'supports_credentials' => true,
```

This is required for cookie-based session auth from the SPA.

## Backups

For staging: Hetzner Cloud snapshots (manual + weekly automated) are sufficient.

For production: at minimum daily managed-Postgres backups with 14-day retention. Test restore quarterly.

## Cost estimate

| Item | Monthly |
|---|---|
| Hetzner CPX21 | €6 |
| Domain | ~$1 |
| Forge | $12 |
| AWS S3 (low usage) | ~$1 |
| Sentry (free tier) | $0 |
| **Total staging** | **~$22/mo** |

Production with managed PG + a load balancer + S3 traffic: ~$80–120/mo.
