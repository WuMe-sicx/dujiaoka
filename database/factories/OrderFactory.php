<?php

namespace Database\Factories;

use App\Models\Goods;
use App\Models\Order;
use App\Models\Pay;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $goodsPrice = $this->faker->randomFloat(2, 10, 200);
        $buyAmount = rand(1, 3);
        $couponDiscount = 0;
        $totalPrice = $goodsPrice * $buyAmount;
        $actualPrice = max(0.01, $totalPrice - $couponDiscount);

        return [
            'order_sn' => strtoupper(Str::random(16)),
            'goods_id' => Goods::factory(),
            'coupon_id' => null,
            'title' => $this->faker->words(3, true),
            'type' => Goods::AUTOMATIC_DELIVERY,
            'goods_price' => $goodsPrice,
            'buy_amount' => $buyAmount,
            'coupon_discount_price' => $couponDiscount,
            'wholesale_discount_price' => 0,
            'total_price' => $totalPrice,
            'actual_price' => $actualPrice,
            'channel_fee' => 0,
            'search_pwd' => $this->faker->regexify('[A-Z0-9]{6}'),
            'email' => $this->faker->safeEmail(),
            'info' => '',
            'pay_id' => Pay::factory(),
            'buy_ip' => $this->faker->ipv4(),
            'trade_no' => strtoupper(Str::random(20)),
            'status' => Order::STATUS_WAIT_PAY,
            'user_id' => null,
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_COMPLETED,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_PENDING,
        ]);
    }

    public function waitPay(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_WAIT_PAY,
        ]);
    }
}
