# Development PHP-FPM runtime: Bedrock + WordPress + Sage/Acorn dependencies.
# The application code is bind-mounted in docker-compose.yml; this image
# only provides the PHP runtime, extensions, Composer, and WP-CLI.
FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        git \
        unzip \
        zip \
        libzip-dev \
        icu-dev \
        mariadb-client \
        netcat-openbsd \
    && docker-php-ext-install -j"$(nproc)" \
        mysqli \
        pdo_mysql \
        intl \
        zip \
        opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl -sSL -o /usr/local/bin/wp \
        https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

WORKDIR /var/www/html
