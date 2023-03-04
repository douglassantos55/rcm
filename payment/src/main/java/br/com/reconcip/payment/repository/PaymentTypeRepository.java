package br.com.reconcip.payment.repository;

import br.com.reconcip.payment.entity.PaymentType;
import org.springframework.data.repository.ListCrudRepository;
import org.springframework.stereotype.Repository;

import java.util.UUID;

@Repository
public interface PaymentTypeRepository extends ListCrudRepository<PaymentType, UUID> {
}
