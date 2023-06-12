package pkg

import (
	"context"
	"encoding/json"
	"log"

	kit_amqp "github.com/go-kit/kit/transport/amqp"
	"github.com/google/uuid"
	"github.com/streadway/amqp"
)

func setupQueue(channel *amqp.Channel, exchange, key, name string) (*amqp.Queue, error) {
	if err := channel.ExchangeDeclare(exchange, amqp.ExchangeTopic, true, false, false, false, nil); err != nil {
		return nil, err
	}

	queue, err := channel.QueueDeclare(name, true, false, false, false, nil)
	if err != nil {
		return nil, err
	}

	if err := channel.QueueBind(queue.Name, key, exchange, false, nil); err != nil {
		return nil, err
	}

	return &queue, nil
}

func RentCreatedSubscriber(svc Service, channel *amqp.Channel) error {
	queue, err := setupQueue(channel, "rents", "rent.created", "accounting.rent.created")
	if err != nil {
		return err
	}

	messages, err := channel.Consume(queue.Name, "", false, false, false, false, nil)
	if err != nil {
		return err
	}

	subscriber := kit_amqp.NewSubscriber(
		rentCreatedEndpoint(svc),
		decodeTransaction,
		kit_amqp.EncodeJSONResponse,
		kit_amqp.SubscriberResponsePublisher(kit_amqp.NopResponsePublisher),
		kit_amqp.SubscriberErrorEncoder(kit_amqp.SingleNackRequeueErrorEncoder),
	)

	handler := subscriber.ServeDelivery(channel)

	print("[*] Waiting for 'rent.created' messages\n")

	for message := range messages {
		log.Printf("[*] Message 'rent.created': %s", message.Body)
		handler(&message)
		if err := message.Ack(false); err != nil {
			log.Printf("ack failed: %v", err)
		}
	}

	return nil
}

func RentUpdatedSubscriber(svc Service, channel *amqp.Channel) error {
	queue, err := setupQueue(channel, "rents", "rent.updated", "accounting.rent.updated")
	if err != nil {
		return err
	}

	messages, err := channel.Consume(queue.Name, "", false, false, false, false, nil)
	if err != nil {
		return err
	}

	subscriber := kit_amqp.NewSubscriber(
		rentUpdatedEndpoint(svc),
		decodeTransaction,
		kit_amqp.EncodeJSONResponse,
		kit_amqp.SubscriberResponsePublisher(kit_amqp.NopResponsePublisher),
		kit_amqp.SubscriberErrorEncoder(kit_amqp.SingleNackRequeueErrorEncoder),
	)

	handler := subscriber.ServeDelivery(channel)

	print("[*] Waiting for 'rent.updated' messages\n")

	for message := range messages {
		log.Printf("[*] Message 'rent.updated': %s", message.Body)
		handler(&message)
		if err := message.Ack(false); err != nil {
			log.Printf("ack failed: %v", err)
		}
	}

	return nil
}

func RentDeletedSubscriber(svc Service, channel *amqp.Channel) error {
	queue, err := setupQueue(channel, "rents", "rent.deleted", "accounting.rent.deleted")
	if err != nil {
		return err
	}

	messages, err := channel.Consume(queue.Name, "", false, false, false, false, nil)
	if err != nil {
		return err
	}

	subscriber := kit_amqp.NewSubscriber(
		rentDeletedEndpoint(svc),
		decodeUUID,
		kit_amqp.EncodeJSONResponse,
		kit_amqp.SubscriberResponsePublisher(kit_amqp.NopResponsePublisher),
		kit_amqp.SubscriberErrorEncoder(kit_amqp.SingleNackRequeueErrorEncoder),
	)

	handler := subscriber.ServeDelivery(channel)

	print("[*] Waiting for 'rent.deleted' messages\n")

	for message := range messages {
		log.Printf("[*] Message 'rent.deleted': %s", message.Body)
		handler(&message)
		if err := message.Ack(false); err != nil {
			log.Printf("ack failed: %v", err)
		}
	}

	return nil
}

func decodeTransaction(ctx context.Context, message *amqp.Delivery) (any, error) {
	var transaction Transaction
	if err := json.Unmarshal(message.Body, &transaction); err != nil {
		return nil, err
	}
	return transaction, nil
}

func decodeUUID(ctx context.Context, message *amqp.Delivery) (any, error) {
	uuid, err := uuid.ParseBytes(message.Body)
	if err != nil {
		return nil, err
	}
	return uuid, nil
}
