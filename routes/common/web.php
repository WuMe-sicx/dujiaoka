<?php
/**
 * The file was created by Assimon.
 *
 * @author    assimon<ashang@utf8.hk>
 * @copyright assimon<ashang@utf8.hk>
 * @link      http://utf8.hk/
 */
use Illuminate\Support\Facades\Route;


Route::group(['middleware' => ['dujiaoka.boot'],'namespace' => 'Home'], function () {
    // 首页
    Route::get('/', 'HomeController@index');
    // 极验效验
    Route::get('check-geetest', 'HomeController@geetest');
    // 商品详情 (支持POST用于访问密码验证)
    Route::match(['get', 'post'], 'buy/{id}', 'HomeController@buy');
    // 提交订单
    Route::post('create-order', 'OrderController@createOrder')->middleware('throttle:10,1');
    // 结算页
    Route::get('bill/{orderSN}', 'OrderController@bill');
    // 通过订单号详情页
    Route::get('detail-order-sn/{orderSN}', 'OrderController@detailOrderSN')->middleware('throttle:20,1');
    // 订单查询页
    Route::get('order-search', 'OrderController@orderSearch');
    // 检查订单状态
    Route::get('check-order-status/{orderSN}', 'OrderController@checkOrderStatus');
    // 通过订单号查询
    Route::post('search-order-by-sn', 'OrderController@searchOrderBySN')->middleware('throttle:10,1');
    // 通过邮箱查询
    Route::post('search-order-by-email', 'OrderController@searchOrderByEmail')->middleware('throttle:10,1');
    // 通过浏览器查询
    Route::post('search-order-by-browser', 'OrderController@searchOrderByBrowser')->middleware('throttle:10,1');
});

Route::group(['middleware' => ['install.check'],'namespace' => 'Home'], function () {
    // 安装
    Route::get('install', 'HomeController@install');
    // 执行安装
    Route::post('do-install', 'HomeController@doInstall');
});

// 用户认证路由
Route::group(['middleware' => ['dujiaoka.boot'], 'namespace' => 'Auth'], function () {
    // 登录
    Route::get('login', 'LoginController@showLoginForm')->name('login');
    Route::post('login', 'LoginController@login');
    Route::post('logout', 'LoginController@logout')->name('logout');

    // 注册
    Route::get('register', 'RegisterController@showRegistrationForm')->name('register');
    Route::post('register', 'RegisterController@register');

    // 忘记密码
    Route::get('forgot-password', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('forgot-password', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('reset-password/{token}', 'ForgotPasswordController@showResetForm')->name('password.reset');
    Route::post('reset-password', 'ForgotPasswordController@reset')->name('password.update');
});

// 用户中心路由
Route::group(['middleware' => ['dujiaoka.boot', 'auth'], 'namespace' => 'Home', 'prefix' => 'user'], function () {
    Route::get('/', 'UserController@dashboard')->name('user.dashboard');
    Route::get('orders', 'UserController@orders')->name('user.orders');
    Route::get('transactions', 'UserController@transactions')->name('user.transactions');
    Route::get('settings', 'UserController@settings')->name('user.settings');
    Route::post('update-profile', 'UserController@updateProfile')->name('user.update-profile');
    Route::post('update-password', 'UserController@updatePassword')->name('user.update-password');

    // 充值
    Route::get('topup', 'TopupController@create')->name('user.topup');
    Route::post('topup', 'TopupController@store')->name('user.topup.store');
    Route::get('topup/history', 'TopupController@index')->name('user.topup.history');
});

