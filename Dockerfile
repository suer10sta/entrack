FROM php:8.3-fpm-bookworm


RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpq-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
       gd \
       pdo_pgsql \
       bcmath \
       zip \
       dom \
       xml \
       mbstring \
       opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY . .

RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/cache \
                storage/framework/sessions \
                storage/framework/views \
                storage/logs \
                bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
