#!/bin/bash

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

tmux new-session -d -s taxi

tmux rename-window -t taxi:0 "Gateway"
tmux send-keys -t taxi:0 "cd $ROOT_DIR/Gateway && go run ." Enter

tmux new-window -t taxi -n "UserService"
tmux send-keys -t taxi:"UserService" "cd $ROOT_DIR/UserService && php artisan serve --port=8000" Enter

tmux new-window -t taxi -n "NotificationService"
tmux send-keys -t taxi:"Notification" "cd $ROOT_DIR/NotificationService && php artisan serve --port=8001" Enter

tmux new-window -t taxi -n "DriverService"
tmux send-keys -t taxi:"Driver" "cd $ROOT_DIR/DriverService && php artisan serve --port=8002" Enter

tmux new-window -t taxi -n "PaymentService"
tmux send-keys -t taxi:"Payment" "cd $ROOT_DIR/PaymentSerivce && php artisan serve --port=8003" Enter

tmux new-window -t taxi -n "LocationService"
tmux send-keys -t taxi:"Location" "cd $ROOT_DIR/LocationService && php artisan serve --port=8004" Enter

tmux new-window -t taxi -n "RideService"
tmux send-keys -t taxi:"Ride" "cd $ROOT_DIR/RideService && php artisan serve --port=8005" Enter

tmux attach -t taxi
