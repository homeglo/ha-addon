# ─────────────────────────────────────────────────────────────
#  Dockerfile  (build with:  docker build -t homeglo-php .)
# ─────────────────────────────────────────────────────────────
FROM yiisoftware/yii2-php:8.2-fpm-nginx

# Install SQLite (should already be available, but make sure)
RUN apt-get update && apt-get install -y \
    sqlite3 \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy application code
COPY . /app

# Create runtime directory and set proper permissions
RUN mkdir -p /app/runtime && \
    chown -R www-data:www-data /app/runtime && \
    chmod -R 755 /app/runtime

# Install Composer dependencies
RUN if [ -f composer.json ]; then \
      composer install --no-interaction --prefer-dist --optimize-autoloader; \
    fi

# Set proper permissions for the entire app
RUN chown -R www-data:www-data /app && \
    chmod -R 755 /app && \
    chmod -R 777 /app/runtime

# Create database file if it doesn't exist and set permissions
RUN touch /app/runtime/database.sqlite && \
    chown www-data:www-data /app/runtime/database.sqlite && \
    chmod 666 /app/runtime/database.sqlite

# Expose Apache's port
EXPOSE 80

# Entrypoint inherited from yiisoftware/yii2-php image → `apache2-foreground`