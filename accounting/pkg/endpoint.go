package pkg

import (
	"context"

	"github.com/go-kit/kit/endpoint"
)

func makeOrderCreatedEndpoint(svc Service) endpoint.Endpoint {
	return func(ctx context.Context, request any) (any, error) {
		transaction := request.(Transaction)
		entry, err := svc.RentCreated(transaction)
		if err != nil {
			return nil, err
		}
		return entry, nil
	}
}
