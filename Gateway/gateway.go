package main

import (
	"net/http"
)

type Gateway struct {
	config Config
	routes *Routes
}

func NewGateway(config Config) *Gateway {
	return &Gateway{
		config: config,
		routes: NewRoutes(config),
	}
}

func (g *Gateway) Handler() http.Handler {
	return g.routes.Handler()
}