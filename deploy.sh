#!/bin/bash

echo "🚀 Déploiement Symfony..."

# Installer les dépendances
composer install --no-dev --optimize-autoloader

# Migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Assets (si utilisé)
# npm install
# npm run build

echo "✅ Déploiement terminé"