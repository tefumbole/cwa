# Catholic Women's Association Cameroon (CWACAM)

Public site for [cwacam.org](https://cwacam.org). Until **1 October 2026** the homepage is a coming-soon countdown. Admin and login routes stay reachable.

This repository is **not** BeyondTechWorld. Do not point any CWACAM env file at the BeyondTechWorld database or deploy directory.

## Local development

```bash
# Isolated MySQL on port 3308 (separate volume from Alpha Bridge / Beyond)
# Requires Docker Desktop, or Homebrew MySQL plus MYSQL_ROOT_PASSWORD=...
bash tools/setup-cwacam-db.sh

# Frontend (coming-soon on / ; admin at /admin/login)
cp tools/env/cwacam.local.env .env
npm run dev
```

Laravel (optional, same `cwacam` database):

```bash
cp laravel-app/.env.example laravel-app/.env
# generate APP_KEY, then:
# php artisan serve --port=8000
```

`BEYOND_DATA_DB_*` must use the **same** `cwacam` credentials. Never set them to `u152889834_beyondworld` or any Beyond host.

## Database isolation

| Site | Database | Port (local) |
|------|----------|----------------|
| CWACAM (this repo) | `cwacam` | 3308 |
| Beyond Enterprise | `u152889834_beyondworld` | production only |
| Alpha Bridge | `u152889834_alphabridge` / local `alphabridge` | 3307 |

- Node API refuses to start if `DB_NAME` matches `beyondworld` or `beyondtech`
- Laravel refuses to boot if `DB_DATABASE` or `BEYOND_DATA_DB_DATABASE` matches those names
- `tools/setup-cwacam-db.sh` runs **fresh migrate + seed only** — it never imports Beyond or Alpha Bridge dumps

## Production Hostinger (create later)

1. New database e.g. `u152889834_cwacam` and a user that can access **only** that database
2. New password — do not reuse the Beyond user
3. Copy `apps/api/.env.cwacam.example` on the CWACAM host and set those credentials
4. Fresh migrate/seed — do not import Beyond data

## Later deploy (do not reuse Beyond paths)

When you are ready to put this on cwacam.org:

1. Clone [tefumbole/cwa](https://github.com/tefumbole/cwa.git) to `/var/www/cwacam` (not `/var/www/beyondtechworld`)
2. New nginx vhost for `cwacam.org`
3. Laravel `.env` on the new CWACAM database only
4. Frontend build env: `tools/env/cwacam.production.env`
