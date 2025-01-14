FROM php:8.4-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    git \
    unzip \
    && docker-php-ext-install zip pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /app

# Copy the application files
COPY . /app

# Install dependencies
RUN composer install

# Expose WebSocket port (if needed)
EXPOSE 8080

# Command to start Ratchet WebSocket server (adjust as needed)
CMD ["php", "app/Socket/index.php"]
