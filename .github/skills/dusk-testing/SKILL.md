---
name: dusk-test
description: "Use this skill when running, fixing, or debugging Laravel Dusk Browser tests in this project (especially in Docker). Trigger when Browser tests fail with errors like missing chromium binary, missing chromedriver path, or net::ERR_CONNECTION_REFUSED. Uses a selenium-first, non-root workflow with isolated sqlite, in-container app server, and stable APP_URL routing."
license: MIT
metadata:
    author: madaia33
---

# Dusk Test Workflow

## Goal

Run Browser tests reliably in this repository with a non-root Docker workflow.

## When to Use

- You run tests under `tests/Browser/`.
- Dusk fails with one of these errors:
    - `Invalid path to Chromedriver`
    - `no chrome binary at /usr/bin/chromium`
    - `unknown error: net::ERR_CONNECTION_REFUSED`
- You need reproducible Browser test execution without root installs.

## Default Workflow (First Try)

This project should run Dusk with remote Selenium first, not local Chromium installation.

### 1) Start required services

```bash
docker compose up -d db selenium mailhog
```

### 2) Run Browser tests in a named non-root container

Use this command as the default path for Browser tests:

```bash
docker compose run --rm --name dusk-app --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 sh -lc '
set -e
export APP_ENV=testing
export APP_URL=http://dusk-app:8000
export DUSK_DRIVER_URL=http://selenium:4444/wd/hub
export DB_CONNECTION=sqlite
export DB_DATABASE=/tmp/laravel_dusk.sqlite
export SESSION_DRIVER=file
export CACHE_STORE=array
export QUEUE_CONNECTION=sync
export MAIL_MAILER=smtp
export MAIL_HOST=mailhog
export MAIL_PORT=1025
export RECAPTCHA_SKIP=true
rm -f /tmp/laravel_dusk.sqlite
php artisan migrate:fresh --seed --force >/tmp/migrate-seed.log 2>&1
php artisan serve --host=0.0.0.0 --port=8000 >/tmp/laravel-serve.log 2>&1 &
SERVER_PID=$!
for i in $(seq 1 40); do
    if curl -fsS http://127.0.0.1:8000 >/dev/null 2>&1; then break; fi
    sleep 1
done
php artisan test --compact tests/Browser
STATUS=$?
kill $SERVER_PID || true
wait $SERVER_PID 2>/dev/null || true
exit $STATUS
'
```

Replace `tests/Browser` with specific Browser files when you only need a subset.

## Why This Works Better

1. No root required.
2. No Chromium package install required in the app container.
3. Selenium already provides Chromium.
4. App server and sqlite test DB live in the same test container.
5. Selenium reaches app via stable container hostname `dusk-app`.
6. Avoids the common `ERR_CONNECTION_REFUSED` caused by bad APP_URL routing.

## Remote Visible Debug (Optional)

If you want to watch the browser while tests run, use Selenium standalone Chromium + noVNC:

1. Start Selenium (and open noVNC viewer):

```bash
docker compose up -d selenium
```

2. Open browser viewer in your host:

```text
http://localhost:7900/?autoconnect=1&resize=scale
```

2. Run with headless disabled:

```bash
docker compose run --rm --name dusk-app --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 sh -lc '
set -e
export APP_ENV=testing
export APP_URL=http://dusk-app:8000
export DUSK_DRIVER_URL=http://selenium:4444/wd/hub
export DUSK_HEADLESS_DISABLED=1
export DB_CONNECTION=sqlite
export DB_DATABASE=/tmp/laravel_dusk.sqlite
export SESSION_DRIVER=file
export CACHE_STORE=array
export QUEUE_CONNECTION=sync
export MAIL_MAILER=smtp
export MAIL_HOST=mailhog
export MAIL_PORT=1025
export RECAPTCHA_SKIP=true
rm -f /tmp/laravel_dusk.sqlite
php artisan migrate:fresh --seed --force >/tmp/migrate-seed.log 2>&1
php artisan serve --host=0.0.0.0 --port=8000 >/tmp/laravel-serve.log 2>&1 &
SERVER_PID=$!
for i in $(seq 1 40); do
    if curl -fsS http://127.0.0.1:8000 >/dev/null 2>&1; then break; fi
    sleep 1
done
php artisan test --compact tests/Browser/PublicNavigationTest.php
STATUS=$?
kill $SERVER_PID || true
wait $SERVER_PID 2>/dev/null || true
exit $STATUS
'
```

## Minimal Debug Checklist

- Selenium reachable:

```bash
curl -fsS http://localhost:4444/wd/hub/status | head
```

- App server reachable inside test container:

```bash
curl -fsS http://127.0.0.1:8000 >/dev/null && echo ok
```

- Dusk URL points to named container host:

```bash
echo $APP_URL
```

- Isolated sqlite path active:

```bash
php artisan tinker --execute 'dump(config("database.default"), config("database.connections.sqlite.database"));'
```

## Common Failure and Fix

- Error: `unknown error: net::ERR_CONNECTION_REFUSED`.
    - Cause: Selenium cannot reach the app server URL.
    - Fix: use `--name dusk-app`, serve on `0.0.0.0:8000`, and set `APP_URL=http://dusk-app:8000`.

- Error: Mail assertions fail in Browser tests.
    - Cause: test run uses `MAIL_MAILER=array` while tests expect Mailhog delivery.
    - Fix: set `MAIL_MAILER=smtp`, `MAIL_HOST=mailhog`, `MAIL_PORT=1025` for Browser suite runs.

## Test Scope Rule

- Start with only changed Browser files.
- Expand to more Browser tests only if a changed area has cross-page side effects.

## Notes for This Repository

- Use `php artisan test --compact ...` for Browser file runs.
- If Browser tests depend on translations/settings/legal content, always seed before running.
- Temporary `Xdebug` connection warnings during CLI test runs are non-blocking unless tests actually fail.
- This workflow intentionally keeps Dusk on an isolated sqlite file to avoid wiping your main application database.
- For visible execution, use `selenium` service and noVNC on `http://localhost:7900` with `DUSK_DRIVER_URL=http://selenium:4444/wd/hub` and `DUSK_HEADLESS_DISABLED=1`.
