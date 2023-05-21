package br.com.reconcip.payment;

import org.springframework.http.HttpStatus;
import org.springframework.validation.BindException;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.ResponseBody;
import org.springframework.web.bind.annotation.ResponseStatus;

import java.util.HashMap;
import java.util.Map;

@ControllerAdvice
public class ApplicationExceptionHandler {
    @ExceptionHandler
    @ResponseBody
    @ResponseStatus(HttpStatus.UNPROCESSABLE_ENTITY)
    public Map<String, Map<String, String>> handleValidationException(BindException e) {
        Map<String, String> errors = new HashMap<>();

        e.getFieldErrors().forEach(error ->
                errors.put(
                        error.getField(),
                        error.getDefaultMessage()
                )
        );

        Map<String, Map<String, String>> response = new HashMap<>();
        response.put("errors", errors);

        return response;
    }

}
