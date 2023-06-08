package pkg

import (
	"context"

	"github.com/go-kit/kit/endpoint"
)

func rentCreatedEndpoint(svc Service) endpoint.Endpoint {
	return func(ctx context.Context, request any) (any, error) {
		transaction := request.(Transaction)
		entry, err := svc.RentCreated(transaction)
		if err != nil {
			return nil, err
		}
		return entry, nil
	}
}

func rentUpdatedEndpoint(svc Service) endpoint.Endpoint {
	return func(ctx context.Context, request any) (any, error) {
		transaction := request.(Transaction)
		entry, err := svc.UpdateEntry(transaction)
		if err != nil {
			return nil, err
		}
		return entry, nil
	}
}

