FROM php:7.4-fpm-alpine

# python3-dev:
#   && pip3 install --upgrade pip \
#   && pip3 install credstash

# openssl-dev
#   NOT SURE

# ORIGINAL
# RUN apk add --no-cache --virtual .dd-build-deps $PHPIZE_DEPS python3-dev openssl-dev libffi-dev \
#     && apk add --no-cache python3 openssl \
#         php7-openssl \
#         ca-certificates \
#     && docker-php-ext-install pdo_mysql sockets \
#     && pecl install -of redis \
#     && docker-php-ext-enable redis \
#     && pip3 install --upgrade pip \
#     && pip3 install credstash

# && apk add --no-cache python3 openssl \
#         php7-openssl \
#         ca-certificates \

# docker-php-ext-install
#   && pecl install -of redis \
#   && docker-php-ext-enable redis

RUN apk add --no-cache --virtual .dd-build-deps $PHPIZE_DEPS libffi-dev

ADD fpm/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
