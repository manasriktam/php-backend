FROM php:fpm
RUN apt-get update

RUN apt-get install -y --no-install-recommends \
    libfreetype6-dev \
    libicu-dev \
    libjpeg-dev \
    libmagickwand-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev

RUN docker-php-ext-install -j "$(nproc)" \ 
    pdo \
    pdo_mysql \
    mysqli \
    bcmath \
    exif \
    gd \
    intl \
    zip

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN pecl install imagick-3.7.0 && docker-php-ext-enable imagick

RUN composer require "hhxsv5/php-sse:~2.0" -vvv

RUN set -eux; \
docker-php-ext-enable opcache; \
{ \
echo 'opcache.memory_consumption=128'; \
echo 'opcache.interned_strings_buffer=8'; \
echo 'opcache.max_accelerated_files=4000'; \
echo 'opcache.revalidate_freq=2'; \
echo 'opcache.fast_shutdown=1'; \
} > /usr/local/etc/php/conf.d/opcache-recommended.ini
