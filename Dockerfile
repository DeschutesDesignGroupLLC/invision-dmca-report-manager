FROM php:8.0-fpm

LABEL maintainer="Jon Erickson"

# Download Docker PHP Extension Installer
# Repo: https://github.com/mlocati/docker-php-extension-installer/blob/master/install-php-extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install Extensions
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions gd xdebug exif mysqli zip redis

# Use default PHP production config file
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Include our own to modify the PHP config
COPY php/php.ini "$PHP_INI_DIR/conf.d/99-custom.ini"