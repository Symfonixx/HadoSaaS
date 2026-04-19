# HadoSaaS

HadoSaaS is a Laravel 13 modular starter kit for SaaS and admin systems.  
It follows a DDD-lite architecture so features can scale without mixing transport, business orchestration, and persistence concerns.

## System Design

The project is organized by modules using `nwidart/laravel-modules`:

- `Modules/Core`: shared infrastructure (helpers, macros, console commands, contracts/services)
- `Modules/Base`: system settings, SEO, logs, admin configuration concerns
- `Modules/Cms`: pages, blogs, categories
- `Modules/User`: users, roles, permissions
- `Modules/Support`: contact forms and subscribers

Each module can follow this DDD-lite shape:

- `Http/Controllers`: request/response transport only
- `Application/*`: use-case orchestration
- `Application/*/Commands|Queries`: explicit inputs for use-cases
- `Repositories/*`: persistence and query access only
- `Models/*`: entity state/relations

See `DDD_LITE_ARCHITECTURE.md` for full conventions and migration rules.

## Core Architecture Rules

- Controllers delegate business flows to Application Services.
- Repositories do not read directly from `request()`.
- Cross-cutting concerns use contracts/services (e.g. translation, flash messaging).
- Keep routes and Blade contracts stable while refactoring internals.

## Requirements

- PHP `^8.4`
- Composer
- Node.js + npm
- MySQL/MariaDB (or a compatible database configured in `.env`)

## Main Packages

- [laravel/framework](https://laravel.com)
- [nwidart/laravel-modules](https://github.com/nWidart/laravel-modules)
- [spatie/laravel-data](https://github.com/spatie/laravel-data)
- [spatie/laravel-permission](https://github.com/spatie/laravel-permission)
- [spatie/laravel-translatable](https://github.com/spatie/laravel-translatable)
- [mcamara/laravel-localization](https://github.com/mcamara/laravel-localization)
- [intervention/image-laravel](https://github.com/Intervention/image)
- [laravel/telescope](https://laravel.com/docs/telescope)
- [laravel/pulse](https://laravel.com/docs/pulse)

## Installation

1. Install dependencies:
   - `composer install`
   - `npm install`
2. Build assets:
   - `npm run build`
3. Run installer:
   - `php artisan app:install`

### Installer Command Options

`app:install` now supports system bootstrap options:

- `--fresh`: run `migrate:fresh` instead of incremental `migrate`
- `--skip-sql`: skip importing `Modules/Core/database/db.sql`
- `--admin-name=...`
- `--admin-email=...`
- `--admin-password=...`
- `--admin-mobile=...`

Example:

```bash
php artisan app:install --fresh --admin-email=owner@example.com --admin-password=StrongPass123!
```

## Default Admin (if options are not passed)

- Email: `admin@example.com`
- Password: `12345678`

## Development Notes

- Run tests: `php artisan test`
- Run architecture checks: `composer test:architecture`
