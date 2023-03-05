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

@RestController
@RequestMapping("/payment-conditions")
public class PaymentConditionController {
    @Autowired
    private PaymentConditionRepository repository;

    @Autowired
    private PaymentTypeRepository paymentTypeRepository;

    @PostMapping
    @ResponseBody
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
}
