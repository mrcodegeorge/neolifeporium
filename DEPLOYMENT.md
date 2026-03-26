# Deployment Guide

## cPanel deployment

1. Create a MySQL database and user in cPanel.
2. Upload the project into your application directory.
3. Point the domain document root to `public/`.
4. Run:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Environment configuration

Set at minimum:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=neolifeporium
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## Queue worker setup

Run a persistent worker:

```bash
php artisan queue:work --queue=default --sleep=3 --tries=3 --max-time=3600
```

In cPanel, use Cron Jobs or a process manager if available.

## Storage

User uploads, vendor documents, and product media should be stored on the `public` disk or a cloud object store. Always run:

```bash
php artisan storage:link
```

## Production hardening checklist

- Configure HTTPS and secure cookies
- Add Paystack, MTN MoMo, OpenWeather, and SMS credentials
- Configure mail transport
- Enable Redis
- Set up scheduled tasks with `php artisan schedule:run`
