# ---------------------------------------------------------------------------
# 1. PHP dependencies
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev --no-interaction

# ---------------------------------------------------------------------------
# 2. Front-end assets
# ---------------------------------------------------------------------------
FROM node:22-bookworm-slim AS assets

# The Wayfinder Vite plugin shells out to `php artisan wayfinder:generate`
# during the build, so this Node stage needs a PHP CLI too — just to boot
# Laravel and list routes, not to run the app. It has to be PHP 8.4
# specifically: composer.lock's platform check rejects Debian bookworm's
# stock PHP 8.2, so PHP 8.4 comes from Sury's APT repo instead.
RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates curl gnupg \
    && curl -fsSL https://packages.sury.org/php/apt.gpg -o /usr/share/keyrings/sury-php.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ $(. /etc/os-release && echo "$VERSION_CODENAME") main" > /etc/apt/sources.list.d/sury-php.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends php8.4-cli php8.4-sqlite3 \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY . .

# The PHP dependencies have to be in place before the frontend build runs,
# since Wayfinder's artisan command needs the vendor autoloader.
COPY --from=vendor /app/vendor ./vendor

# storage/framework/views doesn't exist until the container boots (entrypoint
# creates it), but Wayfinder boots the framework far enough to need it now —
# config/view.php resolves it with realpath(), which returns false for a
# missing directory and makes Laravel's view compiler reject it outright.
RUN mkdir -p storage/framework/views storage/framework/sessions storage/framework/cache/data

RUN npm run build

# ---------------------------------------------------------------------------
# 3. Runtime
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:1-php8.4 AS runtime

# pdo_sqlite is what the app runs on; opcache is the biggest single win on a Pi.
# sqlite3 and curl are here for backups and the healthcheck respectively.
RUN install-php-extensions pdo_sqlite opcache intl zip \
    && apt-get update \
    && apt-get install -y --no-install-recommends curl sqlite3 \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

COPY docker/Caddyfile /etc/frankenphp/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 80

ENTRYPOINT ["entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
