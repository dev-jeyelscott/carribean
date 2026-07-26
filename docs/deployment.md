# Deployment Baseline

This document captures production requirements during the foundation phase. Provider-specific deployment commands will be finalized before launch.

Laravel Sail is the local development environment. The production runtime must use an appropriate PHP web server and process-management configuration.

## Required production services

- PHP 8.4
- Supported web server
- MySQL
- HTTPS
- Persistent uploaded-file storage
- Queue worker
- Laravel scheduler
- Transactional email provider
- Automated database backups
- Application and server logs
- Stripe webhook endpoint

## Required environment behavior

```dotenv
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

Real secrets must be configured through the hosting environment and must never be committed.

Deployment sequence

```bash
git fetch origin
git switch develop
git pull --ff-only origin develop

composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

npm ci
npm run build

php artisan down --retry=60
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan queue:restart
php artisan up
```

### Required long-running processes

Queue worker:

```bash
php artisan queue:work \
    --sleep=3 \
    --tries=3 \
    --timeout=90
```

Scheduler cron entry:

```cron
* * * * * cd /path/to/carribean && php artisan schedule:run >> /dev/null 2>&1
```

### Production validation

- HTTPS is active.
- /up returns a successful response.
- Database migrations are current.
- Queue jobs process successfully.
- Scheduled commands execute.
- Uploaded files persist between deployments.
- Transactional emails are delivered.
- Database backups are verified.
- Application logs contain no critical errors.
