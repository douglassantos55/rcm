package br.com.reconcip.payment;

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
public class PaymentTypeTests {
    private MockMvc client;

    @Autowired
    private PaymentTypeRepository repository;

    @BeforeEach
    void setUp(WebApplicationContext context) {
        this.client = MockMvcBuilders.webAppContextSetup(context).build();
    }

    @Test
    void createPaymentType() throws Exception {
        this.client.perform(
                MockMvcRequestBuilders
                        .post("/payment-types")
                        .content("{\"name\":\"credit card\"}")
                        .contentType(MediaType.APPLICATION_JSON)
                        .accept(MediaType.APPLICATION_JSON)
        ).andExpect(MockMvcResultMatchers.status().isCreated());

        assertEquals(1, this.repository.count());
    }

    @Test
    void validation() throws Exception {
        this.client.perform(
                MockMvcRequestBuilders
                        .post("/payment-types")
                        .content("{\"name\":\"\"}")
                        .contentType(MediaType.APPLICATION_JSON)
                        .accept(MediaType.APPLICATION_JSON)
        ).andExpect(MockMvcResultMatchers.status().isBadRequest());
    }
}
