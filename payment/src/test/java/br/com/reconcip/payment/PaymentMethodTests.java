package br.com.reconcip.payment;

import br.com.reconcip.payment.repository.PaymentMethodRepository;
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
public class PaymentMethodTests {
    private MockMvc client;

    @Autowired
    private PaymentMethodRepository repository;

    @BeforeEach
    public void setUp(WebApplicationContext context) {
        this.client = MockMvcBuilders.webAppContextSetup(context).build();
    }

    @Test
    void create() throws Exception {
        this.client.perform(
                MockMvcRequestBuilders.post("/payment-methods")
                        .accept(MediaType.APPLICATION_JSON)
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"name\":\"cash\"}")
        )
                .andExpect(MockMvcResultMatchers.status().isCreated())
                .andExpect(MockMvcResultMatchers.content().json("{\"name\":\"cash\",\"deletedAt\":null}"));

        assertEquals(1, this.repository.count());
    }

    @Test
    void validation() throws Exception {
        this.client.perform(
                        MockMvcRequestBuilders.post("/payment-methods")
                                .accept(MediaType.APPLICATION_JSON)
                                .contentType(MediaType.APPLICATION_JSON)
                                .content("{\"name\":\"\"}")
                )
                .andExpect(MockMvcResultMatchers.status().isBadRequest());
    }
}
