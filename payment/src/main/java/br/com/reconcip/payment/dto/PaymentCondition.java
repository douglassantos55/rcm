package br.com.reconcip.payment.dto;

import jakarta.validation.constraints.Max;
import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.NotEmpty;
import jakarta.validation.constraints.NotNull;

import java.util.UUID;

public record PaymentCondition(
        @NotNull
        @NotEmpty
        String name,
        @NotNull
        @NotEmpty
        String title,
        @Min(0)
        @Max(1000)
        short increment,
        @Min(0)
        @Max(98)
        short installments,
        @NotNull
        UUID paymentTypeId
) {
}
