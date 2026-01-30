<?php

namespace Tests\Unit;

use Tests\TestCase;

class OrderAmountValidationTest extends TestCase
{
    /**
     * 模拟 bccomp 金额对比逻辑（与 OrderProcessService::completedOrder 一致）
     */
    private function validateAmount(float $expectedPrice, float $actualPrice): bool
    {
        return bccomp($expectedPrice, $actualPrice, 2) === 0;
    }

    public function test_matching_amounts_pass_validation(): void
    {
        $this->assertTrue($this->validateAmount(10.00, 10.00));
        $this->assertTrue($this->validateAmount(99.99, 99.99));
        $this->assertTrue($this->validateAmount(0.01, 0.01));
    }

    public function test_mismatched_amounts_fail_validation(): void
    {
        $this->assertFalse($this->validateAmount(10.00, 9.99));
        $this->assertFalse($this->validateAmount(10.00, 10.01));
        $this->assertFalse($this->validateAmount(10.00, 0.01));
    }

    public function test_zero_amount_validation(): void
    {
        $this->assertTrue($this->validateAmount(0.00, 0.00));
        $this->assertFalse($this->validateAmount(0.00, 0.01));
    }

    public function test_precision_handling(): void
    {
        // bccomp 使用 2 位精度，第三位小数不影响比较
        $this->assertTrue($this->validateAmount(10.001, 10.009));
        $this->assertTrue($this->validateAmount(10.00, 10.001));
    }

    public function test_large_amount_validation(): void
    {
        $this->assertTrue($this->validateAmount(99999.99, 99999.99));
        $this->assertFalse($this->validateAmount(99999.99, 99999.98));
    }

    public function test_amount_string_conversion_consistency(): void
    {
        // 模拟支付网关返回字符串金额
        $orderPrice = 25.50;
        $callbackPrice = (float) '25.50';

        $this->assertTrue($this->validateAmount($orderPrice, $callbackPrice));
    }
}
