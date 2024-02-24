FROM ghcr.io/kuaukutsu/php:8.1-cli as app_cli_81_build
FROM ghcr.io/kuaukutsu/php:8.2-cli as app_cli_82_build
FROM ghcr.io/kuaukutsu/php:8.3-cli as app_cli_83_build

FROM app_cli_83_build AS app_setup

# Arguments
ARG UID=10001
ARG WORKDIR="/src"

# Configure
COPY conf/php.ini /usr/local/etc/php/php.ini

COPY --from=composer:latest --link /usr/bin/composer /usr/bin/composer

# Adduser
RUN adduser -u $UID -G www-data -s /bin/sh -D developer www-data

# Create structure
RUN mkdir -p $WORKDIR \
    && chown -R $UID:www-data $WORKDIR

# Install extensions https://github.com/mlocati/docker-php-extension-installer
RUN install-php-extensions \
      sync

FROM app_setup AS app_cli

USER $UID
WORKDIR $WORKDIR

FROM app_setup AS app_devel

# Install xdebug
RUN install-php-extensions xdebug

USER $UID
WORKDIR $WORKDIR
