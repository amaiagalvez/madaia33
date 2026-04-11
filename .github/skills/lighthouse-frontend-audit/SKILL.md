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

## Default Workflow (First Try)

### 1) Ensure app is reachable in Docker network

```bash
docker compose up -d db madaia33
```

### 2) Run Lighthouse from a Docker image with Chrome included

Use this stable command pattern:

```bash
docker run --rm --network madaia33_frontend -u $(id -u):$(id -g) -v "$PWD:/work" -w /work ghcr.io/puppeteer/puppeteer:22.15.0 sh -lc "npx -y lighthouse@11 'http://madaia33/es/avisos' --only-categories=performance,accessibility,best-practices,seo --chrome-flags='--headless --no-sandbox --disable-gpu' --output=json --output-path='/work/.docs/lighthouse-madaia33-es-avisos.json'"
```

Repeat for each affected URL and keep reports in `.docs/`.

### 3) Summarize category scores from report JSON

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

### 4) Compare before/after scores (when rerunning after a fix)

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
    - Fix: target `http://madaia33/...` (service name), not `localhost` from a separate container.

- No report files appear in workspace.
    - Cause: output path not inside mounted volume.
    - Fix: write to `/work/.docs/...` and ensure `-v "$PWD:/work"` is present.

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
