package pkg

import (
	"context"

	"github.com/go-kit/kit/endpoint"
	"github.com/google/uuid"
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
		if err != nil && err != ErrEntryNotFound {
			return nil, err
		}
		return entry, nil
	}
}

func rentDeletedEndpoint(svc Service) endpoint.Endpoint {
	return func(ctx context.Context, request any) (any, error) {
		uuid := request.(uuid.UUID)
		return nil, svc.DeleteEntry(uuid)
	}
}
