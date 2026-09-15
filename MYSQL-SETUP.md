# MySQL + Docker Setup

## Summary

All services now use a **single MySQL container** with separate databases for each service.

## What Was Created

### 1. Individual Dockerfiles (in each service directory)
- `UserService/Dockerfile`
- `DriverService/Dockerfile`
- `LocationService/Dockerfile`
- `RideService/Dockerfile`
- `NotificationService/Dockerfile`
- `PaymentService/Dockerfile`

Each Dockerfile:
- Multi-stage build (Node.js for assets, PHP for app)
- PHP 8.3-fpm-alpine base image
- Installs Composer dependencies
- Compiles frontend assets
- Auto-runs migrations on startup

### 2. MySQL Database Setup
- `mysql/init/01-create-databases.sql` - Creates all 6 databases and users

### 3. Updated docker-compose.yml
- Single MySQL 8.0 container
- Redis container for caching
- Kafka + Zookeeper for messaging
- All services configured to use MySQL
- Health checks for all services
- Auto-migration on startup
- Proper networking and dependencies

## Database Configuration

All databases created and configured:
- `user_service` (user: admin, password: 852456)
- `driver_service` (user: admin, password: 852456)
- `location_service` (user: admin, password: 852456)
- `ride_service` (user: admin, password: 852456)
- `notification_service` (user: admin, password: 852456)
- `payment_service` (user: root, password: empty)

## Service Ports

- User Service: 8000
- Driver Service: 8001
- Location Service: 8002
- Ride Service: 8003
- Notification Service: 8004
- Payment Service: 8005
- API Gateway: 8080
- MySQL: 3306
- Kafka: 9092
- Redis: 6379

## Quick Start

```bash
docker compose build
docker compose up -d
docker compose logs -f
```

## Common Commands

```bash
docker compose ps
docker compose logs user-service
docker compose exec mysql mysql -uroot -p
docker compose down
docker compose down -v
```
