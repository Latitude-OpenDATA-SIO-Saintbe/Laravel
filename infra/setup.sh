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
bash ./db-seed/setup-py.sh

echo "final préparation"
npm install
composer install
php artisan key:generate
php artisan migrate
npm run build

echo "All tasks completed successfully."

echo "you can now ether do php artisan serve to view application or you can do php artisan test to test the application"
