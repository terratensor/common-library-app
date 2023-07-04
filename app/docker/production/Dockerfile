FROM php:8.1-cli-alpine AS builder

RUN apk add --no-cache unzip

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet

WORKDIR /app

COPY ./composer.json ./composer.lock ./

RUN composer install --no-dev --prefer-dist --no-progress --no-suggest --optimize-autoloader \
    && rm -rf /root/.composer/cache


FROM yiisoftware/yii2-php:8.1-apache

WORKDIR /app

COPY --from=builder /app ./
COPY ./ ./
