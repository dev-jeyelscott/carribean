#!/usr/bin/env bash

set -Eeuo pipefail

APP_PATH="${APP_PATH:-/var/www/carribean}"
DEPLOY_BRANCH="${DEPLOY_BRANCH:-develop}"
HEALTH_URL="${HEALTH_URL:-https://restaurant.example.com/up}"

PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NPM_BIN="${NPM_BIN:-npm}"

maintenance_enabled=0

# Bring the application back online when a deployment command fails.
restore_application() {
    if [[ "${maintenance_enabled}" -eq 1 ]]; then
        "${PHP_BIN}" artisan up || true
    fi
}

trap restore_application EXIT

cd "${APP_PATH}"

if [[ ! -f artisan ]]; then
    echo "Laravel artisan file was not found in ${APP_PATH}."
    exit 1
fi

if [[ ! -f .env ]]; then
    echo "Production .env file is missing."
    exit 1
fi

current_branch="$(git branch --show-current)"

if [[ "${current_branch}" != "${DEPLOY_BRANCH}" ]]; then
    echo "Expected branch ${DEPLOY_BRANCH}, found ${current_branch}."
    exit 1
fi

if [[ -n "$(git status --porcelain)" ]]; then
    echo "Production working tree contains uncommitted changes."
    exit 1
fi

echo "Fetching ${DEPLOY_BRANCH}..."
git fetch origin "${DEPLOY_BRANCH}"

echo "Applying the latest fast-forward deployment..."
git pull --ff-only origin "${DEPLOY_BRANCH}"

echo "Installing production PHP dependencies..."
"${COMPOSER_BIN}" install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

echo "Installing frontend dependencies..."
"${NPM_BIN}" ci

echo "Building production frontend assets..."
"${NPM_BIN}" run build

echo "Enabling Laravel maintenance mode..."
"${PHP_BIN}" artisan down --retry=60
maintenance_enabled=1

echo "Applying production-safe database migrations..."
"${PHP_BIN}" artisan migrate --force

if [[ ! -L public/storage ]]; then
    echo "Creating the public storage link..."
    "${PHP_BIN}" artisan storage:link
fi

echo "Optimizing Laravel configuration, events, routes, and views..."
"${PHP_BIN}" artisan optimize

echo "Gracefully reloading long-running Laravel services..."
"${PHP_BIN}" artisan reload

echo "Disabling Laravel maintenance mode..."
"${PHP_BIN}" artisan up
maintenance_enabled=0

trap - EXIT

echo "Checking the Laravel health endpoint..."
curl \
    --fail \
    --silent \
    --show-error \
    --max-time 15 \
    "${HEALTH_URL}" \
    > /dev/null

echo "Deployment completed successfully."
