package pkg

import (
	"fmt"

	"github.com/streadway/amqp"
)

var connection *amqp.Connection

func Connect(host, user, password string) (*amqp.Connection, error) {
	if connection == nil {
		conn, err := amqp.Dial(fmt.Sprintf("amqp://%s:%s@%s", user, password, host))
		if err != nil {
			return nil, err
		}
		connection = conn
	}
	return connection, nil
}
