package main

import (
	"os"
)

type Config struct {
	Address                  string
	JWTSecret                string
	UserServiceURL           string
	DriverServiceURL         string
	LocationServiceURL       string
	NotificationServiceURL   string
	PaymentServiceURL        string
	RideServiceURL           string
}

func LoadConfig() Config {
	return Config{
		Address:                getEnv("GATEWAY_ADDRESS", ":8080"),
		JWTSecret:              getEnv("JWT_SECRET", "pfRCM8fiirKHFhi0SRVgVeParjCho0Ke8mMnvbzBzMBtlidpVYxhVyM0owchFVqk"),
		UserServiceURL:         getEnv("USER_SERVICE_URL", "http://127.0.0.1:8000"),
		NotificationServiceURL: getEnv("NOTIFICATION_SERVICE_URL", "http://localhost:8001"),
		DriverServiceURL:       getEnv("DRIVER_SERVICE_URL", "http://localhost:8002"),
		PaymentServiceURL:      getEnv("PAYMENT_SERVICE_URL", "http://localhost:8003"),
		LocationServiceURL:     getEnv("LOCATION_SERVICE_URL", "http://localhost:8004"),
		RideServiceURL:         getEnv("RIDE_SERVICE_URL", "http://localhost:8005"),
	}
}

func getEnv(key, fallback string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}

	return fallback
}