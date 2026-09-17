FROM php:8.2-apache

ARG ENABLE_XDEBUG

RUN apt-get update && apt-get install -y --no-install-recommends git zip unzip libicu-dev libtidy-dev zlib1g-dev libpng-dev libzip-dev supervisor

RUN docker-php-ext-install mysqli pdo_mysql bcmath tidy sockets

RUN docker-php-ext-configure zip && docker-php-ext-install zip

RUN docker-php-ext-configure intl && docker-php-ext-install intl

RUN docker-php-ext-configure pcntl --enable-pcntl && docker-php-ext-install pcntl

RUN docker-php-ext-configure gd && docker-php-ext-install gd

RUN pecl install -o -f redis &&  rm -rf /tmp/pear &&  docker-php-ext-enable redis

RUN curl https://getcomposer.org/download/2.6.6/composer.phar -o /usr/local/bin/composer && chmod +x /usr/local/bin/composer

RUN echo "memory_limit=4096M" > $PHP_INI_DIR/conf.d/memory-limit.ini
RUN echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT" > $PHP_INI_DIR/conf.d/error_reporting.ini

RUN a2enmod rewrite

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

RUN npx --yes playwright install-deps chromium

# Chromium cannot write to the default $HOME/.cache when 'docker compose exec' runs as a non-root uid
ENV PLAYWRIGHT_BROWSERS_PATH=/ms-playwright
RUN mkdir -p /ms-playwright && chmod 777 /ms-playwright

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN sed -ri -e 's!^Listen 80$!Listen 80\nListen 8000!' /etc/apache2/ports.conf
RUN sed -ri -e 's!<VirtualHost \*:80>!<VirtualHost *:80 *:8000>!' /etc/apache2/sites-available/*.conf

COPY limits.conf /etc/apache2/conf-enabled/limits.conf
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN if [ "$ENABLE_XDEBUG" = "true" ] ; then \
      pecl install xdebug-3.1.5 && \
      docker-php-ext-enable xdebug ; \
  else \
      rm /usr/local/etc/php/conf.d/xdebug.ini ; \
  fi
