<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Pay;
use App\Models\TopupOrder;
use App\Service\BalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TopupController extends Controller
{
    /**
     * 显示充值页面
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', '请先登录');
        }

        $pays = Pay::where('is_open', Pay::STATUS_OPEN)->get();
        $user = Auth::user();

        return view(dujiaoka_config_get('is_open_theme', 'unicorn') . '.topup.create', [
            'pays' => $pays,
            'user' => $user,
        ]);
    }

    /**
     * 创建充值订单
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', '请先登录');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1|max:10000|decimal:0,2',
            'pay_id' => 'required|exists:pays,id',
        ], [
            'amount.required' => '请输入充值金额',
            'amount.numeric' => '金额必须是数字',
            'amount.min' => '最小充值金额为1元',
            'amount.max' => '单笔最大充值金额为10000元',
            'amount.decimal' => '金额最多保留两位小数',
            'pay_id.required' => '请选择支付方式',
            'pay_id.exists' => '支付方式不存在',
        ]);

        $pay = Pay::findOrFail($request->pay_id);

        if ($pay->is_open != Pay::STATUS_OPEN) {
            return back()->with('error', '该支付方式已关闭');
        }

        $topupOrder = TopupOrder::create([
            'order_sn' => TopupOrder::generateOrderSN(),
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'pay_id' => $pay->id,
            'status' => TopupOrder::STATUS_WAIT_PAY,
            'buy_ip' => $request->ip(),
        ]);

        Log::info('充值订单创建', [
            'order_sn' => $topupOrder->order_sn,
            'user_id' => Auth::id(),
            'amount' => $request->amount,
        ]);

        // 跳转到支付网关
        return redirect()->route('pay-gateway', [
            'handle' => $pay->pay_handleroute,
            'payway' => $pay->pay_check,
            'orderSN' => $topupOrder->order_sn,
        ]);
    }

    /**
     * 充值订单列表
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', '请先登录');
        }

        $orders = TopupOrder::where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view(dujiaoka_config_get('is_open_theme', 'unicorn') . '.topup.index', [
            'orders' => $orders,
        ]);
    }

    /**
     * 处理充值回调 (由支付控制器调用)
     */
    public static function handleTopupCallback(string $orderSN, string $tradeNo): bool
    {
        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($orderSN, $tradeNo) {
                // 使用悲观锁获取订单，防止重复处理
                $topupOrder = TopupOrder::lockForUpdate()
                    ->where('order_sn', $orderSN)
                    ->first();

                if (!$topupOrder) {
                    Log::warning('充值订单不存在', ['order_sn' => $orderSN]);
                    return false;
                }

                // 幂等性检查：已完成的订单直接返回成功
                if ($topupOrder->status == TopupOrder::STATUS_COMPLETED) {
                    Log::info('充值订单已处理', ['order_sn' => $orderSN]);
                    return true;
                }

                // 更新订单状态
                $topupOrder->status = TopupOrder::STATUS_COMPLETED;
                $topupOrder->trade_no = $tradeNo;
                $topupOrder->save();

                // 增加用户余额（BalanceService 内部也有事务，但会复用当前事务）
                $balanceService = app(BalanceService::class);
                $balanceService->addBalance(
                    $topupOrder->user,
                    $topupOrder->amount,
                    \App\Models\TransactionLog::TYPE_TOPUP,
                    $topupOrder->order_sn,
                    '在线充值'
                );

                Log::info('充值成功', [
                    'order_sn' => $orderSN,
                    'user_id' => $topupOrder->user_id,
                    'amount' => $topupOrder->amount,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error('充值回调处理失败', [
                'order_sn' => $orderSN,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
