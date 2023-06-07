<?php

namespace App\Messenger;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQMessenger implements Messenger
{
    /**
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

    public function __construct(string $host, int $port, string $user, string $password)
    {
        $this->connection = new AMQPStreamConnection($host, $port, $user, $password);
        $this->channel = $this->connection->channel();
    }

    public function send(mixed $data, string $key)
    {
        // Make sure the exchange is declared
        $this->channel->exchange_declare('rents', AMQPExchangeType::TOPIC, false, true, false);

        $json = json_encode($data);
        if ($json === false) {
            Log::debug("could not encode data to JSON", ['data' => $data]);
            return;
        }

        $message = new AMQPMessage($json, [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);

        $this->channel->basic_publish($message, 'rents', $key);

        $this->channel->close();
        $this->connection->close();
    }
}
