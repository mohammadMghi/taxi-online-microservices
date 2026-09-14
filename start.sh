#!/bin/bash

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SESSION="taxi"

# =========================
# Helpers
# =========================

create_php_service() {
    local service="$1"
    local port="$2"
    local consumer="$3"
    local consumer_name="$4"

    tmux new-window -t "$SESSION" -n "$service"

    # Application
    tmux send-keys -t "$SESSION:$service" \
        "cd \"$ROOT_DIR/$service\" && php artisan serve --port=$port" Enter

    tmux select-pane \
        -t "$SESSION:$service.0" \
        -T "App"

    # Kafka Consumer
    if [ -n "$consumer" ]; then
        tmux split-window -v -t "$SESSION:$service.0"

        tmux send-keys -t "$SESSION:$service.1" \
            "cd \"$ROOT_DIR/$service\" && php artisan $consumer" Enter

        tmux select-pane \
            -t "$SESSION:$service.1" \
            -T "$consumer_name"

        tmux select-layout -t "$SESSION:$service" even-vertical
    fi
}


# =========================
# Create Session
# =========================

tmux new-session -d -s "$SESSION" -n "Gateway"


# =========================
# Gateway
# =========================

tmux send-keys -t "$SESSION:Gateway" \
    "cd \"$ROOT_DIR/Gateway\" && go run ." Enter

tmux select-pane \
    -t "$SESSION:Gateway.0" \
    -T "App"


# =========================
# User Service
# =========================

create_php_service \
    "UserService" \
    "8000" \
    "kafka:deduct-mony-command" \
    "Kafka: DeductMoney"


# =========================
# Notification Service
# =========================

create_php_service \
    "NotificationService" \
    "8001" \
    "kafka:consume-driver-notifications" \
    "Kafka: DriverNotifications"


# =========================
# Driver Service
# =========================

create_php_service \
    "DriverService" \
    "8002" \
    "kafka:consume-nearby-drivers" \
    "Kafka: NearbyDrivers"


# =========================
# Payment Service
# =========================

create_php_service \
    "PaymentService" \
    "8003" \
    "" \
    ""


# =========================
# Location Service
# =========================

create_php_service \
    "LocationService" \
    "8004" \
    "kafka:validation-location" \
    "Kafka: ValidationLocation"


# =========================
# Ride Service
# =========================

create_php_service \
    "RideService" \
    "8005" \
    "kafka:consume-insufficient-balance" \
    "Kafka: InsufficientBalance"


# =========================
# Attach
# =========================

tmux attach -t "$SESSION"
