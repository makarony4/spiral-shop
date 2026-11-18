# Спільний образ для всіх Spiral-сервісів: PHP CLI + бінарник RoadRunner.
# Ніякого php-fpm/nginx всередині — HTTP/gRPC-сервер це сам RoadRunner.
#
# Контекст збірки — корінь репозиторію (щоб бачити спільний proto/),
# конкретний сервіс обирається через --build-arg SERVICE=catalog|orders|notifications.
FROM ghcr.io/roadrunner-server/roadrunner:2025.1.6 AS roadrunner
FROM composer:2 AS composer
FROM mlocati/php-extension-installer:2 AS ext-installer

FROM php:8.4-cli-alpine AS runtime

# orders додатково ставить grpc (потрібен gRPC-клієнту); сервер на RoadRunner його не потребує
ARG PHP_EXTENSIONS="pdo_pgsql sockets opcache"
COPY --from=ext-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions ${PHP_EXTENSIONS}

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

# ---------- образ конкретного сервісу ----------
FROM runtime AS app
ARG SERVICE

# Спочатку лише composer-файли — кеш шару із залежностями переживе зміни в коді
COPY services/${SERVICE}/composer.json services/${SERVICE}/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY services/${SERVICE}/ ./
# gRPC-контракт: RoadRunner читає .proto під час старту grpc-плагіна
COPY proto/ ./proto/
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev \
 && mkdir -p runtime/cache && chown -R www-data:www-data runtime

COPY --chmod=755 services/${SERVICE}/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

USER www-data
# Прогріваємо кеш tokenizer-лістенерів, щоб воркери не створювали його паралельно на старті
RUN php app.php > /dev/null
EXPOSE 8080
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["rr", "serve", "-c", ".rr.yaml"]

# ---------- кодогенерація з .proto (make proto) ----------
FROM app AS codegen
USER root
ARG TARGETARCH
ARG RR_VERSION=2025.1.6
RUN apk add --no-cache protobuf-dev protoc \
 && wget -qO- "https://github.com/roadrunner-server/roadrunner/releases/download/v${RR_VERSION}/protoc-gen-php-grpc-${RR_VERSION}-linux-${TARGETARCH}.tar.gz" \
    | tar -xz -C /tmp \
 && mv /tmp/protoc-gen-php-grpc-*/protoc-gen-php-grpc /usr/local/bin/ \
 && rm -rf /tmp/protoc-gen-php-grpc-* \
 # без classmap-authoritative: нові згенеровані класи мають підхоплюватись автозавантажувачем
 && composer dump-autoload --no-dev
ENTRYPOINT []
