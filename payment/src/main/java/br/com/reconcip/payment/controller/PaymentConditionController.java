package br.com.reconcip.payment.controller;

import br.com.reconcip.payment.entity.PaymentCondition;
import br.com.reconcip.payment.entity.PaymentType;
import br.com.reconcip.payment.repository.PaymentConditionRepository;
import br.com.reconcip.payment.repository.PaymentTypeRepository;
import jakarta.validation.Valid;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.server.ResponseStatusException;

import java.time.Instant;
import java.util.List;
import java.util.UUID;

@RestController
@RequestMapping("/payment-conditions")
public class PaymentConditionController {
    @Autowired
    private PaymentConditionRepository repository;

    @Autowired
    private PaymentTypeRepository paymentTypeRepository;

    @PostMapping
    @ResponseStatus(HttpStatus.CREATED)
    public PaymentCondition create(@RequestBody @Valid br.com.reconcip.payment.dto.PaymentCondition paymentCondition) {
        PaymentType paymentType = this.paymentTypeRepository.findById(paymentCondition.paymentType()).orElseThrow(() ->
                new ResponseStatusException(HttpStatus.BAD_REQUEST, "invalid payment type")
        );

        PaymentCondition condition = new PaymentCondition();

        condition.setName(paymentCondition.name());
        condition.setTitle(paymentCondition.title());
        condition.setIncrement(paymentCondition.increment());
        condition.setInstallments(paymentCondition.installments());
        condition.setPaymentType(paymentType);

        return this.repository.save(condition);
    }

    @PutMapping("/{id}")
    public PaymentCondition update(@RequestBody @Valid br.com.reconcip.payment.dto.PaymentCondition data, @PathVariable UUID id) {
        PaymentCondition condition = this.repository.findById(id).orElseThrow(() ->
                new ResponseStatusException(HttpStatus.NOT_FOUND)
        );

        PaymentType paymentType = this.paymentTypeRepository.findById(data.paymentType()).orElseThrow(() ->
                new ResponseStatusException(HttpStatus.BAD_REQUEST, "invalid payment type")
        );

        condition.setPaymentType(paymentType);
        condition.setName(data.name());
        condition.setInstallments(data.installments());
        condition.setTitle(data.title());
        condition.setIncrement(data.increment());

        return this.repository.save(condition);
    }

    @DeleteMapping("/{id}")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void delete(@PathVariable UUID id) {
        PaymentCondition condition = this.repository.findById(id).orElseThrow(() ->
                new ResponseStatusException(HttpStatus.NOT_FOUND)
        );

        condition.setDeletedAt(Instant.now());
        this.repository.save(condition);
    }

    @GetMapping("/{id}")
    PaymentCondition get(@PathVariable UUID id) {
        return this.repository.findById(id).orElseThrow(() ->
                new ResponseStatusException(HttpStatus.NOT_FOUND)
        );
    }

    @RequestMapping(method = { RequestMethod.HEAD, RequestMethod.GET })
    public List<PaymentCondition> list() {
        return this.repository.findByDeletedAtNull();
    }
}
