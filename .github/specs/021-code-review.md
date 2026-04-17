# 021 — Code Review: tests/Feature to tests/Unit migration (DB-free cases)

**Date**: 2026-04-17
**Scope**: tests/Feature and tests/Unit

## Issues Found

### 🔴 Critical

- [ ] None

### 🟡 Important

- [ ] Render-only Blade tests were kept in Feature and incurred unnecessary database setup — tests/Feature/NoticeCardComponentTest.php
- [ ] A view-only assertion block was mixed into a Feature HTTP test file — tests/Feature/SecondaryPagesResponsiveTest.php

### 🟢 Minor

- [ ] Feature suite carries avoidable runtime cost when DB-free assertions are not separated into Unit tests — tests/Pest.php

## Change Status

- [ ] 1. Move notice card Blade render tests from Feature to Unit without database persistence.
- [ ] 2. Split private page view-only accessibility assertions into a Unit test file.
- [ ] 3. Keep HTTP/Livewire integration assertions in Feature tests unchanged.
- [ ] 4. Run formatting and targeted tests.
- [ ] 5. Run Docker quality gate, full test suite, and Dusk tests.

## Results

- [ ] `composer quality` passed
- [ ] `php artisan test --compact` passed
- [ ] Dusk tests passed (following `dusk-testing` skill)
