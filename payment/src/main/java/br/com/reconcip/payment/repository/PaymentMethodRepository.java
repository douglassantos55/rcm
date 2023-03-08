package br.com.reconcip.payment.repository;

import br.com.reconcip.payment.entity.PaymentMethod;
import org.springframework.data.repository.ListCrudRepository;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.UUID;

@Repository
public interface PaymentMethodRepository extends ListCrudRepository<PaymentMethod, UUID> {
    List<PaymentMethod> findByDeletedAtNull();
}
