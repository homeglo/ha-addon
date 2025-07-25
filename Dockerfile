ARG BUILD_FROM=ghcr.io/home-assistant/amd64-base:latest

###############################
# ----- Stage 1: Builder -----
###############################
FROM yiisoftware/yii2-php:8.2-fpm-nginx AS build

# Copy app code and install dependencies
WORKDIR /app
COPY ./app /app
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

################################
# ----- Stage 2:  Runtime -----
################################
# Home-Assistant base image (Alpine + s6-overlay)
FROM $BUILD_FROM

# Install runtime deps (apk, not apt!)
RUN apk add --no-cache \
        nginx \
        php82-fpm \
        php82-pdo_sqlite \
        php82-session \
        php82-opcache \
        php82-cli \
        php82-sockets \
        php82-mbstring \
        php82-dom \
        php82-xml \
        php82-ctype \
        php82-json \
        php82-tokenizer \
        php82-fileinfo \
        php82-openssl \
        php82-curl \
        php82-iconv \
        php82-phar \
        sqlite \
        tzdata \
        su-exec

# Copy code from builder
COPY --from=build /app /app/homeglo

# Copy s6 service scripts (see below)
COPY rootfs/ /

RUN chmod +x /etc/services.d/*/run && \
    if [ -d /etc/cont-init.d ]; then chmod +x /etc/cont-init.d/* || true; fi

# Fix permissions expected by Yii
RUN mkdir -p /app/homeglo/runtime /app/homeglo/web/assets && \
    chmod -R 777 /app/homeglo/runtime && \
    chmod -R 777 /app/homeglo/web/assets

RUN nginx -t

EXPOSE 80
# No CMD/ENTRYPOINT – s6 /init is PID 1