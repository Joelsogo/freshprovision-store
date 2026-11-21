FROM php:8.3-cli

# Install PostgreSQL PDO extension
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy all application files
COPY . .

# Expose the port Render will use
EXPOSE 10000

# Start the PHP built-in server
# Render injects the $PORT environment variable
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000}"]
