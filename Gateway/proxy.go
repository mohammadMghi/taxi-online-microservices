// proxy.go
package main

import (
	"net/http"
	"net/http/httputil"
	"net/url"
	"time"
)

func NewProxy(target string) http.Handler {
	targetURL, err := url.Parse(target)
	if err != nil {
		panic(err)
	}

	proxy := httputil.NewSingleHostReverseProxy(targetURL)

	originalDirector := proxy.Director

	proxy.Director = func(req *http.Request) {
		originalDirector(req)

		req.Header.Set("X-Gateway", "go-api-gateway")

		if req.Header.Get("X-Forwarded-Proto") == "" {
			req.Header.Set("X-Forwarded-Proto", "http")
		}
	}

	proxy.Transport = &http.Transport{
		Proxy: http.ProxyFromEnvironment,

		MaxIdleConns:        100,
		MaxIdleConnsPerHost: 20,
		IdleConnTimeout:     90 * time.Second,

		TLSHandshakeTimeout:   10 * time.Second,
		ResponseHeaderTimeout: 30 * time.Second,
		ExpectContinueTimeout: 1 * time.Second,
	}

	proxy.ErrorHandler = func(w http.ResponseWriter, _ *http.Request, _ error) {
		http.Error(
			w,
			"service unavailable",
			http.StatusBadGateway,
		)
	}

	return proxy
}