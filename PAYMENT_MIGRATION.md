# 支付依赖包迁移指南

> **文档版本**: v1.0
> **创建日期**: 2026-01-28
> **适用版本**: Laravel 12 + PHP 8.2
> **当前状态**: 包已升级但代码未适配

---

## 📊 迁移概览

| 包名 | 旧版本 | 新版本 | 难度 | 状态 | 影响文件 |
|------|--------|--------|------|------|----------|
| `jenssegers/agent` | ^2.6 | ^4.x | ⭐ 简单 | ⏳ 待处理 | `AppServiceProvider.php` |
| `stripe/stripe-php` | ^7.84 | ^13.x+ | ⭐⭐ 中等 | ⏳ 待处理 | `StripeController.php` |
| `yansongda/laravel-pay` | ^2.10 | ~3.7.0 | ⭐⭐⭐ 复杂 | ⏳ 待处理 | `AlipayController.php`, `WepayController.php` |
| `paypal/*` | rest-api-sdk | paypal-server-sdk 2.2.0 | ⭐⭐⭐⭐ 非常复杂 | ⏳ 待处理 | `PaypalPayController.php` |
| `simplesoftwareio/simple-qrcode` | 2.0.0 | ^4.2 | ⭐ 简单 | ✅ 已完成 | 二维码生成 |
| `zbrettonye/geetest` | - | ^1.3.1 | ⭐⭐ 中等 | ✅ 已完成 | `HomeController.php`, `DujiaoBoot.php` |
| `xhat/payjs-laravel` | ^1.6 | ^1.6 | ⭐ 简单 | ✅ 无需更改 | `PayjsController.php` |
| `mews/captcha` | ^3.2 | ^3.2 | ⭐ 简单 | ✅ 无需更改 | `VerifyImg.php` |

---

## 1️⃣ jenssegers/agent (^2.6 → ^4.x)

### 📦 包升级

```bash
composer require jenssegers/agent:^4.0
```

### 🔧 代码变化

**v2.6 → v4.x 主要是内部重构，公共 API 基本不变。**

#### ✅ 兼容的用法

```php
// 这些用法在 v4 中完全兼容
use Jenssegers\Agent\Agent;

$agent = new Agent();
$agent->isMobile();
$agent->isTablet();
$agent->isDesktop();
$agent->browser();
$agent->platform();
$agent->device();
```

#### ⚠️ 可能的变化

1. **命名空间保持不变** - `Jenssegers\Agent\Agent`
2. **Facade 保持不变** - `Agent::isMobile()`
3. **新增方法** - 新增了一些设备检测方法，但旧方法仍然支持

#### 📝 需要检查的文件

```bash
# 搜索项目中使用 Agent 的地方
grep -r "use Jenssegers\\Agent" app/
grep -r "Agent::" app/
```

**预期影响**: ✅ **最小** - 通常无需代码更改，只需升级包即可

---

## 2️⃣ stripe/stripe-php (^7.84 → ^13.x+)

### 📦 包升级

```bash
composer require stripe/stripe-php:^13.0
```

### 🔧 核心 API 变化

#### ✅ v7 → v13 兼容性

好消息：**大部分基础 API 保持向后兼容！**

```php
// ✅ 这些在 v13 中仍然有效
\Stripe\Stripe::setApiKey($apiKey);
\Stripe\Charge::create([...]);
\Stripe\Customer::create([...]);
\Stripe\PaymentIntent::create([...]);
```

#### ⚠️ 主要变化

1. **命名空间保持不变** - `\Stripe\*`
2. **API 密钥设置** - `\Stripe\Stripe::setApiKey()` 仍然支持
3. **推荐使用客户端实例** - v13 推荐使用客户端实例，但旧方式仍可用

#### 📄 当前代码分析

**文件**: `app/Http/Controllers/Pay/StripeController.php`

```php
// 第 27 行 - ✅ 兼容，但建议优化
\Stripe\Stripe::setApiKey($this->payGateway->merchant_id);

// 后续使用 Stripe API...
```

#### 🔄 推荐的迁移方案

##### 方案 A: 保持向后兼容（快速）

```php
// 当前代码 - 在 v13 中仍然有效
\Stripe\Stripe::setApiKey($this->payGateway->merchant_id);
$session = \Stripe\Checkout\Session::create([...]);
```

##### 方案 B: 使用新的客户端实例（推荐）

```php
// 新的推荐方式
$stripe = new \Stripe\StripeClient($this->payGateway->merchant_id);
$session = $stripe->checkout->sessions->create([...]);
```

#### 📝 StripeController.php 完整迁移对照

**当前代码（v7）**:
```php
\Stripe\Stripe::setApiKey($this->payGateway->merchant_id);
$amount = bcmul($this->order->actual_price, 100, 2);
$usd = bcmul($this->getUsdCurrency($this->order->actual_price), 100, 2);
```

**迁移选项 1（最小改动）**:
```php
// 仅升级包，代码保持不变
// v13 完全兼容 v7 的这种用法
\Stripe\Stripe::setApiKey($this->payGateway->merchant_id);
```

**迁移选项 2（使用新 API）**:
```php
// 使用新的客户端实例
$stripe = new \Stripe\StripeClient([
    'api_key' => $this->payGateway->merchant_id,
    'stripe_version' => '2023-10-16', // 指定 API 版本
]);

// 使用客户端创建会话
$session = $stripe->checkout->sessions->create([...]);
```

#### ⚠️ 废弃警告

查看 Stripe API 调用是否使用了废弃方法：

```bash
# 检查是否使用了废弃的 API
grep -n "Stripe\\\\Charge::" app/Http/Controllers/Pay/StripeController.php
grep -n "Stripe\\\\Token::" app/Http/Controllers/Pay/StripeController.php
```

**预期影响**: ⭐⭐ **中等** - 可以保持向后兼容快速升级，或采用新 API 获得更好的体验

---

## 3️⃣ yansongda/laravel-pay (v2 → v3)

### 🚨 重要提醒

**包已升级到 v3，但代码仍在使用 v2 API！这会导致支付功能失败！**

### 📦 包状态

```json
// composer.json 中已经是 v3
"yansongda/laravel-pay": "~3.7.0"
```

### 🔧 核心 API 变化

#### ❌ v2 API (旧的 - 当前代码使用)

```php
use Yansongda\Pay\Pay;

// v2 方式 - 配置和调用合并
$result = Pay::alipay($config)->scan($order);
$result = Pay::alipay($config)->web($order);
$result = Pay::alipay($config)->wap($order);

// 验证回调
$pay = Pay::alipay($config);
$data = $pay->verify($request->getContent());
```

#### ✅ v3 API (新的 - 需要迁移到)

```php
use Yansongda\Pay\Pay;

// v3 方式 - 先配置，后调用
Pay::config($config);
$result = Pay::alipay()->scan($order);
$result = Pay::alipay()->web($order);
$result = Pay::alipay()->wap($order);

// 验证回调
Pay::config($config);
$data = Pay::alipay()->callback($request);
```

### 📄 AlipayController.php 迁移

#### 当前代码（v2 API）

**文件**: `app/Http/Controllers/Pay/AlipayController.php`

```php
// 第 24-49 行 - gateway() 方法
public function gateway(string $payway, string $orderSN)
{
    $this->loadGateWay($orderSN, $payway);
    $config = [
        'app_id' => $this->payGateway->merchant_id,
        'ali_public_key' => $this->payGateway->merchant_key,
        'private_key' => $this->payGateway->merchant_pem,
        'notify_url' => url($this->payGateway->pay_handleroute . '/notify_url'),
        'return_url' => url('detail-order-sn', ['orderSN' => $this->order->order_sn]),
    ];
    $order = [
        'out_trade_no' => $this->order->order_sn,
        'total_amount' => (float)$this->order->actual_price,
        'subject' => $this->order->order_sn
    ];

    // ❌ v2 API - 需要修改
    switch ($payway){
        case 'zfbf2f':
        case 'alipayscan':
            $result = Pay::alipay($config)->scan($order)->toArray();
            break;
        case 'zfbpcdp':
            $result = Pay::alipay($config)->web($order);
            break;
        case 'zfbmobile':
            $result = Pay::alipay($config)->wap($order);
            break;
    }
}
```

```php
// 第 88-100 行 - notifyUrl() 方法
public function notifyUrl(Request $request)
{
    $config = [
        'app_id' => $this->payGateway->merchant_id,
        'ali_public_key' => $this->payGateway->merchant_key,
        'private_key' => $this->payGateway->merchant_pem,
    ];

    // ❌ v2 API - 需要修改
    $pay = Pay::alipay($config);
    $data = $pay->verify($request->getContent());
}
```

#### ✅ 迁移后的代码（v3 API）

```php
// gateway() 方法 - v3 版本
public function gateway(string $payway, string $orderSN)
{
    $this->loadGateWay($orderSN, $payway);

    // ✅ v3: 先配置
    $config = [
        'alipay' => [
            'default' => [
                'app_id' => $this->payGateway->merchant_id,
                'app_secret_cert' => $this->payGateway->merchant_pem,
                'app_public_cert_path' => '', // 如果使用证书模式
                'alipay_public_cert_path' => '', // 如果使用证书模式
                'alipay_root_cert_path' => '', // 如果使用证书模式
                'notify_url' => url($this->payGateway->pay_handleroute . '/notify_url'),
                'return_url' => url('detail-order-sn', ['orderSN' => $this->order->order_sn]),
                'mode' => 'normal', // 或 'dev' 沙箱模式
            ],
        ],
    ];

    Pay::config($config);

    $order = [
        'out_trade_no' => $this->order->order_sn,
        'total_amount' => (float)$this->order->actual_price,
        'subject' => $this->order->order_sn
    ];

    // ✅ v3: 然后调用
    switch ($payway){
        case 'zfbf2f':
        case 'alipayscan':
            $result = Pay::alipay()->scan($order)->toArray();
            break;
        case 'zfbpcdp':
            $result = Pay::alipay()->web($order);
            break;
        case 'zfbmobile':
            $result = Pay::alipay()->wap($order);
            break;
    }
}
```

```php
// notifyUrl() 方法 - v3 版本
public function notifyUrl(Request $request)
{
    // ✅ v3: 配置
    $config = [
        'alipay' => [
            'default' => [
                'app_id' => $this->payGateway->merchant_id,
                'app_secret_cert' => $this->payGateway->merchant_pem,
                'notify_url' => url($this->payGateway->pay_handleroute . '/notify_url'),
            ],
        ],
    ];

    Pay::config($config);

    // ✅ v3: 使用 callback() 而不是 verify()
    $data = Pay::alipay()->callback($request);

    // 处理回调数据...
}
```

#### 🔄 配置结构变化对照

| v2 配置键 | v3 配置键 | 说明 |
|----------|----------|------|
| `app_id` | `app_id` | ✅ 保持不变 |
| `ali_public_key` | 移除 | ⚠️ v3 使用证书或自动获取 |
| `private_key` | `app_secret_cert` | ⚠️ 密钥参数名变化 |
| `notify_url` | `notify_url` | ✅ 保持不变 |
| `return_url` | `return_url` | ✅ 保持不变 |
| - | `mode` | 🆕 v3 新增：'normal' 或 'dev' |

#### ⚠️ 注意事项

1. **配置嵌套**: v3 需要将配置包在 `['alipay']['default']` 中
2. **密钥处理**: v3 推荐使用证书模式，或使用 `app_secret_cert` 传递私钥
3. **回调验证**: v3 使用 `callback()` 方法替代 `verify()`
4. **返回值**: `toArray()` 方法仍然可用

### 📄 WepayController.php 迁移

#### 当前代码（v2 API）

**文件**: `app/Http/Controllers/Pay/WepayController.php`

```php
// 第 12-47 行 - gateway() 方法
public function gateway(string $payway, string $orderSN)
{
    $this->loadGateWay($orderSN, $payway);
    $config = [
        'app_id' => $this->payGateway->merchant_id,
        'mch_id' => $this->payGateway->merchant_key,
        'key' => $this->payGateway->merchant_pem,
        'notify_url' => url($this->payGateway->pay_handleroute . '/notify_url'),
    ];
    $order = [
        'out_trade_no' => $this->order->order_sn,
        'total_fee' => bcmul($this->order->actual_price, 100, 0),
        'body' => $this->order->order_sn
    ];

    // ❌ v2 API - 需要修改
    switch ($payway){
        case 'wescan':
            $result = Pay::wechat($config)->scan($order)->toArray();
            break;
    }
}
```

```php
// 第 70-83 行 - notifyUrl() 方法
public function notifyUrl(Request $request)
{
    $config = [
        'app_id' => $this->payGateway->merchant_id,
        'mch_id' => $this->payGateway->merchant_key,
        'key' => $this->payGateway->merchant_pem,
    ];

    // ❌ v2 API - 需要修改
    $pay = Pay::wechat($config);
    $data = $pay->verify($request->getContent());
}
```

#### ✅ 迁移后的代码（v3 API）

```php
// gateway() 方法 - v3 版本
public function gateway(string $payway, string $orderSN)
{
    $this->loadGateWay($orderSN, $payway);

    // ✅ v3: 先配置
    $config = [
        'wechat' => [
            'default' => [
                'app_id' => $this->payGateway->merchant_id,
                'mch_id' => $this->payGateway->merchant_key,
                'mch_secret_key' => $this->payGateway->merchant_pem,
                'mch_secret_cert' => '', // 如果使用证书
                'mch_public_cert_path' => '', // 如果使用证书
                'notify_url' => url($this->payGateway->pay_handleroute . '/notify_url'),
                'mode' => 'normal', // 或 'dev' 沙箱模式
            ],
        ],
    ];

    Pay::config($config);

    $order = [
        'out_trade_no' => $this->order->order_sn,
        'total_fee' => bcmul($this->order->actual_price, 100, 0),
        'body' => $this->order->order_sn
    ];

    // ✅ v3: 然后调用
    switch ($payway){
        case 'wescan':
            $result = Pay::wechat()->scan($order)->toArray();
            $result['qr_code'] = $result['code_url'];
            break;
    }
}
```

```php
// notifyUrl() 方法 - v3 版本
public function notifyUrl(Request $request)
{
    // ✅ v3: 配置
    $config = [
        'wechat' => [
            'default' => [
                'app_id' => $this->payGateway->merchant_id,
                'mch_id' => $this->payGateway->merchant_key,
                'mch_secret_key' => $this->payGateway->merchant_pem,
            ],
        ],
    ];

    Pay::config($config);

    // ✅ v3: 使用 callback() 而不是 verify()
    $data = Pay::wechat()->callback($request);

    // 处理回调数据...
}
```

#### 🔄 配置结构变化对照

| v2 配置键 | v3 配置键 | 说明 |
|----------|----------|------|
| `app_id` | `app_id` | ✅ 保持不变 |
| `mch_id` | `mch_id` | ✅ 保持不变 |
| `key` | `mch_secret_key` | ⚠️ 密钥参数名变化 |
| `notify_url` | `notify_url` | ✅ 保持不变 |
| - | `mode` | 🆕 v3 新增：'normal' 或 'dev' |
| - | `mch_secret_cert` | 🆕 v3 新增：商户证书（可选） |

### 📚 yansongda/pay v3 官方文档

- **文档**: https://pay.yansongda.cn/docs/v3/
- **升级指南**: https://pay.yansongda.cn/docs/v3/upgrade.html
- **支付宝**: https://pay.yansongda.cn/docs/v3/alipay/
- **微信支付**: https://pay.yansongda.cn/docs/v3/wechat/

**预期影响**: ⭐⭐⭐ **复杂** - 需要重构配置和方法调用，建议逐个方法测试

---

## 4️⃣ PayPal SDK (rest-api-sdk → paypal-server-sdk)

### 🚨 重大变更

**旧的 REST API SDK 已完全废弃，必须完整重写控制器！**

### 📦 包状态

```json
// composer.json 中已经替换
"paypal/paypal-server-sdk": "2.2.0"
```

### ❌ 废弃的命名空间（需要删除）

**文件**: `app/Http/Controllers/Pay/PaypalPayController.php`

```php
// ❌ 这些命名空间已废弃，无法使用
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
```

### ✅ 新的 PayPal Server SDK

#### 初始化客户端

```php
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

// 初始化环境
$environment = new ProductionEnvironment(
    $clientId,    // merchant_key
    $clientSecret // merchant_pem
);

// 或沙箱环境
// $environment = new SandboxEnvironment($clientId, $clientSecret);

// 创建客户端
$client = new PayPalHttpClient($environment);
```

#### 创建订单

```php
// 旧的 REST SDK (v1) - ❌ 已废弃
$payer = new Payer();
$payer->setPaymentMethod('paypal');
$item = new Item();
$item->setName($product)->setCurrency('USD')->setQuantity(1)->setPrice($total);
$payment = new Payment();
$payment->setIntent('sale')
    ->setPayer($payer)
    ->setTransactions([$transaction]);
$payment->create($paypal);

// 新的 Server SDK (v2) - ✅ 推荐
$request = new OrdersCreateRequest();
$request->prefer('return=representation');
$request->body = [
    'intent' => 'CAPTURE',
    'purchase_units' => [[
        'amount' => [
            'currency_code' => 'USD',
            'value' => '100.00'
        ],
        'description' => 'Order description'
    ]],
    'application_context' => [
        'return_url' => 'https://example.com/return',
        'cancel_url' => 'https://example.com/cancel',
    ]
];

$response = $client->execute($request);
$orderId = $response->result->id;
$approvalUrl = $response->result->links[1]->href; // 用户支付链接
```

#### 捕获支付

```php
// 旧的 REST SDK (v1) - ❌ 已废弃
$execution = new PaymentExecution();
$execution->setPayerId($request->input('PayerID'));
$payment = Payment::get($paymentId, $paypal);
$payment->execute($execution, $paypal);

// 新的 Server SDK (v2) - ✅ 推荐
$request = new OrdersCaptureRequest($orderId);
$response = $client->execute($request);

if ($response->result->status === 'COMPLETED') {
    // 支付成功
}
```

### 📄 PaypalPayController.php 完整重写

#### 当前代码结构（v1 REST SDK）

**文件**: `app/Http/Controllers/Pay/PaypalPayController.php`

```php
// ❌ 旧的结构 - 需要完全重写
public function gateway(string $payway, string $orderSN)
{
    $this->loadGateWay($orderSN, $payway);

    // 使用旧的 ApiContext
    $paypal = new ApiContext(
        new OAuthTokenCredential(
            $this->payGateway->merchant_key,
            $this->payGateway->merchant_pem
        )
    );

    // 使用旧的 API 对象
    $payer = new Payer();
    $item = new Item();
    $itemList = new ItemList();
    $details = new Details();
    $amount = new Amount();
    $transaction = new Transaction();
    $redirectUrls = new RedirectUrls();
    $payment = new Payment();

    // ...设置并创建支付
}
```

#### ✅ 新的代码结构（v2 Server SDK）

```php
<?php

namespace App\Http\Controllers\Pay;

use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpException;
use Torann\Currency\Facades\Currency;

class PaypalPayController extends PayController
{
    const CURRENCY = 'USD';

    /**
     * 获取 PayPal 客户端
     */
    private function getPayPalClient()
    {
        $clientId = $this->payGateway->merchant_key;
        $clientSecret = $this->payGateway->merchant_pem;

        // 根据配置选择环境
        $environment = new ProductionEnvironment($clientId, $clientSecret);
        // 如果需要沙箱模式: $environment = new SandboxEnvironment($clientId, $clientSecret);

        return new PayPalHttpClient($environment);
    }

    /**
     * 支付网关
     */
    public function gateway(string $payway, string $orderSN)
    {
        try {
            $this->loadGateWay($orderSN, $payway);

            $client = $this->getPayPalClient();

            // 货币转换 (CNY → USD)
            $total = Currency::convert($this->order->actual_price, 'CNY', 'USD');
            $total = number_format($total, 2, '.', '');

            // 创建订单请求
            $request = new OrdersCreateRequest();
            $request->prefer('return=representation');
            $request->body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $this->order->order_sn,
                    'amount' => [
                        'currency_code' => self::CURRENCY,
                        'value' => $total,
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => self::CURRENCY,
                                'value' => $total
                            ]
                        ]
                    ],
                    'items' => [[
                        'name' => $this->order->title,
                        'description' => $this->order->title,
                        'unit_amount' => [
                            'currency_code' => self::CURRENCY,
                            'value' => $total
                        ],
                        'quantity' => '1',
                        'category' => 'DIGITAL_GOODS'
                    ]]
                ]],
                'application_context' => [
                    'brand_name' => dujiaoka_config_get('text_logo'),
                    'landing_page' => 'BILLING',
                    'user_action' => 'PAY_NOW',
                    'return_url' => url($this->payGateway->pay_handleroute . '/return_url'),
                    'cancel_url' => url('detail-order-sn', ['orderSN' => $this->order->order_sn]),
                ]
            ];

            // 执行请求
            $response = $client->execute($request);

            if ($response->statusCode !== 201) {
                throw new RuleValidationException('PayPal order creation failed');
            }

            // 获取批准链接
            $approvalUrl = '';
            foreach ($response->result->links as $link) {
                if ($link->rel === 'approve') {
                    $approvalUrl = $link->href;
                    break;
                }
            }

            if (empty($approvalUrl)) {
                throw new RuleValidationException('PayPal approval URL not found');
            }

            // 重定向到 PayPal
            return redirect($approvalUrl);

        } catch (HttpException $e) {
            Log::error('PayPal HttpException: ' . $e->getMessage());
            throw new RuleValidationException(__('dujiaoka.prompt.abnormal_payment_channel') . ': ' . $e->getMessage());
        } catch (RuleValidationException $exception) {
            return $this->err($exception->getMessage());
        }
    }

    /**
     * 同步回调
     */
    public function returnUrl(Request $request)
    {
        try {
            $token = $request->get('token'); // PayPal Order ID

            if (empty($token)) {
                return redirect('/#/?event=order_query_fail');
            }

            $this->loadGateWay('', 'paypal');
            $client = $this->getPayPalClient();

            // 获取订单详情
            $orderRequest = new OrdersGetRequest($token);
            $orderResponse = $client->execute($orderRequest);

            // 捕获支付
            $captureRequest = new OrdersCaptureRequest($token);
            $captureResponse = $client->execute($captureRequest);

            if ($captureResponse->result->status !== 'COMPLETED') {
                return redirect('/#/?event=order_query_fail');
            }

            // 获取订单号
            $orderSN = $captureResponse->result->purchase_units[0]->reference_id;

            return redirect(url('detail-order-sn', ['orderSN' => $orderSN]));

        } catch (HttpException $e) {
            Log::error('PayPal return error: ' . $e->getMessage());
            return redirect('/#/?event=order_query_fail');
        }
    }

    /**
     * 异步回调
     */
    public function notifyUrl(Request $request)
    {
        try {
            // PayPal v2 使用 Webhooks，需要单独配置
            // 这里简化处理，实际应该验证 webhook 签名

            $payload = $request->all();
            Log::info('PayPal webhook received', $payload);

            // 验证事件类型
            if ($payload['event_type'] !== 'PAYMENT.CAPTURE.COMPLETED') {
                return response('OK');
            }

            $orderSN = $payload['resource']['purchase_units'][0]['reference_id'] ?? '';

            if (empty($orderSN)) {
                Log::error('PayPal webhook: missing reference_id');
                return response('FAIL');
            }

            // 加载订单
            $this->loadGateWay($orderSN, 'paypal');

            // 处理订单完成
            $this->completeOrder($orderSN, $payload['id']);

            return response('OK');

        } catch (\Exception $e) {
            Log::error('PayPal webhook error: ' . $e->getMessage());
            return response('FAIL');
        }
    }
}
```

### 🔄 API 对照表

| v1 REST SDK | v2 Server SDK | 说明 |
|-------------|---------------|------|
| `ApiContext` | `PayPalHttpClient` | 客户端初始化 |
| `OAuthTokenCredential` | `ProductionEnvironment` / `SandboxEnvironment` | 认证方式 |
| `Payment::create()` | `OrdersCreateRequest` | 创建支付 |
| `Payment::execute()` | `OrdersCaptureRequest` | 执行支付 |
| `Payment::get()` | `OrdersGetRequest` | 获取订单 |
| `Payer`, `Item`, `Amount` 等 | 数组配置 | 对象 → 数组 |
| `$payment->getApprovalLink()` | `$response->result->links[1]->href` | 获取支付链接 |

### 📚 PayPal Server SDK 文档

- **GitHub**: https://github.com/paypal/Checkout-PHP-SDK
- **Orders API**: https://developer.paypal.com/docs/api/orders/v2/
- **Webhooks**: https://developer.paypal.com/docs/api/webhooks/v1/

**预期影响**: ⭐⭐⭐⭐ **非常复杂** - 需要完全重写控制器，建议单独测试

---

## 🛠️ 升级执行顺序

### 阶段 1: 简单升级（低风险）

1. ✅ **jenssegers/agent** (10 分钟)
   ```bash
   composer require jenssegers/agent:^4.0
   # 测试：检查 Agent facade 是否正常工作
   ```

2. ✅ **stripe/stripe-php** (30 分钟)
   ```bash
   composer require stripe/stripe-php:^13.0
   # 选择迁移方案：保持向后兼容 或 使用新 API
   # 测试：创建测试订单，验证 Stripe 支付流程
   ```

### 阶段 2: 中等难度（中等风险）

3. ✅ **AlipayController** (2-4 小时)
   - 重构 `gateway()` 方法配置
   - 重构 `notifyUrl()` 方法回调验证
   - 测试：扫码支付、电脑网站支付、手机网站支付

4. ✅ **WepayController** (2-4 小时)
   - 重构 `gateway()` 方法配置
   - 重构 `notifyUrl()` 方法回调验证
   - 测试：微信扫码支付

### 阶段 3: 高难度（高风险）

5. ✅ **PaypalPayController** (4-8 小时)
   - 完全重写控制器
   - 实现 Orders API v2
   - 配置 Webhooks
   - 测试：创建订单、支付、回调

### 阶段 4: 全面测试

6. ✅ **集成测试** (2-4 小时)
   - 测试所有支付网关的完整流程
   - 验证订单状态更新
   - 验证异步回调处理
   - 验证邮件通知
   - 验证 API Hooks

---

## ✅ 测试清单

### 每个支付网关需要测试

- [ ] 创建订单成功
- [ ] 跳转到支付页面
- [ ] 支付成功后异步回调
- [ ] 支付成功后同步跳转
- [ ] 订单状态正确更新为 COMPLETED
- [ ] 卡密正确发货（如果是自动发货商品）
- [ ] 邮件通知发送成功
- [ ] 后台订单记录正确

### 通用测试

```bash
# 清除缓存
php artisan cache:clear
php artisan config:clear

# 启动队列处理
php artisan queue:work

# 检查依赖
composer show | grep -E "stripe|yansongda|paypal|jenssegers"

# 运行单元测试
php vendor/bin/phpunit
```

---

## 🔄 回滚方案

如果升级出现问题，可以快速回滚：

```bash
# 回滚到 Laravel 11
git checkout v2.5.0-laravel11
composer install

# 或仅回滚特定包
composer require yansongda/laravel-pay:^2.10
composer require stripe/stripe-php:^7.84
composer require paypal/rest-api-sdk-php:^1.14

# 清除缓存
php artisan cache:clear
php artisan config:clear
```

---

## 📞 技术支持

如果遇到问题：

1. **yansongda/pay**: https://pay.yansongda.cn/docs/v3/
2. **Stripe PHP**: https://stripe.com/docs/api?lang=php
3. **PayPal SDK**: https://developer.paypal.com/docs/checkout/
4. **DuJiaoKa Issues**: https://github.com/assimon/dujiaoka/issues

---

## 📝 变更日志

| 日期 | 版本 | 变更内容 |
|------|------|----------|
| 2026-01-28 | v1.0 | 创建初始迁移文档 |

---

## ⚠️ 重要提醒

1. **备份数据库** - 升级前务必备份生产数据库
2. **先在开发环境测试** - 不要直接在生产环境升级
3. **逐个升级** - 不要一次性升级所有包
4. **保持队列运行** - 确保 `php artisan queue:work` 始终运行
5. **监控日志** - 升级后密切监控 `storage/logs/laravel.log`
6. **测试支付** - 使用测试账号和小额订单验证

---

**文档维护**: 请在每次升级后更新此文档，记录实际遇到的问题和解决方案。
