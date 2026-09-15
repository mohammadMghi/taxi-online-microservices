CREATE DATABASE IF NOT EXISTS user_service;
CREATE DATABASE IF NOT EXISTS driver_service;
CREATE DATABASE IF NOT EXISTS location_service;
CREATE DATABASE IF NOT EXISTS ride_service;
CREATE DATABASE IF NOT EXISTS notification_service;
CREATE DATABASE IF NOT EXISTS payment_service;

CREATE USER IF NOT EXISTS 'admin'@'%' IDENTIFIED BY '852456';
CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY '';

GRANT ALL PRIVILEGES ON user_service.* TO 'admin'@'%';
GRANT ALL PRIVILEGES ON driver_service.* TO 'admin'@'%';
GRANT ALL PRIVILEGES ON location_service.* TO 'admin'@'%';
GRANT ALL PRIVILEGES ON ride_service.* TO 'admin'@'%';
GRANT ALL PRIVILEGES ON notification_service.* TO 'admin'@'%';
GRANT ALL PRIVILEGES ON payment_service.* TO 'root'@'%';

FLUSH PRIVILEGES;