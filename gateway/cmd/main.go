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

const QUERY_CONTEXT_KEY = "query"

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
		decodeJSONResponse,
		httptransport.ClientBefore(
			setRouteParams,
			appendQueryString,
			setAcceptHeader("application/json"),
		),
	).Endpoint()
}

func setAcceptHeader(mediaType string) httptransport.RequestFunc {
	return func(ctx context.Context, r *http.Request) context.Context {
		r.Header.Add("Accept", mediaType)
		return ctx
	}
}

func appendQueryString(ctx context.Context, r *http.Request) context.Context {
	r.URL.RawQuery = ctx.Value(QUERY_CONTEXT_KEY).(string)
	return ctx
}

func setRouteParams(ctx context.Context, r *http.Request) context.Context {
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

func queryToContext(ctx context.Context, r *http.Request) context.Context {
	return context.WithValue(ctx, QUERY_CONTEXT_KEY, r.URL.RawQuery)
}

func decodeJSONRequest(ctx context.Context, r *http.Request) (req any, err error) {
	err = json.NewDecoder(r.Body).Decode(&req)
	return req, nil
}

func decodeJSONResponse(ctx context.Context, r *http.Response) (res any, err error) {
	err = json.NewDecoder(r.Body).Decode(&res)
	return res, err
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
						decodeJSONRequest,
						httptransport.EncodeJSONResponse,
						httptransport.ServerBefore(
							queryToContext,      // grab query string
						),
					),
				)
			}
		}
	}

	logger.Log(http.ListenAndServe(":8000", router))
}
