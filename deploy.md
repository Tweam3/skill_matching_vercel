# Vercel Deployment

This directory contains the Laravel app configured for Vercel.

## Prerequisites

1. **Vercel account** (free tier works)
2. **External PostgreSQL** (Vercel Postgres, Neon, Supabase, or Render free tier)
3. **S3-compatible storage** (AWS S3, Cloudflare R2, etc.) for profile pictures
4. **MAILTRAP** or SMTP service for emails

## Step 1: Connect to Vercel

Push this folder to a GitHub repository, then import it in Vercel:

```
vercel login
vercel
```

Or connect via the Vercel dashboard by linking your GitHub repo.

## Step 2: Add the PHP runtime

Vercel does not natively support PHP. In your Vercel project settings:

1. Go to **Settings → General**
2. Under **Build & Development Settings**, click **Edit**
3. Add the community PHP builder: `vercel-php@latest` (or install via the Vercel integrations marketplace)

Alternatively, configure a custom build image that includes PHP 8.1+.

## Step 3: Set Environment Variables

In the Vercel dashboard (**Settings → Environment Variables**), add:

| Variable | Example Value |
|---|---|
| `APP_NAME` | Skill Matching |
| `APP_ENV` | production |
| `APP_DEBUG` | false |
| `APP_URL` | https://your-app.vercel.app |
| `APP_KEY` | base64:... (run `php artisan key:generate --show`) |
| `DB_CONNECTION` | pgsql |
| `DB_HOST` | your-db-host |
| `DB_PORT` | 5432 |
| `DB_DATABASE` | your_db |
| `DB_USERNAME` | your_user |
| `DB_PASSWORD` | your_password |
| `SESSION_DRIVER` | database |
| `CACHE_DRIVER` | database |
| `FILESYSTEM_DISK` | s3 |
| `AWS_ACCESS_KEY_ID` | your_s3_key |
| `AWS_SECRET_ACCESS_KEY` | your_s3_secret |
| `AWS_DEFAULT_REGION` | us-east-1 |
| `AWS_BUCKET` | your_bucket |
| `MAIL_MAILER` | smtp |
| `MAIL_HOST` | smtp.mailtrap.io |
| `MAIL_PORT` | 2525 |
| `MAIL_USERNAME` | your_mailtrap_user |
| `MAIL_PASSWORD` | your_mailtrap_pass |
| `LOG_CHANNEL` | stderr |

## Step 4: Run Migrations

Vercel serverless functions cannot run long-lived processes. Run migrations via:

```bash
vercel php artisan migrate --force
```

Or add a deployment hook in Vercel that calls `php artisan migrate --force`.

## Step 5: Build Assets

The `vercel.json` build command runs `npm run build` to compile Vite assets into `public/build/`.

## Known Limitations (Vercel Free Tier)

- **10s function timeout** per request — large matching/recommender operations may time out
- **Read-only filesystem** — no local file uploads, sessions, or cache files (use S3 + database)
- **No background workers** — queues run sync only; no scheduled jobs without Vercel Cron
- **PHP via community builder** — `vercel-php` is unofficial; test thoroughly before production

## Recommended Alternative

If you need native PHP + persistent storage + queues, use **Render** (already configured via `render.yaml` in the original project), **Railway**, or a VPS instead of Vercel.
