<?php

namespace App\Http\Controllers\Pay;

use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use Illuminate\Http\Request;
use Yansongda\Pay\Pay;

class AlipayController extends PayController
{

    /**
     * 支付宝支付网关
     *
     * @param string $payway
     * @param string $orderSN
     */
    public function gateway(string $payway, string $orderSN)
    {
        try {
            // 加载网关
            $this->loadGateWay($orderSN, $payway);
            // v3 配置格式：嵌套在 alipay.default 中
            $config = [
                'alipay' => [
                    'default' => [
                        'app_id' => $this->payGateway->merchant_id,
                        'app_secret_cert' => $this->payGateway->merchant_pem,
                        'app_public_cert_path' => '', // 如果使用证书模式需配置
                        'alipay_public_cert_path' => '', // 如果使用证书模式需配置
                        'alipay_root_cert_path' => '', // 如果使用证书模式需配置
                        'notify_url' => url($this->payGateway->pay_handleroute . '/notify_url'),
                        'return_url' => url('detail-order-sn', ['orderSN' => $this->order->order_sn]),
                        'mode' => 'normal', // normal: 正式环境, dev: 沙箱环境
                        'http' => [
                            'timeout' => 10.0,
                            'connect_timeout' => 10.0,
                        ],
                    ],
                ],
            ];

            // v3: 先配置
            Pay::config($config);

            $order = [
                'out_trade_no' => $this->order->order_sn,
                'total_amount' => (float)$this->order->actual_price,
                'subject' => $this->order->order_sn
            ];
            switch ($payway){
                case 'zfbf2f':
                case 'alipayscan':
                    try{
                        // v3: 后调用，无需传递 config
                        $result = Pay::alipay()->scan($order)->toArray();
                        $result['payname'] = $this->order->order_sn;
                        $result['actual_price'] = (float)$this->order->actual_price;
                        $result['orderid'] = $this->order->order_sn;
                        $result['jump_payuri'] = $result['qr_code'];
                        return $this->render('static_pages/qrpay', $result, __('dujiaoka.scan_qrcode_to_pay'));
                    } catch (\Exception $e) {
                        return $this->err(__('dujiaoka.prompt.abnormal_payment_channel') . $e->getMessage());
                    }
                case 'aliweb':
                    try{
                        // v3: 后调用，无需传递 config
                        $result = Pay::alipay()->web($order);
                        return $result;
                    } catch (\Exception $e) {
                        return $this->err(__('dujiaoka.prompt.abnormal_payment_channel') . $e->getMessage());
                    }
                case 'aliwap':
                    try{
                        // v3: 后调用，无需传递 config
                        $result = Pay::alipay()->wap($order);
                        return $result;
                    } catch (\Exception $e) {
                        return $this->err(__('dujiaoka.prompt.abnormal_payment_channel') . $e->getMessage());
                    }
            }
        } catch (RuleValidationException $exception) {
            return $this->err($exception->getMessage());
        }
    }


    /**
     * 异步通知
     */
    public function notifyUrl(Request $request)
    {
        $orderSN = $request->input('out_trade_no');
        $this->logPaymentCallback('Alipay', '收到回调', ['order_sn' => $orderSN]);

        $order = $this->orderService->detailOrderSN($orderSN);
        if (!$order) {
            $this->logPaymentError('Alipay', '订单不存在', ['order_sn' => $orderSN]);
            return 'error';
        }
        $payGateway = $this->payService->detail($order->pay_id);
        if (!$payGateway) {
            $this->logPaymentError('Alipay', '支付网关不存在', ['order_sn' => $orderSN, 'pay_id' => $order->pay_id]);
            return 'error';
        }
        if($payGateway->pay_handleroute != '/pay/alipay'){
            $this->logPaymentError('Alipay', '路由不匹配', ['order_sn' => $orderSN, 'route' => $payGateway->pay_handleroute]);
            return 'fail';
        }

        // v3 配置格式
        $config = [
            'alipay' => [
                'default' => [
                    'app_id' => $payGateway->merchant_id,
                    'app_secret_cert' => $payGateway->merchant_pem,
                    'app_public_cert_path' => '',
                    'alipay_public_cert_path' => '',
                    'alipay_root_cert_path' => '',
                ],
            ],
        ];

        // v3: 先配置
        Pay::config($config);

        try{
            // v3: 使用 callback() 代替 verify()
            $result = Pay::alipay()->callback($request);

            if ($result->trade_status == 'TRADE_SUCCESS' || $result->trade_status == 'TRADE_FINISHED') {
                $this->orderProcessService->completedOrder($result->out_trade_no, $result->total_amount, $result->trade_no);
                $this->logPaymentCallback('Alipay', '订单完成', ['order_sn' => $result->out_trade_no, 'trade_no' => $result->trade_no]);
            }
            return 'success';
        } catch (\Exception $exception) {
            $this->logPaymentError('Alipay', '处理失败', ['order_sn' => $orderSN, 'error' => $exception->getMessage()]);
            return 'fail';
        }
    }



}
