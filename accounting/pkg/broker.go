package pkg

import (
	"fmt"

	"github.com/streadway/amqp"
)

func Connect(host, user, password string) (*amqp.Connection, error) {
	conn, err := amqp.Dial(fmt.Sprintf("amqp://%s:%s@%s", user, password, host))
	if err != nil {
		return nil, err
	}
	return conn, nil
}
