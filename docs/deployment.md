
# Production Deployment and Launch Runbook

## Purpose

This document defines the production deployment, operations, launch validation,
backup, and administrator handover process for Coast & Cay.

The application is deployed from the `develop` branch.

## Production Architecture

The production environment uses:

- PHP 8.4-FPM
- Nginx
- MySQL
- Database sessions
- Database cache
- Database queue
- Supervisor
- Laravel scheduler through cron
- Persistent public storage
- Transactional SMTP
- Stripe-hosted Checkout
- HTTPS
- Automated database backups

Laravel Sail and Mailpit are development-only services.

## Application Location

The expected production path is:

```text
/var/www/carribean
````

The Nginx document root must be:

```text
/var/www/carribean/public
```

## Required Production Configuration

Production must use:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://restaurant.example.com

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true

CACHE_STORE=database

QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=90

FILESYSTEM_DISK=public
```

The production queue worker uses a 60-second timeout so it remains below the
database queue's 90-second retry interval.

## Required Secrets

Configure these values outside source control:

* `APP_KEY`
* Database credentials
* SMTP credentials
* Stripe publishable key
* Stripe secret key
* Stripe webhook signing secret
* Administrator credentials
* Hosting credentials
* Backup credentials

Never commit the production `.env` file.

## First Deployment

Clone the repository:

```bash
sudo mkdir -p /var/www/carribean
sudo chown "$USER":www-data /var/www/carribean

git clone \
    --branch develop \
    https://github.com/dev-jeyelscott/carribean.git \
    /var/www/carribean

cd /var/www/carribean
```

Install PHP dependencies:

```bash
composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader
```

Install and build frontend assets:

```bash
npm ci
npm run build
```

Create the production environment:

```bash
cp deploy/.env.production.example .env
```

Replace every placeholder before continuing.

Generate the production key:

```bash
php artisan key:generate --force
```

Do not replace the application key after production data has been encrypted.

Apply migrations:

```bash
php artisan migrate --force
```

Do not use `migrate:fresh`, `db:wipe`, or destructive reset commands in
production.

Create the public storage link:

```bash
test -L public/storage || php artisan storage:link
```

Optimize the application:

```bash
php artisan optimize
```

## Directory Permissions

The application and queue worker must be able to write to:

* `storage`
* `bootstrap/cache`

Configure permissions:

```bash
sudo chown -R "$USER":www-data /var/www/carribean

sudo find storage bootstrap/cache \
    -type d \
    -exec chmod 775 {} \;

sudo find storage bootstrap/cache \
    -type f \
    -exec chmod 664 {} \;
```

## Nginx

Install the repository Nginx configuration:

```bash
sudo cp \
    deploy/nginx/carribean.conf \
    /etc/nginx/sites-available/carribean
```

Update the domain and application path before enabling it.

Enable the site:

```bash
sudo ln -sfn \
    /etc/nginx/sites-available/carribean \
    /etc/nginx/sites-enabled/carribean

sudo nginx -t
sudo systemctl reload nginx
```

## HTTPS

After DNS points to the server, obtain and install the certificate:

```bash
sudo certbot \
    --nginx \
    -d restaurant.example.com \
    -d www.restaurant.example.com \
    --redirect
```

Confirm that certificate renewal is enabled:

```bash
sudo systemctl status certbot.timer
sudo certbot renew --dry-run
```

## Queue Worker

Install the Supervisor configuration:

```bash
sudo cp \
    deploy/supervisor/carribean-worker.conf \
    /etc/supervisor/conf.d/carribean-worker.conf

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start "carribean-worker:*"
```

Validate the worker:

```bash
sudo supervisorctl status "carribean-worker:*"
```

Expected result:

```text
RUNNING
```

Review failed jobs:

```bash
php artisan queue:failed
```

Retry a confirmed safe failed job:

```bash
php artisan queue:retry <job-id>
```

Do not retry payment or notification jobs blindly without reviewing the
failure and confirming idempotency.

## Scheduler

Install the Laravel scheduler for the web-server user:

```bash
sudo bash -c "cat > /etc/cron.d/carribean-scheduler <<'CRON'
* * * * * www-data cd /var/www/carribean && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
CRON"
```

Set the correct permissions:

```bash
sudo chmod 644 /etc/cron.d/carribean-scheduler
sudo systemctl restart cron
```

Review Laravel's registered schedule:

```bash
php artisan schedule:list
```

The `orders:complete-fulfilled` command must appear as an hourly command.

## Routine Deployment

Before deploying:

1. Confirm GitHub Actions is green.
2. Confirm the production backup completed.
3. Confirm the working tree is clean.
4. Review pending migrations.
5. Confirm Stripe and mail services are healthy.

Run:

```bash
cd /var/www/carribean

APP_PATH=/var/www/carribean \
DEPLOY_BRANCH=develop \
HEALTH_URL=https://restaurant.example.com/up \
./deploy/scripts/deploy.sh
```

## Database Backups

Use provider-managed automated backups when available.

Before each production migration, create an additional manual backup:

```bash
mkdir -p "$HOME/backups/carribean"

mysqldump \
    --single-transaction \
    --quick \
    --routines \
    --triggers \
    --host=127.0.0.1 \
    --user=carribean \
    --password \
    carribean \
    | gzip \
    > "$HOME/backups/carribean/database-$(date +%Y%m%d-%H%M%S).sql.gz"
```

Verify the archive:

```bash
gzip -t "$HOME"/backups/carribean/database-*.sql.gz
```

Back up uploaded files:

```bash
tar \
    -C /var/www/carribean/storage/app \
    -czf "$HOME/backups/carribean/uploads-$(date +%Y%m%d-%H%M%S).tar.gz" \
    public
```

A backup is not considered verified until it has been restored successfully
into a temporary non-production database.

## Stripe Launch Configuration

Configure a live HTTPS webhook endpoint:

```text
https://restaurant.example.com/webhooks/stripe
```

Configure the live endpoint's unique signing secret in the production `.env`:

```dotenv
STRIPE_WEBHOOK_SECRET=
```

Confirm:

* Successful Checkout Session events are delivered.
* Asynchronous payment events are delivered.
* Refund events are delivered.
* Invalid signatures are rejected.
* Duplicate events do not duplicate payments or orders.
* The application does not mark an order paid from the success page alone.

## Transactional Mail

Confirm SMTP configuration with:

* Production sender address
* Production sender name
* Administrator inquiry address
* Verified sending domain
* SPF
* DKIM
* DMARC where supported

Test:

* Order received
* Payment confirmed
* Order confirmed
* Ready for pickup
* Out for delivery
* Delivered
* Cancelled
* Reservation acknowledgement
* Contact acknowledgement
* Administrator order notification
* Administrator inquiry notification

## Production Smoke Test

Run:

```bash
curl --fail --silent --show-error https://restaurant.example.com/up
curl --fail --silent --show-error https://restaurant.example.com/
curl --fail --silent --show-error https://restaurant.example.com/menu
curl --fail --silent --show-error https://restaurant.example.com/contact
curl --fail --silent --show-error https://restaurant.example.com/faq
curl --fail --silent --show-error https://restaurant.example.com/sitemap.xml
```

Check Laravel:

```bash
php artisan migrate:status
php artisan schedule:list
php artisan queue:failed
php artisan about
```

Check infrastructure:

```bash
sudo nginx -t
sudo systemctl status nginx
sudo systemctl status php8.4-fpm
sudo systemctl status cron
sudo supervisorctl status "carribean-worker:*"
```

## Manual Launch Acceptance

Verify:

1. Homepage loads through HTTPS.
2. HTTP redirects to HTTPS.
3. Public images load.
4. Uploaded images remain available after deployment.
5. Menu categories and items display.
6. Item options can be selected.
7. Cart totals are recalculated correctly.
8. Guest cash checkout succeeds.
9. Registered checkout succeeds.
10. Stripe Checkout succeeds.
11. Stripe webhook marks payment paid.
12. Duplicate Stripe webhook delivery is harmless.
13. Customer receives the confirmation email.
14. Administrator receives the new-order email.
15. Administrator can confirm the order.
16. Customer sees the updated status.
17. Pickup lifecycle works.
18. Delivery lifecycle works.
19. Reservation request sends both emails.
20. Contact inquiry sends both emails.
21. Online ordering can be disabled from Filament.
22. Disabled ordering blocks checkout.
23. Scheduler completes eligible fulfilled orders.
24. Queue jobs are processed.
25. Database backup is successful.
26. Backup restoration has been tested.

## Rollback Policy

Do not run destructive database rollbacks automatically.

All production migrations must remain backward-compatible during the deployment
window.

When an application rollback is required:

1. Create a revert commit locally on `develop`.
2. Run the complete CI pipeline.
3. Push the revert commit.
4. Deploy the new revert commit normally.
5. Do not execute `migrate:rollback` unless a migration-specific recovery plan
   has been reviewed and approved.

## Administrator Handover

Training must cover:

* Logging in to Filament
* Editing restaurant identity and contact information
* Managing opening hours
* Managing menu categories
* Managing menu items
* Managing item options and prices
* Marking items unavailable
* Enabling and disabling online ordering
* Managing coupons
* Reviewing new orders
* Confirming and rejecting orders
* Updating pickup statuses
* Updating delivery statuses
* Marking cash payments paid
* Resending customer notifications
* Publishing pages
* Publishing FAQs
* Publishing blog posts
* Uploading gallery images
* Reviewing reservations
* Reviewing contact inquiries
* Checking failed queue jobs
* Contacting technical support

## Definition of Launch Completion

Launch is complete when:

* CI is green.
* HTTPS is active.
* Production environment values are configured.
* Database migrations are current.
* Queue worker is running.
* Scheduler is running.
* Stripe webhook is verified.
* Transactional email is verified.
* Persistent storage is verified.
* Automated backups are enabled.
* Restore verification is complete.
* Smoke tests pass.
* Manual acceptance passes.
* Administrator training is complete.
  MARKDOWN

````

Validate the generated document:

```bash
cat docs/deployment.md
git diff --check
git diff -- docs/deployment.md
````

Then run:

```bash
./vendor/bin/sail composer ci:check
```

**Conventional commit:**

```text
docs: fix production deployment runbook formatting
```
