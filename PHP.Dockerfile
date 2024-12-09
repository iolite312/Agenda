FROM php:8.4-fpm

# Install Git and required dependencies
RUN apt-get update && apt-get install -y git unzip \
    && docker-php-ext-install pdo pdo_mysql

# Copy composer from the official Composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the entrypoint script into the container
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

# Make the script executable
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set the script as the entrypoint
ENTRYPOINT ["entrypoint.sh"]

# Command to run when the container starts
CMD ["php-fpm"]
