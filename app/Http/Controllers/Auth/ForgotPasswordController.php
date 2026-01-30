<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * 显示忘记密码页面
     */
    public function showLinkRequestForm()
    {
        return view(dujiaoka_config_get('is_open_theme', 'unicorn') . '.auth.forgot-password');
    }

    /**
     * 发送重置密码链接
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => '请输入邮箱',
            'email.email' => '邮箱格式不正确',
        ]);

        $throttleKey = 'password-reset:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', "请求过于频繁，请{$seconds}秒后重试");
        }

        RateLimiter::hit($throttleKey, 60);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', '重置密码邮件已发送，请查收')
            : back()->with('error', '发送失败，请检查邮箱是否正确');
    }

    /**
     * 显示重置密码表单
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view(dujiaoka_config_get('is_open_theme', 'unicorn') . '.auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * 重置密码
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ], [
            'email.required' => '请输入邮箱',
            'email.email' => '邮箱格式不正确',
            'password.required' => '请输入新密码',
            'password.min' => '密码至少6位',
            'password.confirmed' => '两次密码输入不一致',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', '密码重置成功，请登录')
            : back()->with('error', '重置失败，请重试');
    }
}
