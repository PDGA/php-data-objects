FROM php:8.2.4-cli-alpine

RUN apk add --update linux-headers
RUN apk --no-cache add ${PHPIZE_DEPS}
RUN pecl channel-update pecl.php.net
RUN pecl install xdebug-3.3.2
RUN docker-php-ext-enable xdebug
