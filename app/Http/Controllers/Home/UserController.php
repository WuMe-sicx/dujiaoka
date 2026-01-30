<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TransactionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * 用户中心
     */
    public function dashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $stats = [
            'balance' => $user->balance,
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'completed_orders' => Order::where('user_id', $user->id)
                ->where('status', Order::STATUS_COMPLETED)
                ->count(),
            'total_spent' => Order::where('user_id', $user->id)
                ->where('status', Order::STATUS_COMPLETED)
                ->sum('actual_price'),
        ];

        $recentOrders = Order::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $recentTransactions = TransactionLog::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return view(dujiaoka_config_get('is_open_theme', 'unicorn') . '.user.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * 我的订单
     */
    public function orders()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $orders = Order::where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view(dujiaoka_config_get('is_open_theme', 'unicorn') . '.user.orders', [
            'orders' => $orders,
        ]);
    }

    /**
     * 交易记录
     */
    public function transactions()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $transactions = TransactionLog::where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view(dujiaoka_config_get('is_open_theme', 'unicorn') . '.user.transactions', [
            'transactions' => $transactions,
        ]);
    }

    /**
     * 个人设置
     */
    public function settings()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view(dujiaoka_config_get('is_open_theme', 'unicorn') . '.user.settings', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * 更新个人资料
     */
    public function updateProfile(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'name' => 'required|string|max:50',
        ], [
            'name.required' => '请输入用户名',
            'name.max' => '用户名不能超过50个字符',
        ]);

        $user = Auth::user();
        $user->name = $request->name;
        $user->save();

        return back()->with('success', '资料更新成功');
    }

    /**
     * 修改密码
     */
    public function updatePassword(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => '请输入当前密码',
            'password.required' => '请输入新密码',
            'password.min' => '新密码至少6位',
            'password.confirmed' => '两次密码输入不一致',
        ]);

        $user = Auth::user();

        if (!\Hash::check($request->current_password, $user->password)) {
            return back()->with('error', '当前密码错误');
        }

        $user->password = \Hash::make($request->password);
        $user->save();

        return back()->with('success', '密码修改成功');
    }
}
