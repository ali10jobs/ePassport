# Examples

## Postman collection

`postman/ePassport-Demo.postman_collection.json` — curated 18-step walk-through of the demo: login → workers list → e-Passport view → scan green → scan red → impersonation flag → permit hard-block → anonymous hazard → public status → dashboards → webhook subscription → API key issuance.

Import into Postman, set the `base_url` and `email`/`password` variables, and run requests in order. The Login step auto-saves the bearer to `{{token}}`.

For a fully-generated reference (every endpoint, not just demo path), use the Scribe-generated collection at `storage/app/private/scribe/collection.json`.

## Webhook receivers

Three reference implementations of HMAC-SHA256 verification. Each:

1. Reads the raw body (NOT a re-serialised JSON — the signature is computed on the wire bytes)
2. Computes `sha256=<hex>` HMAC with the per-subscription secret
3. Constant-time compares against `X-ePassport-Signature`
4. Dedupes by `X-ePassport-Event-Id`
5. Handles the event

| File | Stack |
|---|---|
| `integrations/verify-webhook.js` | Node + Express |
| `integrations/verify-webhook.py` | Python + FastAPI |
| `integrations/verify-webhook.php` | Plain PHP |
