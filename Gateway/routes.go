// routes.go
package main

import (
	"net/http"
	"strings"

	"github.com/golang-jwt/jwt/v5"
)

type Routes struct {
	config Config

	userProxy         http.Handler
	driverProxy       http.Handler
	locationProxy     http.Handler
	notificationProxy http.Handler
	paymentProxy      http.Handler
}

func NewRoutes(config Config) *Routes {
	return &Routes{
		config: config,

		userProxy:         NewProxy(config.UserServiceURL),
		driverProxy:       NewProxy(config.DriverServiceURL),
		locationProxy:     NewProxy(config.LocationServiceURL),
		notificationProxy: NewProxy(config.NotificationServiceURL),
		paymentProxy:      NewProxy(config.PaymentServiceURL),
	}
}

func (r *Routes) Handler() http.Handler {
	mux := http.NewServeMux()

	// Public routes.
	mux.Handle("/api/v1/auth/", r.userProxy)

	// Protected routes.
	mux.Handle(
		"/api/v1/users/",
		AuthMiddleware(r.config.JWTSecret, r.userProxy),
	)

	mux.Handle(
		"/api/v1/drivers/",
		AuthMiddleware(r.config.JWTSecret, r.driverProxy),
	)

	mux.Handle(
		"/api/v1/locations/",
		AuthMiddleware(r.config.JWTSecret, r.locationProxy),
	)

	mux.Handle(
		"/api/v1/notifications/",
		AuthMiddleware(r.config.JWTSecret, r.notificationProxy),
	)

	mux.Handle(
		"/api/v1/payments/",
		AuthMiddleware(r.config.JWTSecret, r.paymentProxy),
	)

	mux.HandleFunc("/health", func(w http.ResponseWriter, _ *http.Request) {
		w.WriteHeader(http.StatusOK)
		_, _ = w.Write([]byte("OK"))
	})

	return RequestIDMiddleware(mux)
}

type Claims struct {
	UserID string `json:"user_id"`
	Role   string `json:"role"`

	jwt.RegisteredClaims
}

func AuthMiddleware(secret string, next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		tokenString, err := extractBearerToken(r)

		if err != nil {
			http.Error(w, "unauthorized", http.StatusUnauthorized)
			return
		}

		claims, err := validateJWT(tokenString, secret)

		if err != nil {
			http.Error(w, "unauthorized", http.StatusUnauthorized)
			return
		}

		/*
			Do not trust these headers from the client.

			The Gateway removes them and creates them from
			the verified JWT instead.
		*/
		r.Header.Del("X-User-ID")
		r.Header.Del("X-User-Role")

		r.Header.Set("X-User-ID", claims.UserID)
		r.Header.Set("X-User-Role", claims.Role)

		next.ServeHTTP(w, r)
	})
}

func extractBearerToken(r *http.Request) (string, error) {
	header := r.Header.Get("Authorization")

	parts := strings.Fields(header)

	if len(parts) != 2 || !strings.EqualFold(parts[0], "Bearer") {
		return "", jwt.ErrTokenMalformed
	}

	return parts[1], nil
}

func validateJWT(tokenString string, secret string) (*Claims, error) {
	token, err := jwt.ParseWithClaims(
		tokenString,
		&Claims{},
		func(token *jwt.Token) (interface{}, error) {
			// Prevent algorithm confusion attacks.
			if token.Method != jwt.SigningMethodHS256 {
				return nil, jwt.ErrTokenSignatureInvalid
			}

			return []byte(secret), nil
		},
	)

	if err != nil {
		return nil, err
	}

	claims, ok := token.Claims.(*Claims)

	if !ok || !token.Valid {
		return nil, jwt.ErrTokenInvalidClaims
	}

	return claims, nil
}

func RequestIDMiddleware(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		requestID := r.Header.Get("X-Request-ID")

		if requestID == "" {
			requestID = generateRequestID()
		}

		r.Header.Set("X-Request-ID", requestID)
		w.Header().Set("X-Request-ID", requestID)

		next.ServeHTTP(w, r)
	})
}

func generateRequestID() string {
	return "request-id"
}