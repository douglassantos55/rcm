package br.com.reconcip.payment;

import br.com.reconcip.payment.entity.PaymentCondition;
import br.com.reconcip.payment.entity.PaymentType;
import br.com.reconcip.payment.repository.PaymentConditionRepository;
import br.com.reconcip.payment.repository.PaymentTypeRepository;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.http.MediaType;
import org.springframework.test.web.servlet.MockMvc;
import org.springframework.test.web.servlet.request.MockMvcRequestBuilders;
import org.springframework.test.web.servlet.result.MockMvcResultMatchers;
import org.springframework.test.web.servlet.setup.MockMvcBuilders;
import org.springframework.web.context.WebApplicationContext;

import static org.junit.jupiter.api.Assertions.assertEquals;

@SpringBootTest
public class PaymentConditionTests {
    private MockMvc client;

    @Autowired
    private PaymentTypeRepository paymentTypeRepository;

    @Autowired
    private PaymentConditionRepository repository;

    @BeforeEach
    public void setUp(WebApplicationContext context) {
        this.client = MockMvcBuilders.webAppContextSetup(context).build();
    }

    @Test
    public void create() throws Exception {
        PaymentType paymentType = new PaymentType();

        paymentType.setName("cash");
        this.paymentTypeRepository.save(paymentType);

        this.client.perform(
                MockMvcRequestBuilders.post("/payment-conditions")
                        .accept(MediaType.APPLICATION_JSON)
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"name\":\"a vista\",\"title\":\"a vista\",\"increment\":0,\"installments\":0,\"paymentType\":\"" + paymentType.getId().toString() + "\"}")
        ).andExpect(MockMvcResultMatchers.status().isCreated());

        assertEquals(1, this.repository.count());
    }

    @Test
    public void validation() throws Exception {
        this.client.perform(
                MockMvcRequestBuilders.post("/payment-conditions")
                        .accept(MediaType.APPLICATION_JSON)
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"name\":\"a vista\",\"title\":\"a vista\",\"increment\":0,\"installments\":0,\"paymentType\":\"134a14c3-a397-4163-b035-9ba97c38792c\"}")
        ).andExpect(MockMvcResultMatchers.status().isBadRequest());
    }

    @Test
    public void update() throws Exception {
        PaymentType paymentType = new PaymentType();

        paymentType.setName("parcelado");
        this.paymentTypeRepository.save(paymentType);

        PaymentCondition condition = new PaymentCondition();
        condition.setName("Ent 30 60");
        condition.setTitle("Ent 30 60");
        condition.setIncrement(10);
        condition.setPaymentType(paymentType);
        condition.setInstallments(3);

        this.repository.save(condition);

        this.client.perform(
                MockMvcRequestBuilders.put("/payment-conditions/" + condition.getId().toString())
                        .accept(MediaType.APPLICATION_JSON)
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"name\":\"Ent 30 60 90\",\"title\":\"Ent 30 60 90\",\"increment\":15,\"installments\":4,\"paymentType\":\"" + paymentType.getId().toString() + "\"}")
        ).andExpect(MockMvcResultMatchers.status().is2xxSuccessful());

        condition = this.repository.findById(condition.getId()).get();

        assertEquals("Ent 30 60 90", condition.getName());
        assertEquals(15, condition.getIncrement());
        assertEquals(4, condition.getInstallments());
    }

    @Test
    public void updateNotFound() throws Exception {
        this.client.perform(
                MockMvcRequestBuilders.put("/payment-conditions/fd487dd7-f522-4e0c-92bf-47bc89ec08dc")
                        .accept(MediaType.APPLICATION_JSON)
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"name\":\"Ent 30 60 90\",\"title\":\"Ent 30 60 90\",\"increment\":15,\"installments\":4,\"paymentType\":\"54696b2c-62e8-45f2-aa70-dfdb05c55053\"}")
        ).andExpect(MockMvcResultMatchers.status().isNotFound());
    }

    @Test
    public void updateInvalidUUID() throws Exception {
        this.client.perform(
                MockMvcRequestBuilders.put("/payment-conditions/somethingotherthanuuid")
                        .accept(MediaType.APPLICATION_JSON)
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"name\":\"Ent 30 60 90\",\"title\":\"Ent 30 60 90\",\"increment\":15,\"installments\":4,\"paymentType\":\"54696b2c-62e8-45f2-aa70-dfdb05c55053\"}")
        ).andExpect(MockMvcResultMatchers.status().isBadRequest());
    }

    @Test
    public void updateInvalidPaymentType() throws Exception {
        PaymentType paymentType = new PaymentType();

        paymentType.setName("parcelado");
        this.paymentTypeRepository.save(paymentType);

        PaymentCondition condition = new PaymentCondition();
        condition.setName("Ent 30 60");
        condition.setTitle("Ent 30 60");
        condition.setIncrement(10);
        condition.setPaymentType(paymentType);
        condition.setInstallments(3);

        this.repository.save(condition);

        this.client.perform(
                MockMvcRequestBuilders.put("/payment-conditions/" + condition.getId().toString())
                        .accept(MediaType.APPLICATION_JSON)
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"name\":\"Ent 30 60 90\",\"title\":\"Ent 30 60 90\",\"increment\":15,\"installments\":4,\"paymentType\":\"54696b2c-62e8-45f2-aa70-dfdb05c55053\"}")
        ).andExpect(MockMvcResultMatchers.status().isBadRequest());

        condition = this.repository.findById(condition.getId()).get();

        assertEquals("Ent 30 60", condition.getName());
        assertEquals(10, condition.getIncrement());
        assertEquals(3, condition.getInstallments());
    }
}
