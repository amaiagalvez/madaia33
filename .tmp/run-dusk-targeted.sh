#!/usr/bin/env sh
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
  if curl -fsS http://127.0.0.1:8000 >/dev/null 2>&1; then
    break
  fi
  sleep 1
done
php artisan test --compact \
  tests/Browser/AdminImagesTest.php \
  tests/Browser/AdminMessagesTest.php \
  tests/Browser/AdminNoticesTest.php \
  tests/Browser/FocusManagementTest.php \
  tests/Browser/ImageGalleryResponsiveTest.php \
  tests/Browser/PrivatePageTest.php \
  tests/Browser/PublicSiteResponsiveTest.php
STATUS=$?
kill $SERVER_PID || true
wait $SERVER_PID 2>/dev/null || true
exit $STATUS
