###############################################################################
# Base image chosen by Home-Assistant add-on guidelines
###############################################################################
ARG BUILD_FROM=ghcr.io/home-assistant/amd64-base:latest

###############################
# ----- Stage 1: Builder -----
###############################
FROM ${BUILD_FROM} AS build

# ── builder stage ───────────────────────────────────────────────
RUN apk add --no-cache \
      php83-cli \
      php83-intl php83-gd php83-zip \
      php83-mbstring php83-json php83-tokenizer php83-xml php83-ctype \
      php83-openssl php83-phar php83-iconv php83-curl php83-dom \
      php83-pdo_sqlite \
      composer git

WORKDIR /app
COPY ./homeglo /app
RUN COMPOSER_MEMORY_LIMIT=-1 \
    composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction --verbose

################################
# ----- Stage 2: Runtime -----
################################
FROM ${BUILD_FROM}

# ── 3. Install runtime packages only ────────────────────────────────────────
RUN apk add --no-cache \
      nginx \
      php83-cli php83-fpm php83-opcache php83-session php83-sockets \
      php83-intl php83-gd php83-pdo_sqlite \
      php83-mbstring php83-json php83-tokenizer php83-xml php83-ctype \
      php83-openssl php83-curl php83-iconv php83-dom php83-fileinfo \
      sqlite tzdata su-exec

# ── 4. Copy built code from the builder stage ───────────────────────────────
COPY --from=build /app /app/homeglo

# ── 5. Add s6 service definitions & any init scripts ────────────────────────
COPY rootfs/ /
RUN chmod +x /etc/services.d/*/run && \
    if [ -d /etc/cont-init.d ]; then chmod +x /etc/cont-init.d/* || true; fi

# ── 6. Fix runtime-writable paths ───────────────────────────────
RUN set -e; \
    # Remove existing directories if they exist to ensure clean permissions
    rm -rf /app/homeglo/runtime /app/homeglo/web/assets && \
    mkdir -p /app/homeglo/runtime /app/homeglo/web/assets && \
    chown -R nginx:nginx /app/homeglo/runtime /app/homeglo/web/assets && \
    chmod -R 775 /app/homeglo/runtime /app/homeglo/web/assets && \
    # Ensure parent directories have correct permissions too
    chown nginx:nginx /app/homeglo/web && \
    chmod 755 /app/homeglo/web

# ── 7. Sanity-check nginx config at build time (optional) ───────────────────
RUN nginx -t

EXPOSE 80
# No CMD/ENTRYPOINT – s6-overlay’s /init is PID 1