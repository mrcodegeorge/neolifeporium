# Neolifeporium

Neolifeporium is a Laravel 11 agritech web platform built as a modular monolith for Ghana and the wider African market. It combines a multi-vendor marketplace, advisory booking, farmer dashboards, knowledge publishing, and weather-aware recommendations.

## Stack

- PHP 8.2+
- Laravel 11
- MySQL
- Redis for cache, queue, and sessions
- Blade + TailwindCSS + Alpine.js
- Laravel Sanctum for API authentication

## Key modules

- Auth and RBAC: farmer, vendor, agronomist, admin, super admin
- Marketplace: categories, products, variants, reviews, wishlists
- Orders and payments: checkout, order lifecycle, Paystack and MTN MoMo-ready payment records
- Advisory: agronomist profiles and bookings
- Knowledge hub: articles with SEO fields and tag metadata
- Weather insights: OpenWeather-ready sync service and recommendation storage
- Admin analytics and moderation endpoints

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

## phpMyAdmin import (no seeding)

If you prefer importing SQL directly instead of running seeders:

1. Create an empty MySQL database in phpMyAdmin (for example `neolifeporium`).
2. Import [database/sql/neolifeporium_mysql.sql](/c:/neolifeporium/database/sql/neolifeporium_mysql.sql).
3. Set `.env` to MySQL credentials for that database.
4. Start the app with `php artisan serve`.

Demo users seeded with password `password`:

- `farmer@neolifeporium.test`
- `vendor@neolifeporium.test`
- `expert@neolifeporium.test`
- `admin@neolifeporium.test`

## API overview

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `GET /api/v1/products`
- `GET /api/v1/products/{slug}`
- `POST /api/v1/orders`
- `POST /api/v1/payments/initiate`
- `POST /api/v1/payments/{payment}/verify`
- `POST /api/v1/bookings`
- `GET /api/v1/admin/dashboard`

## Frontend pages

- `/`
- `/marketplace`
- `/marketplace/{slug}`
- `/knowledge-hub`
- `/advisory`
- `/dashboard`
- `/vendor-panel`
- `/admin-panel`

## Deployment

See [DEPLOYMENT.md](/c:/neolifeporium/DEPLOYMENT.md) for cPanel-oriented deployment, queue worker notes, and storage linking.

## Frontend build

```bash
npm install
npm run build
```

Use `npm run dev` during local development.

## Notes

- Frontend assets now build through Vite/Tailwind. Run the build step before deployment.
- Payment verification and OpenWeather sync are service-ready and environment-driven; add live credentials before production use.

## Installer recovery mode

If the app is already installed and you need to temporarily reopen `/install` for recovery:

1. Set `INSTALLER_RECOVERY_MODE=true` in `.env`
2. Run `php artisan optimize:clear`
3. Complete recovery work in the installer
4. Set `INSTALLER_RECOVERY_MODE=false` immediately after
5. Run `php artisan optimize:clear` again

This avoids deleting `storage/app/installed.lock` on production systems.
