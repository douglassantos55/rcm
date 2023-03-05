package br.com.reconcip.payment.controller;

import br.com.reconcip.payment.entity.PaymentType;
import br.com.reconcip.payment.repository.PaymentTypeRepository;
import jakarta.validation.Valid;
import jakarta.websocket.server.PathParam;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.server.ResponseStatusException;

import java.util.UUID;

@RestController
@RequestMapping("/payment-types")
public class PaymentTypeController {
    @Autowired
    private PaymentTypeRepository repository;

    @PostMapping
    @ResponseStatus(HttpStatus.CREATED)
    PaymentType create(@Valid @RequestBody PaymentType paymentType) {
        return this.repository.save(paymentType);
    }

    @PutMapping("/{id}")
    PaymentType update(@Valid @RequestBody PaymentType paymentType, @PathVariable UUID id) {
        PaymentType type = this.repository.findById(id).orElseThrow(() ->
                new ResponseStatusException(HttpStatus.NOT_FOUND)
        );
        type.setName(paymentType.getName());
        return this.repository.save(type);
    }
}
