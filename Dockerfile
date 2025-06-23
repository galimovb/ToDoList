# Stage 1: Сборка фронтенда
FROM node:20-alpine AS frontend

WORKDIR /app
COPY package*.json ./
RUN npm install

COPY assets/ ./assets
COPY webpack.config.js ./
COPY js/ ./js/
RUN npm run build

# Stage 2: PHP Symfony
FROM php:8.2-fpm-alpine

# Установка зависимостей и PHP расширений
RUN apk add --no-cache \
    bash \
    icu-dev \
    libxml2-dev \
    postgresql-dev \
    sqlite sqlite-dev \
    oniguruma-dev \
    zlib-dev \
    curl \
    git \
    && docker-php-ext-install intl pdo pdo_pgsql pdo_sqlite xml opcache  # Добавлен pdo_pgsql

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Копируем собранный фронтенд
COPY --from=frontend /app/public/build public/build/

# Создаем директории и настраиваем права
RUN mkdir -p var/cache var/log var/data \
    && chown -R www-data:www-data var \
    && chmod -R 777 var

# Установка зависимостей
RUN composer install --optimize-autoloader

USER www-data

CMD sh -c "php bin/console doctrine:migrations:migrate --no-interaction || true && php-fpm"