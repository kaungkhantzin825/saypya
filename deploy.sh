#!/bin/bash



cd /var/www/html/sanpyalearning



git fetch origin main

git reset --hard origin/main



composer install --no-dev --optimize-autoloader



php artisan migrate --force



php artisan config:clear

php artisan cache:clear

php artisan view:clear
