# Repo Notes

## Docker Compose

- docker-compose.yml should not include a top-level version key with modern docker compose.
- Avoid setting XDEBUG_SESSION in container environment; with XDEBUG_START_WITH_REQUEST=trigger it forces CLI debug attempts on every command.
- Use XDEBUG_CLIENT_PORT=9003 for current VS Code/Xdebug defaults.
- Dusk inside docker compose run --rm containers requires provisioning in the same run: install chromium, run php artisan migrate:fresh --seed --force, and start php artisan serve (set APP_URL=http://127.0.0.1:8000) before running Browser tests.
- Main app service name is madaia33 (not basics3) for docker compose run --rm ... commands.

## Shared Domain Constants

- Keep shared domain constants such as community location codes in a single shared class, not duplicated across Livewire classes, view-based components, or tests.
- In this repo the single source of truth is App\\CommunityLocations.

## Execution Preferences

- User preference: never run commands as root.
- Use host user context or Docker container user `application` for project commands.
