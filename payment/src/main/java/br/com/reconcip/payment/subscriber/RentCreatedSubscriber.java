package br.com.reconcip.payment.subscriber;

import org.springframework.amqp.core.ExchangeTypes;
import org.springframework.amqp.rabbit.annotation.Exchange;
import org.springframework.amqp.rabbit.annotation.Queue;
import org.springframework.amqp.rabbit.annotation.QueueBinding;
import org.springframework.amqp.rabbit.annotation.RabbitListener;
import org.springframework.stereotype.Component;

@Component
public class RentCreatedSubscriber {
    private record Rent(String id, float value, String date) {

    }

    @RabbitListener(
            bindings = @QueueBinding(
                    value = @Queue(name = "payment.rent.updated"),
                    exchange = @Exchange(name = "rents", type = ExchangeTypes.TOPIC),
                    key = "rent.updated"
            )
    )
    public void processRent(Rent rent) {
        System.out.println(rent);
    }
}
