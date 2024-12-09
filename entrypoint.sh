#!/bin/bash

# Define the target directory
TARGET_DIR="/app/public/assets/images/uploads"

# Check if the directory exists
if [ ! -d "$TARGET_DIR" ]; then
    # If it doesn't exist, create it
    mkdir -p "$TARGET_DIR"
    echo "Directory $TARGET_DIR created."
fi

# Set the correct permissions (777 in this case)
chmod -R 777 "$TARGET_DIR"
echo "Permissions set to 777 for $TARGET_DIR."

# Execute the main command (start PHP-FPM)
exec "$@"
