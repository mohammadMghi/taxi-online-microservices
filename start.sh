#!/bin/bash

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

SESSION="taxi"

tmux new-session -d -s "$SESSION"

# Gateway
tmux rename-window -t "$SESSION:0" "Gateway"
tmux send-keys -t "$SESSION:0" \
    "cd \"$ROOT_DIR/Gateway\" && go run ." Enter

# User Service
tmux new-window -t "$SESSION" -n "UserService"
tmux send-keys -t "$SESSION:UserService" \
    "cd \"$ROOT_DIR/UserService\" && php artisan serve --port=8000" Enter

# Notification Service
tmux new-window -t "$SESSION" -n "NotificationService"
tmux send-keys -t "$SESSION:NotificationService" \
    "cd \"$ROOT_DIR/NotificationService\" && php artisan serve --port=8001" Enter

# Driver Service
tmux new-window -t "$SESSION" -n "DriverService"
tmux send-keys -t "$SESSION:DriverService" \
    "cd \"$ROOT_DIR/DriverService\" && php artisan serve --port=8002" Enter

# Payment Service
tmux new-window -t "$SESSION" -n "PaymentService"
tmux send-keys -t "$SESSION:PaymentService" \
    "cd \"$ROOT_DIR/PaymentService\" && php artisan serve --port=8003" Enter

# Location Service
tmux new-window -t "$SESSION" -n "LocationService"
tmux send-keys -t "$SESSION:LocationService" \
    "cd \"$ROOT_DIR/LocationService\" && php artisan serve --port=8004" Enter

# Ride Service
tmux new-window -t "$SESSION" -n "RideService"
tmux send-keys -t "$SESSION:RideService" \
    "cd \"$ROOT_DIR/RideService\" && php artisan serve --port=8005" Enter

# Driver Kafka Consumer
tmux new-window -t "$SESSION" -n "DriverKafka"
tmux send-keys -t "$SESSION:DriverKafka" \
    "cd \"$ROOT_DIR/DriverService\" && php artisan kafka:consum" Enter

# Location Kafka Consumer
tmux new-window -t "$SESSION" -n "LocationKafka"
tmux send-keys -t "$SESSION:LocationKafka" \
    "cd \"$ROOT_DIR/LocationService\" && php artisan kafka:validation-location" Enter

# Notification Kafka Consumer
tmux new-window -t "$SESSION" -n "NotificationKafka"
tmux send-keys -t "$SESSION:NotificationKafka" \
    "cd \"$ROOT_DIR/NotificationService\" && php artisan kafka:comsume-notification" Enter
    
# insufficient-balance consumer
tmux new-window -t "$SESSION" -n "Consume:insufficient-balance"
tmux send-keys -t "$SESSION:InsufficientBalance" \
   "cd \"$ROOT_DIR/RideService\" && php artisan kafka:consume-insufficient-balance" Enter 

tmux attach -t "$SESSION"
