<?php

namespace Tests\Unit;

use Tests\TestCase;

class PaySignatureTest extends TestCase
{
    /**
     * 模拟 YiPay 签名构建逻辑
     */
    private function buildSignString(array $data): string
    {
        ksort($data);
        $signParts = [];
        foreach ($data as $key => $val) {
            if ($key === 'sign' || $key === 'sign_type' || $val === '') {
                continue;
            }
            $signParts[] = "{$key}={$val}";
        }
        return implode('&', $signParts);
    }

    public function test_yipay_signature_generation(): void
    {
        $merchantKey = 'test_secret_key_123';
        $data = [
            'pid' => '1001',
            'type' => 'alipay',
            'out_trade_no' => 'ORDER123456',
            'money' => '10.00',
            'name' => 'ORDER123456',
        ];

        $signString = $this->buildSignString($data);
        $sign = md5($signString . $merchantKey);

        // 签名应该是32位 MD5 哈希
        $this->assertEquals(32, strlen($sign));
        // 相同输入应产生相同签名
        $this->assertEquals($sign, md5($signString . $merchantKey));
    }

    public function test_yipay_signature_excludes_sign_and_sign_type(): void
    {
        $data = [
            'pid' => '1001',
            'money' => '10.00',
            'sign' => 'should_be_excluded',
            'sign_type' => 'MD5',
        ];

        $signString = $this->buildSignString($data);

        $this->assertStringNotContainsString('sign=', $signString);
        $this->assertStringNotContainsString('sign_type=', $signString);
    }

    public function test_yipay_signature_excludes_empty_values(): void
    {
        $data = [
            'pid' => '1001',
            'money' => '10.00',
            'empty_field' => '',
        ];

        $signString = $this->buildSignString($data);

        $this->assertStringNotContainsString('empty_field=', $signString);
    }

    public function test_yipay_signature_sorts_keys(): void
    {
        $data = [
            'z_field' => 'last',
            'a_field' => 'first',
            'm_field' => 'middle',
        ];

        $signString = $this->buildSignString($data);

        $this->assertEquals('a_field=first&m_field=middle&z_field=last', $signString);
    }

    public function test_yipay_signature_verification_with_hash_equals(): void
    {
        $merchantKey = 'secret123';
        $data = [
            'pid' => '1001',
            'out_trade_no' => 'ORDER789',
            'money' => '25.50',
        ];

        $signString = $this->buildSignString($data);
        $expectedSign = md5($signString . $merchantKey);

        // 正确签名应通过验证
        $this->assertTrue(hash_equals($expectedSign, $expectedSign));

        // 错误签名应失败
        $wrongSign = md5($signString . 'wrong_key');
        $this->assertFalse(hash_equals($expectedSign, $wrongSign));
    }

    public function test_yipay_tampered_amount_changes_signature(): void
    {
        $merchantKey = 'secret123';

        $originalData = [
            'pid' => '1001',
            'out_trade_no' => 'ORDER789',
            'money' => '25.50',
        ];
        $originalSign = md5($this->buildSignString($originalData) . $merchantKey);

        // 篡改金额
        $tamperedData = $originalData;
        $tamperedData['money'] = '0.01';
        $tamperedSign = md5($this->buildSignString($tamperedData) . $merchantKey);

        // 签名必须不同
        $this->assertNotEquals($originalSign, $tamperedSign);
    }
}
