<?php

namespace App\Service;

use App\Exceptions\InsufficientBalanceException;
use App\Models\TransactionLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BalanceService
{
    /**
     * 增加余额
     *
     * @throws InvalidArgumentException
     */
    public function addBalance(User $user, float $amount, string $type, ?string $orderSN = null, ?string $remark = null): TransactionLog
    {
        if (bccomp((string) $amount, '0', 2) <= 0) {
            throw new InvalidArgumentException('金额必须大于0');
        }

        return DB::transaction(function () use ($user, $amount, $type, $orderSN, $remark) {
            $user = User::lockForUpdate()->find($user->id);
            $balanceBefore = $user->balance;
            $user->balance = bcadd((string) $user->balance, (string) $amount, 2);
            $user->save();

            return TransactionLog::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->balance,
                'order_sn' => $orderSN,
                'remark' => $remark,
            ]);
        });
    }

    /**
     * 扣减余额
     *
     * @throws InvalidArgumentException
     * @throws InsufficientBalanceException
     */
    public function deductBalance(User $user, float $amount, string $type, ?string $orderSN = null, ?string $remark = null): TransactionLog
    {
        if (bccomp((string) $amount, '0', 2) <= 0) {
            throw new InvalidArgumentException('金额必须大于0');
        }

        return DB::transaction(function () use ($user, $amount, $type, $orderSN, $remark) {
            $user = User::lockForUpdate()->find($user->id);

            // 使用 bccomp 进行精确比较
            if (bccomp((string) $user->balance, (string) $amount, 2) < 0) {
                throw new InsufficientBalanceException('余额不足');
            }

            $balanceBefore = $user->balance;
            $user->balance = bcsub((string) $user->balance, (string) $amount, 2);
            $user->save();

            return TransactionLog::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->balance,
                'order_sn' => $orderSN,
                'remark' => $remark,
            ]);
        });
    }

    /**
     * 查询余额
     */
    public function getBalance(User $user): float
    {
        return (float) $user->balance;
    }

    /**
     * 检查余额是否充足
     */
    public function hasEnoughBalance(User $user, float $amount): bool
    {
        return bccomp((string) $this->getBalance($user), (string) $amount, 2) >= 0;
    }

    /**
     * 充值
     */
    public function topup(User $user, float $amount, ?string $orderSN = null, ?string $remark = null): TransactionLog
    {
        return $this->addBalance($user, $amount, TransactionLog::TYPE_TOPUP, $orderSN, $remark ?? '账户充值');
    }

    /**
     * 消费（购买商品）
     */
    public function purchase(User $user, float $amount, ?string $orderSN = null, ?string $remark = null): TransactionLog
    {
        return $this->deductBalance($user, $amount, TransactionLog::TYPE_PURCHASE, $orderSN, $remark ?? '余额支付');
    }

    /**
     * 退款
     */
    public function refund(User $user, float $amount, ?string $orderSN = null, ?string $remark = null): TransactionLog
    {
        return $this->addBalance($user, $amount, TransactionLog::TYPE_REFUND, $orderSN, $remark ?? '订单退款');
    }

    /**
     * 调整（管理员手动）
     *
     * @throws InvalidArgumentException
     */
    public function adjustment(User $user, float $amount, ?string $remark = null): TransactionLog
    {
        if (bccomp((string) $amount, '0', 2) === 0) {
            throw new InvalidArgumentException('调整金额不能为0');
        }

        if (bccomp((string) $amount, '0', 2) > 0) {
            return $this->addBalance($user, $amount, TransactionLog::TYPE_ADJUSTMENT, null, $remark ?? '管理员调整（增加）');
        } else {
            return $this->deductBalance($user, abs($amount), TransactionLog::TYPE_ADJUSTMENT, null, $remark ?? '管理员调整（减少）');
        }
    }
}
