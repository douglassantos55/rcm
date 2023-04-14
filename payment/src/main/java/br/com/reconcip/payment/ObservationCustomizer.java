package br.com.reconcip.payment;

import org.springframework.boot.actuate.autoconfigure.observation.ObservationRegistryCustomizer;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.http.server.observation.ServerRequestObservationContext;

@Configuration
public class ObservationCustomizer {
    /**
     * Ignores observation for metrics and security requests
     *
     * @return ObservationRegistryCustomizer
     */
    @Bean
    public ObservationRegistryCustomizer customizer() {
        return (registry) -> registry.observationConfig().observationPredicate((name, ctx) -> {
            if (ctx instanceof ServerRequestObservationContext) {
                ServerRequestObservationContext context = (ServerRequestObservationContext) ctx;
                return !context.getCarrier().getRequestURI().equals("/actuator/prometheus");
            }
            return !name.startsWith("spring.security.");
        });
    }
}
