<?php

namespace Tests\Unit;

use App\Rules\CpfCnpj;
use App\Rules\CpfCnpjValidator;
use PHPUnit\Framework\TestCase;

class CpfCnpjRuleTest extends TestCase
{
    /** @var bool */
    private $success;

    /** @var \Closure */
    private $callback;

    /** @var CpfCnpj */
    private $rule;

    public function setUp(): void
    {
        $this->success = true;
        $this->rule = new CpfCnpj(new CpfCnpjValidator());

        $this->callback = function () {
            $this->success = false;
        };
    }

    public function valid_cpf_provider()
    {
        return [
            ['885.755.780-49'],
            ['81081902078'],
            [81081902078],
        ];
    }

    public function invalid_cpf_provider()
    {
        return [
            ['000.000.000-00'],
            ['99999999999'],
            ['885.755.780-48'],
            ['81081902077'],
            ['somethingotherthannumbers'],
            [81081902077],
        ];
    }

    public function valid_cnpj_provider()
    {
        return [
            ['89.023.423/0001-30'],
            ['79923564000156'],
            [79923564000156],
        ];
    }

    public function invalid_cnpj_provider()
    {
        return [
            ['00.000.000/0000-00'],
            ['99999999999999'],
            ['89.023.423/0001-31'],
            ['79923564000155'],
            ['somethingotherthannumbers'],
            [79923564000155],
        ];
    }

    /**
     * @dataProvider valid_cpf_provider
     */
    public function test_valid_cpf($input): void
    {
        $this->rule->validate('cpf', $input, $this->callback);
        $this->assertTrue($this->success);
    }

    /**
     * @dataProvider invalid_cpf_provider
     */
    public function test_invalid_cpf($input): void
    {
        $this->rule->validate('cpf', $input, $this->callback);
        $this->assertFalse($this->success);
    }

    /**
     * @dataProvider valid_cnpj_provider
     */
    public function test_valid_cnpj($input): void
    {
        $this->rule->validate('cnpj', $input, $this->callback);
        $this->assertTrue($this->success);
    }

    /**
     * @dataProvider invalid_cnpj_provider
     */
    public function test_invalid_cnpj($input): void
    {
        $this->rule->validate('cnpj', $input, $this->callback);
        $this->assertFalse($this->success);
    }
}
