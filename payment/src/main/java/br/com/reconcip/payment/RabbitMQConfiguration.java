package br.com.reconcip.payment;

import com.rabbitmq.client.ConnectionFactory;
import org.springframework.amqp.rabbit.annotation.EnableRabbit;
import org.springframework.amqp.rabbit.connection.CachingConnectionFactory;
import org.springframework.amqp.support.converter.Jackson2JsonMessageConverter;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;

@Configuration
@EnableRabbit
public class RabbitMQConfiguration {
    @Value("${messenger.rabbitmq.host}")
    private String host;

    @Value("${messenger.rabbitmq.port}")
    private int port;

    @Value("${messenger.rabbitmq.username}")
    private String username;

    @Value("${messenger.rabbitmq.password}")
    private String password;

    @Bean
    public CachingConnectionFactory connection() {
        ConnectionFactory rabbitFactory = new ConnectionFactory();
        CachingConnectionFactory factory = new CachingConnectionFactory(rabbitFactory);

        factory.setHost(host);
        factory.setPort(port);
        factory.setUsername(username);
        factory.setPassword(password);

        return factory;
    }

    @Bean
    public Jackson2JsonMessageConverter messageConverter() {
        return new Jackson2JsonMessageConverter();
    }
}