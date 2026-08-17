// main.go
package main

import (
	"log"
	"net/http"
)

func main() {
	config := LoadConfig()

	if config.JWTSecret == "" {
		log.Fatal("JWT_SECRET is required")
	}

	gateway := NewGateway(config)

	server := &http.Server{
		Addr:    config.Address,
		Handler: gateway.Handler(),
	}

	log.Printf("API Gateway listening on %s", config.Address)

	if err := server.ListenAndServe(); err != nil {
		log.Fatal(err)
	}
}