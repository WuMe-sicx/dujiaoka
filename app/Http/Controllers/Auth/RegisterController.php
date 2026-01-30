<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class RegisterController extends Controller
{
    /**
     * 显示注册页面
     */
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect()->route('user.dashboard');
        }

        // 检查是否开放注册
        if (!dujiaoka_config_get('is_open_register', true)) {
            return redirect('/')->with('error', '暂不开放注册');
        }

        return view(dujiaoka_config_get('is_open_theme', 'unicorn') . '.auth.register');
    }

    /**
     * 处理注册请求
     */
    public function register(Request $request)
    {
        if (!dujiaoka_config_get('is_open_register', true)) {
            return redirect('/')->with('error', '暂不开放注册');
        }

        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ], [
            'name.required' => '请输入用户名',
            'name.max' => '用户名不能超过50个字符',
            'email.required' => '请输入邮箱',
            'email.email' => '邮箱格式不正确',
            'email.unique' => '该邮箱已被注册',
            'password.required' => '请输入密码',
            'password.min' => '密码至少6位',
            'password.confirmed' => '两次密码输入不一致',
        ]);

        $throttleKey = 'register:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', "注册尝试次数过多，请{$seconds}秒后重试");
        }

        RateLimiter::hit($throttleKey, 300);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'balance' => 0,
        ]);

        Auth::login($user);

        return redirect()->route('user.dashboard')->with('success', '注册成功');
    }
}
