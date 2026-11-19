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

echo "🐳 Building all services..."
docker compose build --no-cache

echo ""
echo "🚀 Starting all services..."
docker compose up -d

echo ""
echo "⏳ Waiting for services to start..."
sleep 10

echo ""
echo "🗄️ Starting migrate:fresh..."

for SERVICE in "${SERVICES[@]}"; do
    echo ""
    echo "========================================"
    echo "🗄️  Migrating: $SERVICE"
    echo "========================================"

    docker compose exec -T "$SERVICE" php artisan migrate:fresh --force
done

echo ""
echo "========================================"
echo "✅ All services built, started and migrated successfully!"
echo "========================================"