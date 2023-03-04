package br.com.reconcip.payment.controller;

import br.com.reconcip.payment.entity.PaymentType;
import br.com.reconcip.payment.repository.PaymentTypeRepository;
import jakarta.validation.Valid;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/payment-types")
public class PaymentTypeController {
    @Autowired
    private PaymentTypeRepository repository;

    @PostMapping
    @ResponseStatus(HttpStatus.CREATED)
    PaymentType create(@Valid @RequestBody PaymentType paymentType) {
        System.out.println("doing this mom");
        return this.repository.save(paymentType);
    }
}
