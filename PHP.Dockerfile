FROM php:8.4-fpm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy the entrypoint script into the container
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

# Make the script executable
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set the script as the entrypoint
ENTRYPOINT ["entrypoint.sh"]

# Command to run when the container starts
CMD ["php-fpm"]
