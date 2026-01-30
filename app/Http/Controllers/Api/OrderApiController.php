<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderApiController extends BaseApiController
{
    /**
     * 通过订单号查询
     */
    public function queryBySn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_sn' => 'required|string|max:32',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $order = Order::where('order_sn', $request->input('order_sn'))
            ->with(['goods:id,gd_name,type', 'pay:id,pay_name'])
            ->first();

        if (!$order) {
            return $this->notFound('订单不存在');
        }

        return $this->success($this->formatOrder($order));
    }

    /**
     * 通过邮箱查询
     */
    public function queryByEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'search_pwd' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $query = Order::where('email', $request->input('email'))
            ->with(['goods:id,gd_name,type', 'pay:id,pay_name']);

        if ($request->filled('search_pwd')) {
            $query->where('search_pwd', $request->input('search_pwd'));
        }

        $orders = $query->orderBy('id', 'desc')
            ->paginate($request->input('per_page', 10));

        $orders->getCollection()->transform(fn ($order) => $this->formatOrder($order));

        return $this->paginated($orders);
    }

    /**
     * 检查订单状态
     */
    public function checkStatus(string $orderSN): JsonResponse
    {
        $order = Order::where('order_sn', $orderSN)->first(['order_sn', 'status']);

        if (!$order) {
            return $this->notFound('订单不存在');
        }

        return $this->success([
            'order_sn' => $order->order_sn,
            'status' => $order->status,
            'status_text' => Order::getStatusMap()[$order->status] ?? '未知',
        ]);
    }

    private function formatOrder(Order $order): array
    {
        return [
            'order_sn' => $order->order_sn,
            'title' => $order->title,
            'type' => $order->type,
            'goods_name' => $order->goods?->gd_name,
            'pay_name' => $order->pay?->pay_name,
            'goods_price' => $order->goods_price,
            'buy_amount' => $order->buy_amount,
            'total_price' => $order->total_price,
            'actual_price' => $order->actual_price,
            // channel_fee 是内部数据，不暴露给前端
            'email' => $this->maskEmail($order->email),
            'status' => $order->status,
            'status_text' => Order::getStatusMap()[$order->status] ?? '未知',
            'info' => $order->status == Order::STATUS_COMPLETED ? $order->info : null,
            'created_at' => $order->created_at?->toDateTimeString(),
        ];
    }

    /**
     * 邮箱脱敏处理
     */
    private function maskEmail(?string $email): ?string
    {
        if (!$email) {
            return null;
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];

        if (strlen($name) <= 2) {
            return $name[0] . '***@' . $domain;
        }

        return substr($name, 0, 2) . '***@' . $domain;
    }
}
