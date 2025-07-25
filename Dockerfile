ARG BUILD_FROM=ghcr.io/home-assistant/amd64-base:latest

###############################
# ----- Stage 1: Builder -----
###############################
FROM yiisoftware/yii2-php:8.2-fpm-nginx AS build

# Nothing changes here except the base image
WORKDIR /app
COPY . /app
RUN if [ -f composer.json ]; then \
        composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader; \
    fi

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
        sqlite \
        tzdata \
        su-exec

# Copy code from builder
COPY --from=build /app /app

# Fix permissions expected by Yii
RUN mkdir -p /app/runtime && \
    chmod -R 775 /app/runtime

# Copy s6 service scripts (see below)
COPY rootfs/ /

RUN chmod +x /etc/services.d/*/run
RUN chmod -R 777 /app/app/runtime

RUN nginx -t

EXPOSE 80
# No CMD/ENTRYPOINT – s6 /init is PID 1