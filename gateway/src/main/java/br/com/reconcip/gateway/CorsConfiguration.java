package br.com.reconcip.gateway;

import org.bouncycastle.util.Strings;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.web.cors.reactive.CorsConfigurationSource;
import org.springframework.web.cors.reactive.UrlBasedCorsConfigurationSource;

import java.util.Arrays;

@Configuration
public class CorsConfiguration {
    @Value("${management.endpoints.web.cors.allowed-origins}")
    private String allowedOrigins;

    @Value("${management.endpoints.web.cors.allowed-methods}")
    private String allowedMethods;

    @Value("${management.endpoints.web.cors.allowed-headers}")
    private String allowedHeaders;

    @Bean
    public CorsConfigurationSource corsConfigurationSource() {
        org.springframework.web.cors.CorsConfiguration configuration = new org.springframework.web.cors.CorsConfiguration();

        configuration.setAllowedOrigins(Arrays.asList(Strings.split(allowedOrigins, ',')));
        configuration.setAllowedHeaders(Arrays.asList(Strings.split(allowedHeaders, ',')));
        configuration.setAllowedMethods(Arrays.asList(Strings.split(allowedMethods, ',')));

        UrlBasedCorsConfigurationSource source = new UrlBasedCorsConfigurationSource();
        source.registerCorsConfiguration("/**", configuration);

        return source;
    }

}
