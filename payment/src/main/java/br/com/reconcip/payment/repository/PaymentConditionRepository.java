package br.com.reconcip.payment.repository;

import br.com.reconcip.payment.entity.PaymentCondition;
import org.springframework.data.repository.ListCrudRepository;
import org.springframework.stereotype.Repository;

import java.util.UUID;

@Repository
public interface PaymentConditionRepository extends ListCrudRepository<PaymentCondition, UUID> {
}
