# Base official PHP image
FROM php:8.4-fpm-alpine

# Install base package
RUN apk upgrade --update && apk add --no-cache \
    $PHPIZE_DEPS \
    git \
    wget \
    vim \
    supervisor \
    icu-dev \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    openssl-dev \
    pkgconfig \
    unixodbc-dev \
    zlib-dev libzip-dev \
    rabbitmq-c-dev \
    imagemagick-dev \
    imagemagick \
    tzdata

# Set timezone
RUN cp /usr/share/zoneinfo/Europe/Moscow /etc/localtime && "date"

# Install php modules
RUN docker-php-ext-configure zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) pdo mysqli pdo_mysql opcache zip pcntl \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install intl

# Install Image Processing and GD
RUN docker-php-ext-configure gd -with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
  && docker-php-ext-install -j "$(nproc)" gd

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

# Install xDebug
RUN  apk add --update linux-headers \
    && pecl channel-update pecl.php.net \
    && pecl install xdebug-3.4.0 \
    && docker-php-ext-enable xdebug

# Create and change workdir
RUN mkdir /app
WORKDIR /app
