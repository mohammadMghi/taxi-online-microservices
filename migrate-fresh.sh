#!/bin/bash

set -e

SERVICES=(
    "user-service"
    "driver-service"
    "location-service"
    "ride-service"
    "notification-service"
    "payment-service"
)

echo "🚀 Starting migrate:fresh..."

for SERVICE in "${SERVICES[@]}"; do
    echo ""
    echo "========================================"
    echo "🗄️  Migrating: $SERVICE"
    echo "========================================"

    docker compose exec -T "$SERVICE" php artisan migrate:fresh --force
done

echo ""
echo "========================================"
echo "✅ All databases migrated successfully!"
echo "========================================"