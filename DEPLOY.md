# ePassport — MVP Deployment Guide

End-to-end steps to put the three pieces online (backend, web, mobile)
on free / near-free tiers. Order matters: provision data + storage
first, then the backend, then the web app, then build the mobile.

Stack at a glance:

| Piece | Service | Free tier? |
|---|---|---|
| Backend (Laravel) | Render — Docker web service | Free; sleeps after 15 min idle, ~30 s cold start |
| Postgres | Supabase | Free; 500 MB, never sleeps |
| Photo storage | Cloudflare R2 (S3-compatible) | Free; 10 GB + 1M ops / month |
| Web (Vite SPA) | Vercel | Free |
| Mobile Android | Sideloaded APK | Free |
| Mobile iOS | Xcode personal signing | Free for 7-day device builds (needs a Mac) |

---

## 1. Supabase — Postgres

1. https://supabase.com → New project. Pick the region closest to your users.
2. Save the database password (only shown once).
3. Project Settings → Database → **Connection string → Transaction pooler**.
   Note the host (`aws-0-xxx.pooler.supabase.com`), port `6543`, user
   `postgres.<project-ref>`, db `postgres`.

That's all the SQL setup. The Laravel container will run `php artisan
migrate --force` on boot and create every table from `database/migrations/`.

## 2. Cloudflare R2 — photo storage

1. https://dash.cloudflare.com → R2 → Create bucket: `epassport-hazard-photos`.
2. Manage R2 API tokens → **Create API token**, scope to the bucket,
   permission **Object Read & Write**. Note the **Access Key ID** and
   **Secret Access Key** (Secret is shown once).
3. Note the **S3 endpoint URL** shown on the bucket page —
   `https://<account-id>.r2.cloudflarestorage.com`.

## 3. Backend — Render

1. https://render.com → New → Web Service → connect this GitHub repo.
2. Settings:
   - Root Directory: `backend`
   - Runtime: **Docker** (Render detects the Dockerfile)
   - Plan: Free
   - Health Check Path: `/up`
3. **Environment** tab — paste each line from
   `backend/.env.production.example`, filling in the blanks:
   - `APP_KEY` — run `php artisan key:generate --show` locally and paste
   - `DB_*` — from step 1
   - `AWS_*` + `AWS_ENDPOINT` — from step 2
   - `APP_URL` — set to `https://<your-service>.onrender.com` (you'll see
     this after the first deploy; redeploy after editing if needed)
   - `SANCTUM_STATEFUL_DOMAINS` — set after step 4 (or leave for now)
4. Click **Create Web Service**. First build ~5 min. Once green, hit
   `/up` — should return `200`.
5. Seed the demo data (one-time): Render dashboard → Shell tab →
   ```
   php artisan db:seed --class=DemoDataSeeder --force
   php artisan db:seed --class=TestScanWorkersSeeder --force
   ```

### Tokens for the mobile / web clients

```
php artisan tinker
$user = App\Models\User::where('email', 'khalid.maincon@epassport.local')->first();
echo $user->createToken('mobile')->plainTextToken;
```

## 4. Web frontend — Vercel

1. https://vercel.com → New Project → import the same repo.
2. Settings:
   - Root Directory: `web`
   - Framework Preset: **Vite** (auto-detected)
   - Build Command: `npm run build` (default)
   - Output Directory: `dist` (default)
3. **Environment Variables**:
   - `VITE_API_BASE_URL` = `https://<your-render-service>.onrender.com`
4. Deploy. After it goes live, copy the `*.vercel.app` URL and:
   - go back to Render → Environment →
     `SANCTUM_STATEFUL_DOMAINS=<your-web>.vercel.app` → save (triggers
     redeploy).

## 5. Mobile — Android sideload (Linux/Windows/Mac)

```bash
cd mobile
flutter build apk --release \
  --dart-define=API_BASE_URL=https://<your-render-service>.onrender.com
```

The APK lands at `mobile/build/app/outputs/flutter-apk/app-release.apk`.
Transfer to your phone (USB, Drive, email) and install. You'll need to
allow "Install unknown apps" once.

## 6. Mobile — iOS (Mac required)

On the Mac, after `git pull`:

```bash
cd mobile
flutter pub get
cd ios && pod install && cd ..

# Open in Xcode
open ios/Runner.xcworkspace
```

In Xcode:

1. Select **Runner** target → **Signing & Capabilities** → check
   "Automatically manage signing" → pick your free personal Apple ID team.
   You'll get a unique bundle id like `com.<your-name>.epassportMobile`.
2. Plug in the iPhone, select it as the run destination.
3. Run with:

```bash
flutter run --release \
  --dart-define=API_BASE_URL=https://<your-render-service>.onrender.com
```

Free personal signing expires after 7 days — just `flutter run` again
to renew.

---

## Common gotchas

- **Render free tier sleeps**. First request after idle takes 20–40 s. The
  mobile/web clients should be fine on retries; long-poll users may notice.
- **Supabase pooler**. Use port `6543` (`pgbouncer`). The direct port
  `5432` works too but counts against the smaller connection limit.
- **R2 + signed URLs**. The Laravel signed-photo route stays behind the
  app server in this setup. That's intentional for MVP — swap to native
  R2 presigned URLs later if you outgrow it.
- **Sanctum SPA auth**. The web frontend talks to a different host than
  the backend, so cookie-based auth needs `SANCTUM_STATEFUL_DOMAINS` set
  to the exact Vercel hostname (no scheme, no path).
- **Mobile build flag**. If you forget `--dart-define=API_BASE_URL=…` the
  APK ships with the emulator address `http://10.0.2.2:8000` baked in
  and won't connect from a real device.

## Quick verification checklist

After deploy, hit these in order — each should succeed before moving on:

- [ ] `GET https://<render>.onrender.com/up` → `200`
- [ ] `GET https://<render>.onrender.com/api/v1/health` → JSON, no error
- [ ] Web app loads at `https://<web>.vercel.app`, login works
- [ ] Mobile APK on phone shows the dashboard after login
- [ ] Manual entry of `9000000001` → green / valid result
- [ ] Manual entry of `9000000002` → red / "Permit expired on …"
- [ ] Manual entry of `9000000003` → red / "Employee has no permit."
- [ ] Submit an anonymous hazard with a photo → check Cloudflare R2
      bucket has the new object under `hazard-photos/YYYY/MM/`
