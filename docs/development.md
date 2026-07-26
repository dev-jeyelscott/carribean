# Development Guide

## Supported local environment

Development uses:

- Windows Subsystem for Linux 2
- Docker Desktop with WSL integration
- Laravel Sail
- MySQL
- Mailpit

Run repository commands from the WSL filesystem, not from a mounted Windows path.

Recommended location:

```text
~/projects/carribean

Avoid:

```txt
/mnt/c/...
```

First-time setup

```bash
cd ~/projects/carribean

git fetch origin --prune
git switch develop
git pull --ff-only origin develop

composer install --no-interaction --prefer-dist
cp .env.example .env
```

Configure local administrator values in .env:

```dotenv
ADMIN_USER_NAME="Local Administrator"
ADMIN_USER_EMAIL=admin@coastandcay.test
ADMIN_USER_PASSWORD="replace-with-a-local-password"
```

Start the application:

```bash
./vendor/bin/sail up -d --build
./vendor/bin/sail artisan key:generate --force
./vendor/bin/sail npm ci
./vendor/bin/sail artisan migrate --seed
```

Create the storage link when needed:

```bash
test -L public/storage || ./vendor/bin/sail artisan storage:link
```

### Daily commands

```bash
./vendor/bin/sail up -d
./vendor/bin/sail npm run dev
```

Application URL:

```txt
http://localhost
```

Mailpit URL:

```txt
http://localhost:8025
```

### Laravel commands

```bash
./vendor/bin/sail artisan about
./vendor/bin/sail artisan route:list
./vendor/bin/sail artisan migrate:status
./vendor/bin/sail artisan test
./vendor/bin/sail artisan queue:work
```

### Quality commands

```bash
./vendor/bin/sail composer validate --strict
./vendor/bin/sail composer lint:check
./vendor/bin/sail composer types:check
./vendor/bin/sail artisan test
./vendor/bin/sail npm run build
```

Run all gates:

```bash
./vendor/bin/sail composer ci:check
```

### Database safety

Safe commands:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan migrate:status
./vendor/bin/sail artisan db:seed
```

Destructive commands must never run against shared or production databases:

```txt
migrate:fresh
migrate:reset
db:wipe
```

### Container troubleshooting

View running services:

```bash
docker compose ps
```

View application logs:

```bash
./vendor/bin/sail logs laravel.test
```

View MySQL logs:

```bash
./vendor/bin/sail logs mysql
```

Rebuild after Compose or runtime changes:

```bash
./vendor/bin/sail down
./vendor/bin/sail build --no-cache
./vendor/bin/sail up -d
```

Do not use docker compose down -v unless local database-volume deletion is intentional.
