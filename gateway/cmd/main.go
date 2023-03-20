package main

import (
	"context"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"os"
	"strings"
	"time"

	"github.com/go-kit/kit/endpoint"
	"github.com/go-kit/kit/sd"
	"github.com/go-kit/kit/sd/consul"
	"github.com/julienschmidt/httprouter"

	httptransport "github.com/go-kit/kit/transport/http"
	"github.com/go-kit/log"
	"github.com/hashicorp/consul/api"
)

type Map struct {
	Method   string
	Path     string
	Endpoint sd.Endpointer
}

func forwardFactory(method, path string) sd.Factory {
	return func(instance string) (endpoint.Endpoint, io.Closer, error) {
		url, err := url.Parse(strings.TrimSuffix(instance, ":0") + path)
		if err != nil {
			return nil, nil, err
		}
		return forwardRequest(method, url), nil, nil
	}
}

func forwardRequest(method string, url *url.URL) endpoint.Endpoint {
	return httptransport.NewClient(
		method,
		url,
		httptransport.EncodeJSONRequest,
		func(ctx context.Context, r *http.Response) (response any, err error) {
			err = json.NewDecoder(r.Body).Decode(&response)
			return response, err
		},
		httptransport.ClientBefore(parseParams),
	).Endpoint()
}

func parseParams(ctx context.Context, r *http.Request) context.Context {
	r.Header.Add("accept", "application/json")
	r.URL.RawQuery = ctx.Value("query").(string)
	for _, param := range httprouter.ParamsFromContext(ctx) {
		r.URL.Path = strings.Replace(
			r.URL.Path,
			fmt.Sprintf("{%s}", param.Key),
			param.Value,
			1,
		)
	}
	return ctx
}

func forward(instancer sd.Instancer, path, target string, logger log.Logger) []Map {
	options := sd.InvalidateOnError(time.Second)
	return []Map{
		{
			Method:   http.MethodGet,
			Path:     path,
			Endpoint: sd.NewEndpointer(instancer, forwardFactory(http.MethodGet, target), logger, options),
		},
		{
			Method:   http.MethodGet,
			Path:     path + "/:id",
			Endpoint: sd.NewEndpointer(instancer, forwardFactory(http.MethodGet, target+"/{id}"), logger, options),
		},
		{
			Path:     path,
			Method:   http.MethodPost,
			Endpoint: sd.NewEndpointer(instancer, forwardFactory(http.MethodPost, target), logger, options),
		},
		{
			Method:   http.MethodPut,
			Path:     path + "/:id",
			Endpoint: sd.NewEndpointer(instancer, forwardFactory(http.MethodPut, target+"/{id}"), logger, options),
		},
		{
			Method:   http.MethodDelete,
			Path:     path + "/:id",
			Endpoint: sd.NewEndpointer(instancer, forwardFactory(http.MethodDelete, target+"/{id}"), logger, options),
		},
	}
}

func main() {
	config := api.DefaultConfig()
	config.Address = os.Getenv("CONSUL_ADDR")

	consulClient, err := api.NewClient(config)
	if err != nil {
		return
	}

	logger := log.NewLogfmtLogger(os.Stderr)
	logger = log.With(logger, "ts", log.DefaultTimestampUTC)

	client := consul.NewClient(consulClient)
	inventory := consul.NewInstancer(client, logger, "inventory", []string{}, true)
	renting := consul.NewInstancer(client, logger, "renting", []string{}, true)
	payment := consul.NewInstancer(client, logger, "payment", []string{}, true)

	endpointers := [][]Map{
		forward(inventory, "/equipment", "/api/equipment", logger),
		forward(inventory, "/suppliers", "/api/suppliers", logger),
		forward(renting, "/periods", "/api/periods", logger),
		forward(renting, "/customers", "/api/customers", logger),
		forward(renting, "/rents", "/api/rents", logger),
		forward(payment, "/payment-types", "/payment-types", logger),
		forward(payment, "/payment-methods", "/payment-methods", logger),
		forward(payment, "/payment-conditions", "/payment-conditions", logger),
	}

	router := httprouter.New()
	for _, mapper := range endpointers {
		for _, endpointer := range mapper {
			endpoints, err := endpointer.Endpoint.Endpoints()
			if err != nil {
				continue
			}
			for _, endpoint := range endpoints {
				router.Handler(
					endpointer.Method,
					endpointer.Path,
					httptransport.NewServer(
						endpoint,
						func(ctx context.Context, r *http.Request) (req any, err error) {
							err = json.NewDecoder(r.Body).Decode(&req)
							return req, nil
						},
						httptransport.EncodeJSONResponse,
						httptransport.ServerBefore(
							func(ctx context.Context, r *http.Request) context.Context {
								return context.WithValue(ctx, "query", r.URL.RawQuery)
							},
						),
					),
				)
			}
		}
	}

	logger.Log(http.ListenAndServe(":8000", router))
}
