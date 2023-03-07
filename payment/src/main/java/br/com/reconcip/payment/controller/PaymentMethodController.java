package br.com.reconcip.payment.controller;

import br.com.reconcip.payment.entity.PaymentMethod;
import br.com.reconcip.payment.repository.PaymentMethodRepository;
import jakarta.validation.Valid;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.server.ResponseStatusException;

import java.util.UUID;

@RestController
@RequestMapping("/payment-methods")
public class PaymentMethodController {
    @Autowired
    private PaymentMethodRepository repository;

    @PostMapping
    @ResponseStatus(HttpStatus.CREATED)
    public PaymentMethod create(@RequestBody @Valid PaymentMethod method) {
        return this.repository.save(method);
    }

    @PutMapping("/{id}")
    public PaymentMethod update(@RequestBody @Valid PaymentMethod method, @PathVariable UUID id) {
        PaymentMethod paymentMethod = this.repository.findById(id).orElseThrow(() ->
                new ResponseStatusException(HttpStatus.NOT_FOUND)
        );
        paymentMethod.setName(method.getName());
        return this.repository.save(paymentMethod);
    }
}
