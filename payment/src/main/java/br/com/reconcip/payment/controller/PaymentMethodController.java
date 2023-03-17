package br.com.reconcip.payment.controller;

import br.com.reconcip.payment.entity.PaymentMethod;
import br.com.reconcip.payment.repository.PaymentMethodRepository;
import jakarta.validation.Valid;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpMethod;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.server.ResponseStatusException;

import java.time.Instant;
import java.util.List;
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

    @DeleteMapping("/{id}")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void delete(@PathVariable UUID id) {
        PaymentMethod paymentMethod = this.repository.findById(id).orElseThrow(() ->
                new ResponseStatusException(HttpStatus.NOT_FOUND)
        );
        paymentMethod.setDeletedAt(Instant.now());
        this.repository.save(paymentMethod);
    }

    @GetMapping("/{id}")
    PaymentMethod get(@PathVariable UUID id) {
        return this.repository.findById(id).orElseThrow(() ->
                new ResponseStatusException(HttpStatus.NOT_FOUND)
        );
    }

    @RequestMapping(method = {RequestMethod.HEAD, RequestMethod.GET})
    public List<PaymentMethod> list() {
        return this.repository.findByDeletedAtNull();
    }
}
