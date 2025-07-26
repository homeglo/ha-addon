#!/bin/sh

# Composer install
composer install

#Copy env file for user
cp .env.example .env

# Run migrations
php yii migrate --migrationPath=@yii/log/migrations/ --interactive=0
php yii migrate --migrationPath=@yii/rbac/migrations --interactive=0
php yii migrate/up --interactive=0

# chmod directories
chmod 777 web/assets
chmod 777 runtime