FROM dunglas/frankenphp:1.1-builder-php8.3.7


ENV SERVER_NAME="http://"

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git \
    unzip \
    librabbitmq-dev \
    libpq-dev \
    supervisor

RUN install-php-extensions \
    gd \
    pcntl \
    opcache \
    pdo \
    pdo_pgsql \
    redis \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

COPY ./.docker/php/php.ini /etc/frankenphp/php.ini
COPY ./.docker/etc/supervisor.d/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN pecl install xdebug

RUN composer install --ignore-platform-reqs

RUN docker-php-ext-enable xdebug

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 22999

CMD ["/usr/bin/supervisord", "-n", "-c",  "/etc/supervisor/conf.d/supervisord.conf"]
