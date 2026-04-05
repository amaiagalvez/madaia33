---
name: dusk-test
description: "Use this skill when running, fixing, or debugging Laravel Dusk Browser tests in this project (especially in Docker). Trigger when Browser tests fail with errors like missing chromedriver path, missing chromium binary, or net::ERR_CONNECTION_REFUSED. Covers reproducible setup: install browser, install matching ChromeDriver, prepare database state, start app server in-container, run only affected Browser tests, and clean up background processes."
license: MIT
metadata:
  author: madaia33
---

# Dusk Test Workflow

## Goal

Run Browser tests reliably in this repository without repeating setup failures.

## When to Use

- You run tests under `tests/Browser/`.
- Dusk fails with one of these errors:
  - `Invalid path to Chromedriver`
  - `no chrome binary at /usr/bin/chromium`
  - `unknown error: net::ERR_CONNECTION_REFUSED`
- You need fast verification of only changed Browser tests.

## One-Shot Reliable Command (Docker)

Use this in this project when running Browser tests inside Docker:

```bash
docker compose run --rm --user root madaia33 sh -lc '
set -e
apt-get update -qq
apt-get install -y -qq chromium >/dev/null
php artisan dusk:chrome-driver --detect --no-interaction
php artisan migrate:fresh --seed --force >/tmp/migrate-seed.log 2>&1
export APP_URL=http://127.0.0.1:8000
php artisan serve --host=127.0.0.1 --port=8000 >/tmp/laravel-serve.log 2>&1 &
SERVER_PID=$!
for i in $(seq 1 30); do
  if curl -fsS http://127.0.0.1:8000 >/dev/null 2>&1; then break; fi
  sleep 1
done
php artisan test --compact tests/Browser/FooterLinksTest.php tests/Browser/LanguageSwitcherTest.php
STATUS=$?
kill $SERVER_PID || true
wait $SERVER_PID 2>/dev/null || true
exit $STATUS
'
```

Replace test file paths with only the Browser files you changed.

## Why Each Step Exists

1. `apt-get install chromium`
   - Prevents missing browser binary errors.
2. `php artisan dusk:chrome-driver --detect`
   - Prevents chromedriver version/path mismatch.
3. `php artisan migrate:fresh --seed --force`
   - Ensures legal pages/settings/data expected by UI tests exist.
4. `APP_URL=http://127.0.0.1:8000` + `php artisan serve`
   - Prevents `ERR_CONNECTION_REFUSED` by making app reachable from Chromium in the same container.
5. Run only affected Browser tests
   - Keeps verification fast and focused.
6. Explicit `kill`/`wait`
   - Prevents orphaned background server processes.

## Minimal Debug Checklist

- Browser binary exists:

```bash
command -v chromium
```

- Driver installed and detected:

```bash
php artisan dusk:chrome-driver --detect --no-interaction
```

- App responds inside container:

```bash
curl -fsS http://127.0.0.1:8000 >/dev/null && echo ok
```

## Test Scope Rule

- Start with only changed Browser files.
- Expand to more Browser tests only if a changed area has cross-page side effects.

## Notes for This Repository

- Use `php artisan test --compact ...` for Browser file runs.
- If Browser tests depend on translations/settings/legal content, always seed before running.
- Temporary `Xdebug` connection warnings during CLI test runs are non-blocking unless tests actually fail.