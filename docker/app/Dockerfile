#syntax=docker/dockerfile:1

# Versions
FROM dunglas/frankenphp:1-php8.3 AS frankenphp_upstream

# The different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# Base FrankenPHP image
FROM frankenphp_upstream AS frankenphp_base

WORKDIR /app

# persistent / runtime deps
# PARTYNEXUS: Adding ffmpeg
# hadolint ignore=DL3008
RUN apt-get update && apt-get install -y --no-install-recommends \
	acl \
	file \
	gettext \
	git \
    ffmpeg \
	&& rm -rf /var/lib/apt/lists/*

RUN set -eux; \
	install-php-extensions \
		@composer \
		apcu \
		intl \
		opcache \
		zip \
	;

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

ENV MERCURE_SUBSCRIBER_JWT_ALG=RS256
ENV JWT_SECRET_KEY="%kernel.project_dir%/config/jwt/private.pem"
ENV JWT_PUBLIC_KEY="%kernel.project_dir%/config/jwt/public.pem"

# PARTYNEXUS: Adding redis
###> recipes ###
###> doctrine/doctrine-bundle ###
RUN install-php-extensions pdo_pgsql redis
###< doctrine/doctrine-bundle ###
###< recipes ###

COPY --link ./docker/app/frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --link --chmod=755 ./docker/app/frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link ./docker/app/frankenphp/Caddyfile /etc/caddy/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1
CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile" ]

# Dev FrankenPHP image
FROM frankenphp_base AS frankenphp_dev

ENV APP_ENV=dev XDEBUG_MODE=off

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY ./docker/app/frankenphp/xdebug /usr/bin/xdebug
RUN chmod +x /usr/bin/xdebug

RUN set -eux; \
	install-php-extensions \
		xdebug \
	;

COPY --link ./docker/app/frankenphp/conf.d/20-app.dev.ini $PHP_INI_DIR/app.conf.d/

CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--watch" ]

# Frontend intermediary image
FROM node:lts AS frontend

COPY --link ./frontend /app

RUN cd /app && npm install && npm run build

# Prod FrankenPHP image
FROM frankenphp_base AS frankenphp_preprod

ENV APP_ENV=dev
ENV FRANKENPHP_CONFIG="import worker.Caddyfile"

# hadolint ignore=DL3008
RUN apt-get update && apt-get install -y --no-install-recommends \
	supervisor \
	&& rm -rf /var/lib/apt/lists/*

COPY --link ./docker/app/supervisord.conf /etc/supervisor/supervisord.conf

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link ./docker/app/frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/
COPY --link ./docker/app/frankenphp/worker.Caddyfile /etc/caddy/worker.Caddyfile

# prevent the reinstallation of vendors at every changes in the source code
COPY --link composer.* symfony.* ./
RUN set -eux; \
	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# copy sources
COPY --link ../.. ./
RUN rm -Rf ./docker

COPY --from=frontend /app/dist/ /frontend

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	# composer dump-env prod; \ # We don't want to use the JWT secret key specified here
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync;

ARG PARTYNEXUS_VERSION='indev'
ARG PARTYNEXUS_COMMIT='INDEV'

ENV PARTYNEXUS_VERSION=${PARTYNEXUS_VERSION}
ENV PARTYNEXUS_COMMIT=${PARTYNEXUS_COMMIT}

# Setting the version infos
RUN sed -i \
    -e "s/PARTYNEXUS_VERSION: 'indev'/PARTYNEXUS_VERSION: '${PARTYNEXUS_VERSION}'/" \
    -e "s/PARTYNEXUS_COMMIT: 'INDEV'/PARTYNEXUS_COMMIT: '${PARTYNEXUS_COMMIT}'/" \
    /app/config/services.yaml

CMD [ "/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf" ]


# Prod FrankenPHP image
FROM frankenphp_base AS frankenphp_prod

ENV APP_ENV=prod
ENV FRANKENPHP_CONFIG="import worker.Caddyfile"

# hadolint ignore=DL3008
RUN apt-get update && apt-get install -y --no-install-recommends \
	supervisor \
	&& rm -rf /var/lib/apt/lists/*

COPY --link ./docker/app/supervisord.conf /etc/supervisor/supervisord.conf

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link ./docker/app/frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/app.conf.d/
COPY --link ./docker/app/frankenphp/worker.Caddyfile /etc/caddy/worker.Caddyfile

# prevent the reinstallation of vendors at every changes in the source code
COPY --link composer.* symfony.* ./
RUN set -eux; \
	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# copy sources
COPY --link ../.. ./
RUN rm -Rf ./docker

COPY --from=frontend /app/dist/ /frontend

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	# composer dump-env prod; \ # We don't want to use the JWT secret key specified here
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync;

ARG PARTYNEXUS_VERSION='indev'
ARG PARTYNEXUS_COMMIT='INDEV'

ENV PARTYNEXUS_VERSION=${PARTYNEXUS_VERSION}
ENV PARTYNEXUS_COMMIT=${PARTYNEXUS_COMMIT}

# Setting the version infos
RUN sed -i \
    -e "s/PARTYNEXUS_VERSION: 'indev'/PARTYNEXUS_VERSION: '${PARTYNEXUS_VERSION}'/" \
    -e "s/PARTYNEXUS_COMMIT: 'INDEV'/PARTYNEXUS_COMMIT: '${PARTYNEXUS_COMMIT}'/" \
    /app/config/services.yaml

CMD [ "/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf" ]
