FROM php:8.2-fpm-alpine

# Install PostgreSQL extension only
RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

WORKDIR /var/www/html

CMD ["php-fpm"]
