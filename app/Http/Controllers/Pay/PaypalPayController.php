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
                    'return_url' => url($this->payGateway->pay_handleroute . '/return_url', ['orderSN' => $this->order->order_sn]),
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
            $orderSN = $request->get('orderSN'); // 我们的订单号

            if (empty($token) || empty($orderSN)) {
                return redirect('/#/?event=order_query_fail');
            }

            // 加载网关配置
            $order = $this->orderService->detailOrderSN($orderSN);
            if (!$order) {
                return redirect('/#/?event=order_query_fail');
            }

            $payGateway = $this->payService->detail($order->pay_id);
            if (!$payGateway || $payGateway->pay_handleroute != '/pay/paypal') {
                return redirect('/#/?event=order_query_fail');
            }

            // 保存到实例变量供 getPayPalClient 使用
            $this->payGateway = $payGateway;
            $client = $this->getPayPalClient();

            // 获取订单详情
            $orderRequest = new OrdersGetRequest($token);
            $orderResponse = $client->execute($orderRequest);

            // 捕获支付
            $captureRequest = new OrdersCaptureRequest($token);
            $captureResponse = $client->execute($captureRequest);

            if ($captureResponse->result->status !== 'COMPLETED') {
                return redirect(url('detail-order-sn', ['orderSN' => $orderSN]));
            }

            // 获取支付信息
            $captureAmount = $captureResponse->result->purchase_units[0]->payments->captures[0]->amount->value;
            $captureId = $captureResponse->result->purchase_units[0]->payments->captures[0]->id;

            // 转换回 CNY
            $cnyAmount = Currency::convert($captureAmount, 'USD', 'CNY');

            // 处理订单完成
            $this->orderProcessService->completedOrder($orderSN, $cnyAmount, $captureId);

            return redirect(url('detail-order-sn', ['orderSN' => $orderSN]));

        } catch (HttpException $e) {
            Log::error('PayPal return error: ' . $e->getMessage());
            return redirect('/#/?event=order_query_fail');
        } catch (\Exception $e) {
            Log::error('PayPal return exception: ' . $e->getMessage());
            return redirect('/#/?event=order_query_fail');
        }
    }

    /**
     * 异步回调
     * 注意：PayPal v2 使用 Webhooks 系统
     * 需要在 PayPal 开发者后台配置 webhook 端点
     */
    public function notifyUrl(Request $request)
    {
        try {
            // PayPal v2 使用 Webhooks，需要单独配置
            // 这里简化处理，实际应该验证 webhook 签名

            $payload = $request->all();
            Log::info('PayPal webhook received', $payload);

            // 验证事件类型
            if (!isset($payload['event_type']) || $payload['event_type'] !== 'PAYMENT.CAPTURE.COMPLETED') {
                return response('OK'); // 忽略其他事件
            }

            $orderSN = $payload['resource']['purchase_units'][0]['reference_id'] ?? '';

            if (empty($orderSN)) {
                Log::error('PayPal webhook: missing reference_id');
                return response('FAIL');
            }

            // 加载订单
            $order = $this->orderService->detailOrderSN($orderSN);
            if (!$order) {
                Log::error('PayPal webhook: order not found - ' . $orderSN);
                return response('FAIL');
            }

            // 获取支付金额和交易ID
            $captureAmount = $payload['resource']['amount']['value'] ?? 0;
            $captureId = $payload['resource']['id'] ?? '';

            // 转换回 CNY
            $cnyAmount = Currency::convert($captureAmount, 'USD', 'CNY');

            // 处理订单完成
            $this->orderProcessService->completedOrder($orderSN, $cnyAmount, $captureId);

            return response('OK');

        } catch (\Exception $e) {
            Log::error('PayPal webhook error: ' . $e->getMessage());
            return response('FAIL');
        }
    }
}
