#!/bin/bash

# Start the Docker container in detached mode
echo "Starting the Docker container..."
docker-compose -f ./infra/docker-compose-dev.yml up -d

# Confirm the container is running
echo "Checking container status..."
docker-compose -f ./infra/docker-compose-dev.yml ps

# Clone the setup repository
echo "Cloning the setup repository..."
git clone --branch main https://github.com/Latitude-OpenDATA-SIO-Saintbe/PythonPopPostgres.git ./db-seed

# Run the setup script to create and seed the database
echo "Running database setup script..."
bash ./db-seed/setup-py.sh ./db-seed

echo "install driver"

# Dynamic PHP setup for PostgreSQL extension
PHP_EXTENSION_DIR=$(php -r "echo ini_get('extension_dir');")

echo "Checking for pdo_pgsql extension..."
if ! php -m | grep -q pdo_pgsql; then
  echo "pdo_pgsql not found. Installing and enabling..."

  # Add repository and install necessary packages
  sudo add-apt-repository ppa:ondrej/php -y
  sudo apt-get update
  sudo apt-get install -y php8.2-pgsql

  # Locate the extension and copy to the correct directory
  EXTENSION_PATH=$(find / -name "pdo_pgsql.so" 2>/dev/null | head -n 1)
  if [ -n "$EXTENSION_PATH" ]; then
    echo "Found pdo_pgsql.so at $EXTENSION_PATH"
    cp "$EXTENSION_PATH" "$PHP_EXTENSION_DIR"
    chmod +x "$PHP_EXTENSION_DIR/pdo_pgsql.so"
  else
    echo "pdo_pgsql.so not found on the system."
    exit 1
  fi

  # Uncomment extension=pdo_pgsql in php.ini if necessary
  PHP_INI_FILE=$(php --ini | grep "Loaded Configuration" | awk '{print $4}')
  echo "Found php.ini at $PHP_INI_FILE"

  if grep -q "^;extension=pdo_pgsql" "$PHP_INI_FILE"; then
    echo "Uncommenting extension=pdo_pgsql in php.ini..."
    sudo sed -i 's/^;extension=pdo_pgsql/extension=pdo_pgsql/' "$PHP_INI_FILE"
  else
    echo "extension=pdo_pgsql is already uncommented in php.ini."
  fi

  # Restart PHP to apply changes (this is PHP-FPM, adjust if using a different handler)
  echo "Restarting PHP-FPM service..."
  sudo systemctl restart php${PHP_VERSION%10000}-fpm

else
  echo "pdo_pgsql extension is already installed."
fi

  # Check if the extension is available now
  if ! php -m | grep -q pdo_pgsql; then
    echo "pdo_pgsql extension not found after installation."
    exit 1
  fi

echo "final préparation"
npm install
composer install
php artisan key:generate
php artisan migrate
npm run build

echo "All tasks completed successfully."

echo "you can now either run php artisan serve to view application or php artisan test to test the application"
