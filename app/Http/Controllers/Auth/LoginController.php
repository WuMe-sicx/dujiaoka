<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    /**
     * 显示登录页面
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('user.dashboard');
        }

        return view(dujiaoka_config_get('is_open_theme', 'unicorn') . '.auth.login');
    }

    /**
     * 处理登录请求
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => '请输入邮箱',
            'email.email' => '邮箱格式不正确',
            'password.required' => '请输入密码',
            'password.min' => '密码至少6位',
        ]);

        $throttleKey = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', "登录尝试次数过多，请{$seconds}秒后重试");
        }

        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ], $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            return redirect()->intended(route('user.dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->with('error', '邮箱或密码错误');
    }

    /**
     * 退出登录
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
