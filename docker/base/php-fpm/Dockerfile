FROM php:8.4-fpm-bookworm

ARG DEBIAN_FRONTEND=noninteractive
ARG APP_ENV=production

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    PATH="/var/www/html/vendor/bin:${PATH}" \
    APP_ENV=${APP_ENV}

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        unzip \
        zip \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libicu-dev \
        default-mysql-client \
        procps \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache \
    && pecl install redis-6.2.0 \
    && docker-php-ext-enable redis \
    && if [ "${APP_ENV}" = "local" ]; then pecl install xdebug && docker-php-ext-enable xdebug; fi \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:2.8.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/base/common/entrypoint-fpm.sh /usr/local/bin/entrypoint-fpm.sh
COPY docker/base/common/entrypoint-cli.sh /usr/local/bin/entrypoint-cli.sh
COPY docker/base/common/healthcheck.php /usr/local/bin/healthcheck.php

RUN chmod +x /usr/local/bin/entrypoint-fpm.sh /usr/local/bin/entrypoint-cli.sh \
    && chmod 755 /usr/local/bin/healthcheck.php

COPY backend/ /var/www/html

RUN if [ -f composer.json ]; then \
      composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader; \
    fi \
    && mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R ug+rwX storage bootstrap/cache

ENTRYPOINT ["/usr/local/bin/entrypoint-fpm.sh"]
CMD ["php-fpm"]
