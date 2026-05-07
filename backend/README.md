# ePassport Backend

REST API for the e-Passport construction safety platform. Single source of truth — consumed by the React web SPA, Flutter mobile app, and ERP integrations.

## Stack

- Laravel 11 + PHP 8.4
- PostgreSQL 16
- Redis 7 (cache, queues, idempotency keys, rate-limit counters)
- Laravel Sanctum (cookie session for web, PAT for mobile + ERPs)
- Spatie permission/query-builder/medialibrary/activitylog
- Scribe (OpenAPI 3.1 generation)
- Endroid QR Code, Intervention Image (EXIF strip)
- Pest, Larastan, Pint

## Local development setup

```bash
# 1. Clone the monorepo and enter the backend
cd backend

# 2. PHP deps
composer install

# 3. PostgreSQL + Redis
createdb epassport_dev
createdb epassport_test

# 4. Env
cp .env.example .env
php artisan key:generate
# edit .env: DB_USERNAME etc.

# 5. Schema + seed demo data
php artisan migrate:fresh --seed

# 6. Run
php artisan serve --port=8000
# In a second terminal: php artisan horizon  (queue worker)

# 7. Verify
curl http://127.0.0.1:8000/api/v1/health
```

## Demo accounts (password `password` — local dev only)

| Email                              | Role                |
|------------------------------------|---------------------|
| `sara.client@epassport.local`      | client_safety_lead  |
| `khalid.maincon@epassport.local`   | hse_manager (main contractor) |
| `nasser.consultant@epassport.local`| consultant          |

## Login (token mode for curl/Postman)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"khalid.maincon@epassport.local","password":"password","mode":"token","device_name":"my-cli"}'
```

Use the returned `access_token` as `Authorization: Bearer <token>` on subsequent requests.

## API documentation

- OpenAPI 3.1 spec:    `GET /api/v1/openapi.json`
- Interactive HTML:    `GET /api/v1/docs`
- Postman collection:  `storage/app/private/scribe/collection.json`

Re-generate after adding endpoints: `php artisan scribe:generate`.

## Testing

```bash
./vendor/bin/pest           # 34 tests, ~12s
./vendor/bin/phpstan        # static analysis, level 5 (with baseline)
./vendor/bin/pint --test    # check formatting; drop --test to apply
```

The test suite uses `epassport_test` and `RefreshDatabase`. Each test seeds the catalogs + demo data so assertions can rely on known fixtures (4 orgs, 30 workers with 2 expired certs, 10 equipment with 2 expired TPI, etc.).

## Subscribing to a webhook

```bash
TOKEN=...   # from /auth/login
ORG=...     # one of your organization IDs

curl -X POST http://127.0.0.1:8000/api/v1/webhooks \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"owner_organization_id\": \"$ORG\",
    \"label\": \"my-erp\",
    \"url\": \"https://my-erp.example.com/hooks/epassport\",
    \"events\": [\"scan.red\", \"scan.impersonation_flag\", \"permit.approved\", \"hazard_report.submitted\"]
  }"
```

The response includes the HMAC-SHA256 secret **once**. Verify each delivery on your end:

```js
const expected = 'sha256=' + crypto.createHmac('sha256', SECRET).update(rawBody).digest('hex');
if (expected !== req.headers['x-epassport-signature']) abort();
```

Headers sent on every delivery:

| Header                          | Purpose                                  |
|---------------------------------|------------------------------------------|
| `X-ePassport-Event`             | event name (e.g. `scan.red`)             |
| `X-ePassport-Event-Id`          | UUID — dedupe on consumer side           |
| `X-ePassport-Signature`         | `sha256=<hex>` of the raw body            |
| `X-ePassport-Delivery-Attempt`  | 1..5; we retry with exponential backoff  |

## Issuing API keys for ERP integrations

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/api-keys \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "sap-erp-prod",
    "abilities": ["workers.read", "equipment.read", "permits.read", "scans.read"],
    "expires_at": "2027-12-31T23:59:59Z"
  }'
```

The plaintext token is returned **once**. Send it as `Authorization: Bearer <token>` on subsequent requests. Available abilities: `GET /api/v1/auth/api-keys/abilities`.

## Deployment

See [DEPLOYMENT.md](./DEPLOYMENT.md) for staging + production setup on Hetzner Cloud (or DigitalOcean) with Laravel Forge.
