<?php

namespace Tests\Unit\Service;

use App\Exceptions\InsufficientBalanceException;
use App\Models\TransactionLog;
use App\Models\User;
use App\Service\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private BalanceService $balanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->balanceService = new BalanceService();
    }

    public function test_add_balance_increases_user_balance(): void
    {
        $user = User::factory()->create(['balance' => 100.00]);

        $log = $this->balanceService->addBalance($user, 50.00, TransactionLog::TYPE_TOPUP, 'TEST001', '测试充值');

        $user->refresh();
        $this->assertEquals(150.00, $user->balance);
        $this->assertEquals(TransactionLog::TYPE_TOPUP, $log->type);
        $this->assertEquals(50.00, $log->amount);
        $this->assertEquals(100.00, $log->balance_before);
        $this->assertEquals(150.00, $log->balance_after);
    }

    public function test_deduct_balance_decreases_user_balance(): void
    {
        $user = User::factory()->create(['balance' => 100.00]);

        $log = $this->balanceService->deductBalance($user, 30.00, TransactionLog::TYPE_PURCHASE, 'ORDER001', '购买商品');

        $user->refresh();
        $this->assertEquals(70.00, $user->balance);
        $this->assertEquals(TransactionLog::TYPE_PURCHASE, $log->type);
        $this->assertEquals(30.00, $log->amount);
    }

    public function test_deduct_balance_throws_exception_when_insufficient(): void
    {
        $user = User::factory()->create(['balance' => 50.00]);

        $this->expectException(InsufficientBalanceException::class);
        $this->balanceService->deductBalance($user, 100.00, TransactionLog::TYPE_PURCHASE, 'ORDER001', '购买商品');
    }

    public function test_add_balance_throws_exception_for_zero_amount(): void
    {
        $user = User::factory()->create(['balance' => 100.00]);

        $this->expectException(\InvalidArgumentException::class);
        $this->balanceService->addBalance($user, 0, TransactionLog::TYPE_TOPUP);
    }

    public function test_add_balance_throws_exception_for_negative_amount(): void
    {
        $user = User::factory()->create(['balance' => 100.00]);

        $this->expectException(\InvalidArgumentException::class);
        $this->balanceService->addBalance($user, -50.00, TransactionLog::TYPE_TOPUP);
    }

    public function test_get_balance_returns_correct_amount(): void
    {
        $user = User::factory()->create(['balance' => 123.45]);

        $balance = $this->balanceService->getBalance($user);

        $this->assertEquals(123.45, $balance);
    }

    public function test_has_enough_balance_returns_true_when_sufficient(): void
    {
        $user = User::factory()->create(['balance' => 100.00]);

        $this->assertTrue($this->balanceService->hasEnoughBalance($user, 50.00));
        $this->assertTrue($this->balanceService->hasEnoughBalance($user, 100.00));
    }

    public function test_has_enough_balance_returns_false_when_insufficient(): void
    {
        $user = User::factory()->create(['balance' => 50.00]);

        $this->assertFalse($this->balanceService->hasEnoughBalance($user, 100.00));
    }

    public function test_topup_creates_correct_transaction_log(): void
    {
        $user = User::factory()->create(['balance' => 0]);

        $log = $this->balanceService->topup($user, 100.00, 'TOPUP001', '用户充值');

        $this->assertEquals(TransactionLog::TYPE_TOPUP, $log->type);
        $this->assertEquals('TOPUP001', $log->order_sn);
    }

    public function test_purchase_creates_correct_transaction_log(): void
    {
        $user = User::factory()->create(['balance' => 100.00]);

        $log = $this->balanceService->purchase($user, 50.00, 'ORDER001', '购买商品');

        $this->assertEquals(TransactionLog::TYPE_PURCHASE, $log->type);
        $this->assertEquals('ORDER001', $log->order_sn);
    }

    public function test_refund_increases_balance(): void
    {
        $user = User::factory()->create(['balance' => 50.00]);

        $log = $this->balanceService->refund($user, 30.00, 'ORDER001', '订单退款');

        $user->refresh();
        $this->assertEquals(80.00, $user->balance);
        $this->assertEquals(TransactionLog::TYPE_REFUND, $log->type);
    }

    public function test_adjustment_can_increase_or_decrease_balance(): void
    {
        $user = User::factory()->create(['balance' => 100.00]);

        // Positive adjustment
        $log1 = $this->balanceService->adjustment($user, 50.00, '管理员增加余额');
        $user->refresh();
        $this->assertEquals(150.00, $user->balance);
        $this->assertEquals(TransactionLog::TYPE_ADJUSTMENT, $log1->type);

        // Negative adjustment
        $log2 = $this->balanceService->adjustment($user, -30.00, '管理员减少余额');
        $user->refresh();
        $this->assertEquals(120.00, $user->balance);
        $this->assertEquals(TransactionLog::TYPE_ADJUSTMENT, $log2->type);
    }

    public function test_adjustment_throws_exception_for_zero(): void
    {
        $user = User::factory()->create(['balance' => 100.00]);

        $this->expectException(\InvalidArgumentException::class);
        $this->balanceService->adjustment($user, 0, '无效调整');
    }
}
