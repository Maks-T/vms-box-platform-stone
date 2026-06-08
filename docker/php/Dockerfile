FROM php:8.5-fpm-alpine

# Аргументы для создания пользователя
ARG user=laravel
ARG uid=1000

# 1. Установка системных зависимостей (runtime)

RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    icu-dev \
    imagemagick-dev \
    libpq-dev \
    postgresql18-client \
    shadow \
    nodejs \
    npm \
    linux-headers \
    libtool \
    ghostscript-dev

# 2. Конфигурация и установка PHP расширений

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    bcmath \
    exif \
    gd \
    zip \
    intl \
    pcntl

# 3. Установка Redis, Xdebug и Imagick через PECL
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis xdebug imagick \
    && docker-php-ext-enable redis xdebug imagick \
    && apk del .build-deps

# 4. Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Создание пользователя и настройка окружения
RUN useradd -G www-data,root -u $uid -d /home/$user $user \
    && mkdir -p /home/$user/.composer \
    && chown -R $user:$user /home/$user

# 6. Настройка рабочей директории
WORKDIR /var/www/html

# 7. Переключение на созданного пользователя
USER $user

# Запуск FPM
CMD ["php-fpm"]
