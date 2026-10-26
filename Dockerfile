FROM node:22-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./

FROM php:8.4-cli-alpine AS vendor

RUN apk add --no-cache git unzip curl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

FROM php:8.4-cli-alpine

RUN apk add --no-cache sqlite sqlite-dev nodejs npm \
    && docker-php-ext-install pdo pdo_sqlite

WORKDIR /app

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/node_modules ./node_modules

RUN cp .env.example .env \
    && sed -i 's/^APP_ENV=local/APP_ENV=production/; s/^APP_DEBUG=true/APP_DEBUG=false/' .env \
    && mkdir -p database \
    && touch database/database.sqlite \
    && npm run build \
    && rm -rf node_modules \
    && php artisan key:generate --force \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache database

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD wget -q -O /dev/null http://127.0.0.1:8080/up || exit 1

USER www-data

CMD ["sh", "-c", "php artisan migrate --force && php artisan serve --host 0.0.0.0 --port 8080"]
