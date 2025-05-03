FROM php:8.3-fpm

WORKDIR /app

RUN apt-get update && apt-get install -y \
    curl \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    opcache \
    mbstring \
    curl

EXPOSE 9000

CMD ["php-fpm", "-F"]