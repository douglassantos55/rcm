package pkg

import (
	"context"
	"encoding/json"
	"fmt"

	kit_amqp "github.com/go-kit/kit/transport/amqp"
	"github.com/streadway/amqp"
)

func OrderCreatedSubscriber(svc Service, host, user, password string) error {
	conn, err := amqp.Dial(fmt.Sprintf("amqp://%s:%s@%s", user, password, host))
	if err != nil {
		return err
	}

	channel, err := conn.Channel()
	if err != nil {
		return err
	}

	if err := channel.ExchangeDeclare("orders", amqp.ExchangeTopic, true, false, false, false, nil); err != nil {
		return err
	}

	queue, err := channel.QueueDeclare("accounting.order.created", true, false, false, false, nil)
	if err != nil {
		return err
	}

	if err := channel.QueueBind(queue.Name, "order.created", "orders", false, nil); err != nil {
		return err
	}

	subscriber := kit_amqp.NewSubscriber(
		makeOrderCreatedEndpoint(svc),
		decodeTransaction,
		kit_amqp.EncodeJSONResponse,
	)

	messages, err := channel.Consume(queue.Name, "", false, false, false, false, nil)
	if err != nil {
		return err
	}

	var forever chan bool

	go func() {
		handler := subscriber.ServeDelivery(channel)
		for message := range messages {
			handler(&message)
		}
	}()

	print("[x] Waiting for messages\n")

	<-forever
	return nil
}

func decodeTransaction(ctx context.Context, message *amqp.Delivery) (any, error) {
	var transaction Transaction
	if err := json.Unmarshal(message.Body, &transaction); err != nil {
		return nil, err
	}
	return transaction, nil
}
