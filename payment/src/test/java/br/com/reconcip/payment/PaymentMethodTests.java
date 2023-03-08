package br.com.reconcip.payment;

import br.com.reconcip.payment.entity.PaymentMethod;
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
import static org.junit.jupiter.api.Assertions.assertNotNull;

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

    @Test
    void update() throws Exception {
        PaymentMethod method = new PaymentMethod();
        method.setName("credit card");
        this.repository.save(method);

        this.client.perform(
                MockMvcRequestBuilders.put("/payment-methods/" + method.getId().toString())
                        .accept(MediaType.APPLICATION_JSON)
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"name\":\"bank deposit\"}")
        )
                .andExpect(MockMvcResultMatchers.status().is2xxSuccessful())
                .andExpect(MockMvcResultMatchers.content().json("{\"id\":\"" + method.getId().toString() + "\",\"name\":\"bank deposit\"}"));
    }

    @Test
    void updateValidation() throws Exception {
        PaymentMethod method = new PaymentMethod();
        method.setName("check");
        this.repository.save(method);

        this.client.perform(
                        MockMvcRequestBuilders.put("/payment-methods/" + method.getId().toString())
                                .accept(MediaType.APPLICATION_JSON)
                                .contentType(MediaType.APPLICATION_JSON)
                                .content("{\"name\":\"\"}")
                )
                .andExpect(MockMvcResultMatchers.status().isBadRequest());
    }

    @Test
    void updateNonExistent() throws Exception {
        this.client.perform(
                MockMvcRequestBuilders.put("/payment-methods/d19eb62c-9a1f-4ed1-95ce-b5c6335c27ce")
                        .accept(MediaType.APPLICATION_JSON)
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"name\":\"bitcoin\"}")
        ).andExpect(MockMvcResultMatchers.status().isNotFound());
    }

    @Test
    void updateInvalidUUID() throws Exception {
        this.client.perform(
                MockMvcRequestBuilders.put("/payment-methods/not-an-uuid")
                        .accept(MediaType.APPLICATION_JSON)
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"name\":\"bitcoin\"}")
        ).andExpect(MockMvcResultMatchers.status().isBadRequest());
    }

    @Test
    void delete() throws Exception {
        PaymentMethod method = new PaymentMethod();
        method.setName("pix");
        this.repository.save(method);

        this.client.perform(
                MockMvcRequestBuilders.delete("/payment-methods/" + method.getId().toString())
        ).andExpect(MockMvcResultMatchers.status().isNoContent());

        method = this.repository.findById(method.getId()).get();
        assertNotNull(method.getDeletedAt());
    }

    @Test
    void deleteNonExistent() throws Exception {
        this.client.perform(
                MockMvcRequestBuilders.delete("/payment-methods/3319372d-e772-4712-bb61-34a53207b96c")
        ).andExpect(MockMvcResultMatchers.status().isNotFound());
    }

    @Test
    void deleteInvalidUUID() throws Exception {
        this.client.perform(
                MockMvcRequestBuilders.delete("/payment-methods/not-an-uuid")
        ).andExpect(MockMvcResultMatchers.status().isBadRequest());
    }
}
