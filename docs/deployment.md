# Production Deployment and Launch Runbook

## Purpose

This document defines the production deployment, operations, backup, launch
validation, and administrator handover process for Coast & Cay.

The application is deployed from the `develop` branch.

## Production architecture

Production requires:

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

## Application location

Expected application path:

```text
/var/www/carribean
```

Nginx document root:

```text
/var/www/carribean/public
```

## Required production configuration

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

Keep these values outside source control:

* `APP_KEY`
* Database credentials
* SMTP credentials
* Stripe secret key
* Stripe webhook signing secret
* Administrator credentials
* Hosting credentials
* Backup credentials

Never commit the production `.env` file.

## First deployment

Create the application directory:

```bash
sudo mkdir -p /var/www/carribean
sudo chown "$USER":www-data /var/www/carribean
```

Clone the delivery branch:

```bash
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

Generate the application key only during the first deployment:

```bash
php artisan key:generate --force
```

Do not replace `APP_KEY` after production data has been encrypted.

Apply production-safe migrations:

```bash
php artisan migrate --force
```

Never use `migrate:fresh`, `migrate:reset`, `db:wipe`, or automated destructive
rollbacks in production.

Create the public storage link:

```bash
test -L public/storage || php artisan storage:link
```

Optimize the application:

```bash
php artisan optimize
```

## Directory permissions

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

Install the repository configuration:

```bash
sudo cp \
    deploy/nginx/carribean.conf \
    /etc/nginx/sites-available/carribean
```

Update the domain and application path before enabling it:

```bash
sudo ln -sfn \
    /etc/nginx/sites-available/carribean \
    /etc/nginx/sites-enabled/carribean

sudo nginx -t
sudo systemctl reload nginx
```

## HTTPS

After DNS points to the server:

```bash
sudo certbot \
    --nginx \
    -d restaurant.example.com \
    -d www.restaurant.example.com \
    --redirect
```

Validate renewal:

```bash
sudo systemctl status certbot.timer --no-pager
sudo certbot renew --dry-run
```

## Queue worker

Install the Supervisor configuration:

```bash
sudo cp \
    deploy/supervisor/carribean-worker.conf \
    /etc/supervisor/conf.d/carribean-worker.conf

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start "carribean-worker:*"
```

Validate:

```bash
sudo supervisorctl status "carribean-worker:*"
php artisan queue:failed
```

Do not blindly retry payment-related jobs. Review the failure and verify the
operation is idempotent first.

## Scheduler

Create the scheduler cron entry:

```bash
sudo tee /etc/cron.d/carribean-scheduler > /dev/null <<'CRON'
* * * * * www-data cd /var/www/carribean && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
CRON
```

Enable it:

```bash
sudo chmod 644 /etc/cron.d/carribean-scheduler
sudo systemctl restart cron
php artisan schedule:list
```

The `orders:complete-fulfilled` command must appear as an hourly command.

## Routine deployment

Before deploying:

* Confirm GitHub Actions is green.
* Confirm the latest database backup completed.
* Confirm the production working tree is clean.
* Review pending migrations.
* Confirm Stripe and SMTP services are available.

Deploy:

```bash
cd /var/www/carribean

APP_PATH=/var/www/carribean \
DEPLOY_BRANCH=develop \
HEALTH_URL=https://restaurant.example.com/up \
./deploy/scripts/deploy.sh
```

## Database backups

Use provider-managed automated backups when available.

Before a production migration:

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

Validate the newest archive:

```bash
latest_database_backup="$(
    find "$HOME/backups/carribean" \
        -maxdepth 1 \
        -type f \
        -name 'database-*.sql.gz' \
        -printf '%T@ %p\n' \
        | sort -nr \
        | head -n 1 \
        | cut -d' ' -f2-
)"

test -n "$latest_database_backup"
gzip -t "$latest_database_backup"
```

Back up uploaded files:

```bash
tar \
    -C /var/www/carribean/storage/app \
    -czf "$HOME/backups/carribean/uploads-$(date +%Y%m%d-%H%M%S).tar.gz" \
    public
```

A backup is not verified until it has been restored into a temporary,
non-production database.

## Stripe configuration

Production webhook endpoint:

```text
https://restaurant.example.com/webhooks/stripe
```

Configure the endpoint-specific signing secret:

```dotenv
STRIPE_WEBHOOK_SECRET=
```

Verify:

* Valid signatures are accepted.
* Invalid signatures are rejected.
* Successful Checkout Sessions mark the payment paid.
* Asynchronous payment events are processed.
* Refund events update the local payment state.
* Duplicate webhook events are harmless.
* The browser success page alone cannot mark an order paid.
* Payment amount and currency are validated.

## Transactional mail

Confirm:

* Production sender address
* Production sender name
* Contact-inquiry recipient
* Verified sending domain
* SPF
* DKIM
* DMARC where supported

Test:

* Order received
* Payment confirmed
* Order confirmed
* Order rejected
* Ready for pickup
* Out for delivery
* Delivered
* Cancelled
* Contact acknowledgement
* Administrator order notification
* Administrator contact-inquiry notification

## Production smoke test

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
sudo systemctl status nginx --no-pager
sudo systemctl status php8.4-fpm --no-pager
sudo systemctl status cron --no-pager
sudo supervisorctl status "carribean-worker:*"
```

## Manual launch acceptance

Verify:

* Homepage loads through HTTPS.
* HTTP redirects to HTTPS.
* Public and uploaded images load.
* Menu categories and items display.
* Item options can be selected.
* Cart totals are recalculated by the server.
* Coupons, tax, delivery ZIP codes, and delivery minimums work.
* Guest cash checkout succeeds.
* Registered checkout succeeds.
* Stripe Checkout succeeds.
* Stripe webhook marks payment paid.
* Duplicate webhook delivery is harmless.
* Customer receives order notifications.
* Administrator receives new-order notifications.
* Administrator can update valid order statuses.
* Customer sees the updated order status.
* Pickup and delivery lifecycles work.
* Contact inquiry sends its acknowledgement and administrator notification.
* Online ordering can be disabled from Filament.
* Disabled online ordering blocks checkout.
* The scheduler completes eligible fulfilled orders.
* Queue jobs are processed.
* Database and uploaded-file backups succeed.
* Backup restoration has been tested.

## Rollback policy

Do not automatically execute destructive database rollbacks.

When an application rollback is needed:

1. Create a revert commit on `develop`.
2. Run the complete CI pipeline.
3. Push the revert commit.
4. Deploy the revert commit normally.
5. Use `migrate:rollback` only with an approved migration-specific recovery plan.

## Administrator handover

Training must cover:

* Logging in to Filament
* Editing restaurant identity and contact information
* Managing opening hours
* Managing menu categories and items
* Managing item options and prices
* Marking menu items unavailable
* Enabling or disabling online ordering
* Managing coupons
* Reviewing and updating orders
* Recording collected cash payments
* Resending customer notifications
* Publishing pages, FAQs, and blog posts
* Uploading gallery images
* Reviewing contact inquiries
* Checking failed queue jobs
* Contacting technical support

## Definition of launch completion

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
