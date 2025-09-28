FROM php:8.3-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock* ./

# Install PHP dependencies (if composer.json exists with dependencies)
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader || true; fi

# Copy application code
COPY . .

# Make the CLI executable
RUN chmod +x gh-analyzer

# Create a symbolic link to make it globally available
RUN ln -sf /app/gh-analyzer /usr/local/bin/gh-analyzer

ENTRYPOINT ["gh-analyzer"]
CMD ["--help"]