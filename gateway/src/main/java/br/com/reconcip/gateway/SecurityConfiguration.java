package br.com.reconcip.gateway;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.security.config.annotation.web.reactive.EnableWebFluxSecurity;
import org.springframework.security.config.web.server.ServerHttpSecurity;
import org.springframework.security.oauth2.core.DelegatingOAuth2TokenValidator;
import org.springframework.security.oauth2.core.OAuth2TokenValidator;
import org.springframework.security.oauth2.jose.jws.JwsAlgorithms;
import org.springframework.security.oauth2.jwt.*;
import org.springframework.security.web.server.SecurityWebFilterChain;

import javax.crypto.SecretKey;
import javax.crypto.spec.SecretKeySpec;
import java.util.List;

@Configuration
@EnableWebFluxSecurity
public class SecurityConfiguration {
    @Value("${jwt.secret}")
    private String secret;

    @Value("${jwt.issuer}")
    private String issuer;

    @Value("${jwt.audience}")
    private String audience;

    @Bean
    public ReactiveJwtDecoder jwtDecoder() {
        SecretKey secretKey = new SecretKeySpec(secret.getBytes(), JwsAlgorithms.HS256);
        NimbusReactiveJwtDecoder decoder = NimbusReactiveJwtDecoder.withSecretKey(secretKey).build();

        decoder.setJwtValidator(jwtValidator());
        return decoder;
    }

    public OAuth2TokenValidator<Jwt> jwtValidator() {
        return new DelegatingOAuth2TokenValidator(
                new JwtTimestampValidator(),
                new JwtIssuerValidator(issuer),
                new JwtClaimValidator<List<String>>(
                        JwtClaimNames.AUD,
                        value -> value != null && value.contains(audience)
                )
        );
    }

    @Bean
    public SecurityWebFilterChain securityFilterChain(ServerHttpSecurity http) throws Exception {
        return http
                .authorizeExchange()
                .anyExchange()
                .authenticated().and()
                .oauth2ResourceServer(ServerHttpSecurity.OAuth2ResourceServerSpec::jwt)
                .cors().and()
                .build();
    }
}
