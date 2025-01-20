#!/bin/bash

# Define the target directory for uploads.
TARGET_DIR="/app/public/assets/images/uploads"

# Ensure the uploads directory exists
if [ ! -d "$TARGET_DIR" ]; then
    mkdir -p "$TARGET_DIR"
    echo "Directory $TARGET_DIR created."
fi

# Set the correct permissions (777 in this case)
chmod -R 777 "$TARGET_DIR"
echo "Permissions set to 777 for $TARGET_DIR."

# Change directory to /app (where your project files are mounted)
cd /app

# Check if the vendor directory exists and is not empty
if [ ! -d "vendor" ] || [ -z "$(ls -A vendor)" ]; then
    echo "Vendor directory is missing or empty. Running composer install..."
    composer install --optimize-autoloader
else
    echo "Vendor directory exists and is not empty. Skipping composer install."
fi

# Execute the main command (start PHP-FPM)
exec "$@"
