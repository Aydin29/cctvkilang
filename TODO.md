# TODO (cctvkilang)

## Current fix: artisan serve error
- [x] Identify error: missing table `cctv_final.migrations`.
- [ ] Ensure DB connection points to correct database (check `.env` DB_DATABASE / DB_CONNECTION).
- [ ] Create migrations table in that DB: run `php artisan migrate --force` (or at least `php artisan migrate:install`).
- [ ] If you previously used a different DB name (e.g. `cctv_final` vs `laravel`), switch env and re-run.
- [ ] Confirm `php artisan serve` works and routes load.

