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

- [x] 1. Move notice card Blade render tests from Feature to Unit without database persistence.
- [x] 2. Split private page view-only accessibility assertions into a Unit test file.
- [x] 3. Keep HTTP/Livewire integration assertions in Feature tests unchanged.
- [x] 4. Run formatting and targeted tests.
- [x] 5. Run Docker quality gate, full test suite, and Dusk tests.

## Results

- [ ] `composer quality` passed
- [x] `php artisan test --compact` passed
- [ ] Dusk tests passed (following `dusk-testing` skill)

## Verification Notes

- Focused tests passed: `tests/Unit/NoticeCardComponentTest.php`, `tests/Unit/PrivatePageAccessibilityViewTest.php`, `tests/Feature/SecondaryPagesResponsiveTest.php` (15 passed).
- Full tests passed: `php artisan test --compact` (677 passed, 2292 assertions).
- `composer quality` failed due to pre-existing style issues outside this change set:
	- `app/Http/Controllers/ProfileController.php`
	- `app/Livewire/AdminCampaignManager.php`
	- `tests/Browser/AdminCampaignTestEmailModalTest.php`
	- `tests/Browser/CookieConsentBannerTest.php`
	- `tests/Feature/AdminCampaignManagerTest.php`
- Dusk browser verification failed in this environment:
	- Direct run (`php artisan test --compact tests/Browser`) fails with sqlite table bootstrapping issues and local Chromium path errors.
	- Selenium workflow run progressed but did not produce a passing completion in this session.
