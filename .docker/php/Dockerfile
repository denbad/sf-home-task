FROM php:8.3.0-fpm-bullseye

WORKDIR /app

EXPOSE 9000

RUN set -eux; \
    apt-get update; \
    apt-get install --yes --no-install-recommends \
    git \
    # intl \
    libicu-dev \
    # zip
    libzip-dev \
; \
docker-php-ext-install -j$(nproc) opcache intl zip pdo_mysql bcmath;

COPY php/www.conf $PHP_INI_DIR/../php-fpm.d/www.conf
COPY php/php.dev.ini $PHP_INI_DIR/php.ini
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ARG host_user=9001
ARG host_group=9001

RUN groupadd --gid ${host_user} app \
    && useradd --create-home --home-dir /home/app --gid app --groups www-data --uid $host_user app \
    && passwd --lock app

RUN mkdir /home/app/.composer && chown app:www-data /home/app/.composer

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/bin/symfony \
    && rm -rf /root/.symfony5/bin/symfony

CMD ["php-fpm"]
