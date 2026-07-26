# Coast & Cay

Coast & Cay is a Laravel-based Caribbean restaurant website and online-ordering application.

The current brand name and content are development placeholders. Restaurant identity, imagery, menu information, business details, and legal content will be replaced when the client provides the approved production materials.

## Technology

- Laravel 13
- PHP 8.4
- Livewire 4
- Flux UI
- Alpine.js 3
- Tailwind CSS 4
- Vite 8
- GSAP
- Laravel Fortify
- Filament 5
- Pest
- Larastan
- Laravel Pint
- Laravel Sail
- MySQL
- Mailpit

## Branch workflow

Active development happens on:

```text
develop
```

Do not create a branch for every implementation phase unless the repository workflow is changed explicitly.

### Requirements

- Windows with WSL2
- Docker Desktop with WSL integration enabled
- Git
- PHP 8.4 and Composer 2 for the initial Sail bootstrap

### Initial setup

```bash
git clone https://github.com/dev-jeyelscott/carribean.git
cd carribean

git switch develop

composer install --no-interaction --prefer-dist
cp .env.example .env

./vendor/bin/sail up -d --build
./vendor/bin/sail artisan key:generate --force
./vendor/bin/sail npm ci
```

Before seeding, configure these local `.env` values:

```dotenv
ADMIN_USER_NAME="Local Administrator"
ADMIN_USER_EMAIL=admin@coastandcay.test
ADMIN_USER_PASSWORD="replace-with-a-local-password"
```

Then run:

```bash
./vendor/bin/sail artisan migrate --seed
test -L public/storage || ./vendor/bin/sail artisan storage:link
```

### Local URLs

```txt
Application: http://localhost
Administration: http://localhost/admin
Health check: http://localhost/up
Mailpit: http://localhost:8025
```

### Daily development

Start the containers:

```bash
./vendor/bin/sail up -d
```

Start Vite:

```bash
./vendor/bin/sail npm run dev
```

Stop the containers:

```bash
./vendor/bin/sail stop
```

### Quality checks

Run the complete local quality pipeline:

```bash
./vendor/bin/sail composer validate --strict
./vendor/bin/sail composer ci:check
```

The pipeline runs:

1. Laravel Pint formatting validation
2. Larastan static analysis
3. Pest test suite
4. Vite production build

### Database commands

Apply pending migrations:

```bash
./vendor/bin/sail artisan migrate
```

View migration status:

```bash
./vendor/bin/sail artisan migrate:status
```

Seed configured development data:

```bash
./vendor/bin/sail artisan db:seed
```

Never use `migrate:fresh`, `db:wipe`, or destructive database commands against shared or production databases.

Documentation

- `docs/scope.md`
- `docs/development.md`
- `docs/deployment.md`
