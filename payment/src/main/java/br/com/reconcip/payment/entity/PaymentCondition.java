package br.com.reconcip.payment.entity;

import jakarta.persistence.*;
import jakarta.validation.constraints.Max;
import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.NotEmpty;
import jakarta.validation.constraints.NotNull;

import java.util.UUID;

@Entity
public class PaymentCondition {
    @Id
    @GeneratedValue(strategy = GenerationType.UUID)
    private UUID id;

    @NotNull
    @NotEmpty
    private String name;

    @NotNull
    @NotEmpty
    private String title;

    @Min(0)
    @Max(1000)
    private short increment;

    @ManyToOne(optional = false)
    private PaymentType paymentType;

    @Min(0)
    @Max(98)
    private short installments;

    public UUID getId() {
        return id;
    }

    public void setId(UUID id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public short getIncrement() {
        return increment;
    }

    public void setIncrement(short increment) {
        this.increment = increment;
    }

    public PaymentType getPaymentType() {
        return paymentType;
    }

    public void setPaymentType(PaymentType paymentType) {
        this.paymentType = paymentType;
    }

    public short getInstallments() {
        return installments;
    }

    public void setInstallments(short installments) {
        this.installments = installments;
    }
}
