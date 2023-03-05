package br.com.reconcip.payment;

import br.com.reconcip.payment.entity.PaymentType;
import br.com.reconcip.payment.repository.PaymentConditionRepository;
import br.com.reconcip.payment.repository.PaymentTypeRepository;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.http.MediaType;
import org.springframework.test.web.servlet.MockMvc;
import org.springframework.test.web.servlet.MvcResult;
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
}
