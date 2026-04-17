---
name: lighthouse-frontend-audit
description: "Use this skill after frontend changes in this project to run Lighthouse in a Docker-first workflow (after relevant Dusk tests pass), analyze results, and propose concrete improvements. Trigger on requests mentioning lighthouse, frontend audit, performance/accessibility/seo/best-practices review, or post-Dusk quality checks."
license: MIT
metadata:
    author: madaia33
---

# Lighthouse Frontend Audit Workflow

## Goal

Run Lighthouse reliably in this repository using Docker-only commands, then provide actionable improvement proposals and an optimization loop.

## When to Use

- Frontend Blade/Livewire/Tailwind pages were changed.
- Relevant Dusk tests already passed (required gate before Lighthouse).
- You need a measurable report for:
    - Performance
    - Accessibility
    - Best Practices
    - SEO

## Mandatory Preconditions

1. Confirm affected Dusk tests pass first.
2. Keep Docker-first workflow (no host `npm` / `node` execution).
3. Use non-root user context for project commands where possible.
4. For authenticated/private audits, do not rely on manual cookie injection as the default approach.

## Default Workflow (First Try)

### 1) Ensure app is reachable in Docker network

```bash
docker compose up -d db madaia33
```

### 2) Start a temporary app container with Debugbar disabled

Use this command so Lighthouse audits the UI without `phpdebugbar` overlays/noise:

```bash
docker compose run --rm -d --name lh-app \
    --user "${DC_UID:-1000}:${DC_GID:-1000}" \
    -e APP_ENV=testing \
    -e APP_DEBUG=false \
    -e DEBUGBAR_ENABLED=false \
    -e APP_URL=http://lh-app:8000 \
    -e DB_CONNECTION=sqlite \
    -e DB_DATABASE=/tmp/lighthouse.sqlite \
    -e CACHE_STORE=array \
    -e SESSION_DRIVER=file \
    -e QUEUE_CONNECTION=sync \
    madaia33 sh -lc '
set -e
rm -f /tmp/lighthouse.sqlite
php artisan migrate:fresh --seed --force >/tmp/lighthouse-migrate.log 2>&1
php artisan serve --host=0.0.0.0 --port=8000 >/tmp/lighthouse-serve.log 2>&1
'
```

If the page to audit requires seeded demo users/owners/votings from `DevSeeder`, recreate `lh-app` with `APP_ENV=local` instead of `APP_ENV=testing`:

```bash
docker rm -f lh-app >/dev/null 2>&1 || true
docker compose run --rm -d --name lh-app \
    --user "${DC_UID:-1000}:${DC_GID:-1000}" \
    -e APP_ENV=local \
    -e APP_DEBUG=false \
    -e DEBUGBAR_ENABLED=false \
    -e APP_URL=http://lh-app:8000 \
    -e DB_CONNECTION=sqlite \
    -e DB_DATABASE=/tmp/lighthouse.sqlite \
    -e CACHE_STORE=array \
    -e SESSION_DRIVER=file \
    -e QUEUE_CONNECTION=sync \
    madaia33 sh -lc '
set -e
rm -f /tmp/lighthouse.sqlite
php artisan migrate:fresh --seed --force >/tmp/lighthouse-migrate.log 2>&1
php artisan serve --host=0.0.0.0 --port=8000 >/tmp/lighthouse-serve.log 2>&1
'
```

Then verify the container is reachable before running Lighthouse:

```bash
for i in $(seq 1 60); do
    docker run --rm --network madaia33_frontend curlimages/curl:8.7.1 -fsS http://lh-app:8000/es >/dev/null 2>&1 && break
    if [ "$i" -eq 60 ]; then
        echo "lh-app unreachable" >&2
        exit 1
    fi
done
```

Stop and remove it when finished:

```bash
docker rm -f lh-app >/dev/null 2>&1 || true
```

### 3) Run Lighthouse from a Docker image with Chrome included

Use this robust command pattern (recommended):

```bash
WORKSPACE_DIR="$(pwd -P)"
docker run --rm --network madaia33_frontend \
    -u "$(id -u):$(id -g)" \
    -v "${WORKSPACE_DIR}:/work" \
    -w /work \
    ghcr.io/puppeteer/puppeteer:22.15.0 \
    sh -lc "npx -y lighthouse@11 'http://lh-app:8000/es/avisos' \
        --only-categories=performance,accessibility,best-practices,seo \
        --chrome-flags='--headless --no-sandbox --disable-gpu' \
        --output=json \
        --output-path='/work/.docs/lighthouse-madaia33-es-avisos.json' \
        --quiet"

ls -l "${WORKSPACE_DIR}/.docs/lighthouse-madaia33-es-avisos.json"
```

Repeat for each affected URL and keep reports in `.docs/`.

### 3b) Authenticated pages: use a shared Chrome session

For private/authenticated/Livewire-heavy pages, use a real browser session and run Lighthouse against that same Chrome instance. This is the default for routes like `/es/votaciones`.

Reason:

- Manual cookie/header injection can silently audit a redirect/login page instead of the target route.
- Shared-session Chrome avoids CSRF/session drift and better reflects real authenticated behavior.

Use this pattern:

```bash
WORKSPACE_DIR="$(pwd -P)"
docker run --rm --network madaia33_frontend \
        -u "$(id -u):$(id -g)" \
        -v "${WORKSPACE_DIR}:/work" \
        -w /work \
        ghcr.io/puppeteer/puppeteer:22.15.0 sh -lc '
set -e
cat >/tmp/lh-login.cjs <<"NODE"
const puppeteer = require("/home/pptruser/node_modules/puppeteer");

(async () => {
    const browser = await puppeteer.launch({
        executablePath: "/usr/bin/google-chrome",
        headless: true,
        args: ["--remote-debugging-port=9222", "--no-sandbox", "--disable-gpu", "--user-data-dir=/tmp/lh-profile"],
    });

    const page = await browser.newPage();
    await page.goto("http://lh-app:8000/es/privado", { waitUntil: "networkidle2" });
    await page.type("input[name=email]", "propietaria@email.eus");
    await page.type("input[name=password]", "password");
    await Promise.all([
        page.click("[data-test=login-button]"),
        page.waitForNavigation({ waitUntil: "networkidle2" }),
    ]);
    await page.goto("http://lh-app:8000/es/votaciones", { waitUntil: "networkidle2" });
    await page.waitForSelector("[data-page=votings]", { timeout: 10000 });
    console.log("LH_READY");
    setInterval(() => {}, 1000);
})();
NODE

node /tmp/lh-login.cjs >/tmp/lh-login.log 2>&1 &
NODE_PID=$!
i=0
while [ "$i" -lt 60 ]; do
        if grep -q "LH_READY" /tmp/lh-login.log; then break; fi
        if ! kill -0 "$NODE_PID" 2>/dev/null; then cat /tmp/lh-login.log; exit 1; fi
        i=$((i+1))
        if [ "$i" -eq 60 ]; then cat /tmp/lh-login.log; exit 1; fi
        sleep 1
done

npx -y lighthouse@11 "http://lh-app:8000/es/votaciones" \
        --port=9222 \
        --disable-storage-reset \
        --only-categories=performance,accessibility,best-practices,seo \
        --chrome-flags="--headless --no-sandbox --disable-gpu" \
        --output=json \
        --output-path="/work/.docs/lighthouse-madaia33-es-votaciones-auth.json" \
        --quiet

kill "$NODE_PID" || true
ls -l /work/.docs/lighthouse-madaia33-es-votaciones-auth.json
'
```

After each authenticated audit, confirm that `requestedUrl` and `finalUrl` both match the intended route. If `finalUrl` falls back to `/login`, `/es/privado`, or another auth gate, the audit is invalid and must be rerun.

### 4) Summarize category scores from report JSON

```bash
node - <<'NODE'
const fs = require('fs');
const files = fs.readdirSync('.docs').filter(f => f.startsWith('lighthouse-') && f.endsWith('.json')).sort();
for (const file of files) {
    const r = JSON.parse(fs.readFileSync(`.docs/${file}`, 'utf8'));
    const c = r.categories;
    const pct = (v) => Math.round((v?.score ?? 0) * 100);
    console.log(`${file}: performance=${pct(c.performance)} accessibility=${pct(c.accessibility)} best-practices=${pct(c['best-practices'])} seo=${pct(c.seo)}`);
}
NODE
```

### 5) Compare before/after scores (when rerunning after a fix)

Save improved reports with a `-v2` suffix (e.g. `lighthouse-madaia33-es-galeria-v2.json`), then run:

```bash
node - <<'NODE'
const fs = require('fs');
const pairs = fs.readdirSync('.docs')
    .filter(f => f.endsWith('-v2.json') && f.startsWith('lighthouse-'))
    .map(v2 => ({ v2, v1: v2.replace('-v2.json', '.json') }))
    .filter(p => fs.existsSync(`.docs/${p.v1}`));

const cats = ['performance', 'accessibility', 'best-practices', 'seo'];
const pct = (r, c) => Math.round((r.categories[c]?.score ?? 0) * 100);
const sign = d => (d > 0 ? '+' : '') + d;

for (const { v1, v2 } of pairs) {
    const a = JSON.parse(fs.readFileSync(`.docs/${v1}`, 'utf8'));
    const b = JSON.parse(fs.readFileSync(`.docs/${v2}`, 'utf8'));
    console.log(`\n=== ${v1.replace('lighthouse-madaia33-es-', '').replace('.json', '')} ===`);
    for (const c of cats) {
    const before = pct(a, c), after = pct(b, c);
    console.log(`  ${c.padEnd(18)} ${before} → ${after}  (${sign(after - before)})`);
    }
}
NODE
```

## Common Failures and Fixes

- Error: `SyntaxError: Unexpected token 'with'` from Lighthouse locale imports.
    - Cause: incompatible host Node runtime with newer Lighthouse.
    - Fix: run Lighthouse in Docker image with compatible Node/Chrome (`ghcr.io/puppeteer/puppeteer:22.15.0`) and pin `lighthouse@11`.

- Error: Lighthouse cannot reach URL / `ERR_CONNECTION_REFUSED`.
    - Cause: wrong hostname/port from inside Docker network.
    - Fix: when using the temporary audit container, target `http://lh-app:8000/...` and keep `--network madaia33_frontend` in the Lighthouse container.

- Authenticated page report finishes on `/login`, `/es/privado`, or another unexpected URL.
    - Cause: invalid session reproduction; cookie/header injection did not preserve the real authenticated state.
    - Fix: rerun with Step `3b` (shared Chrome session), then verify `requestedUrl === finalUrl`.

- A long all-in-one shell command appears to stall or returns partial output.
    - Cause: the workflow is too large for one opaque terminal step, making diagnosis difficult.
    - Fix: split execution into small verified steps: start `lh-app`, verify reachability, run each Lighthouse audit separately, then summarize results.

- Contrast failure points to `.phpdebugbar-*` selectors.
    - Cause: Debugbar rendered in audited DOM.
    - Fix: rerun using Step 2 (`APP_DEBUG=false`, `DEBUGBAR_ENABLED=false`) and only treat it as app issue if selector still points to product DOM.

- No report files appear in workspace.
    - Cause: output path not inside mounted volume or ambiguous working directory.
    - Fix: use `WORKSPACE_DIR="$(pwd -P)"`, mount `-v "${WORKSPACE_DIR}:/work"`, write to `/work/.docs/...`, and verify with `ls -l` after each run.

- HTTPS audit failure in local environment.
    - Cause: local test URL is HTTP.
    - Fix: treat this as expected local-only signal unless HTTPS is configured for local reverse proxy.

## Analysis Checklist

For each audited page, capture:

1. Category scores (Performance, Accessibility, Best Practices, SEO)
2. Core metrics (FCP, LCP, TBT, CLS)
3. Top failing audits (at least 3)
4. Whether issue is:
    - environment-only (local HTTP, source map policy)
    - code-level (render-blocking assets, lazy LCP image, heading order, missing accessible names)

## Improvement Proposal Standard

Every proposal must include:

1. Root cause hypothesis
2. Concrete code-level action
3. Expected impact (which Lighthouse category/metric should improve)
4. Validation method (which page and what to re-measure)

Prioritize proposals in this order:

1. High impact + low risk
2. High impact + medium risk
3. Medium impact + low risk

## Improvement Loop (Required)

After initial proposals, try to improve them instead of stopping at generic advice:

1. Rewrite each proposal to be implementation-ready (file/area, exact change type).
2. Remove vague wording like "optimize" without a mechanism.
3. Add measurable acceptance criteria (for example, "reduce LCP from ~6s to <4s on /es/avisos").
4. If changes are implemented, rerun Lighthouse and compare before/after scores.
5. Keep only proposals that show plausible or measured benefit.

## Output Format Recommendation

For each page:

- Scores summary
- Key issues detected
- 2 to 4 prioritized proposals

Final section:

- Cross-page quick wins
- Environment-only warnings to ignore in local context
- Suggested next Lighthouse re-run scope (only changed pages first)

## Notes for This Repository

- Run this skill after the frontend + Dusk gate.
- Store generated JSON reports in `.docs/` so they are auditable.
- Keep Docker-first and non-root execution discipline.
- If running from host shell for quick JSON parsing, avoid mutating project files unless requested.
- For authenticated front routes in this repo, prefer `APP_ENV=local` when you need the full DevSeeder dataset (owner users, active votings, delegated flows).
- For public/guest pages, `APP_ENV=testing` is usually enough and keeps the dataset smaller.
