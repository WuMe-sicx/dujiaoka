<?php
namespace App\Http\Controllers\Pay;


use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use Yansongda\Pay\Pay;

class WepayController extends PayController
{

    public function gateway(string $payway, string $orderSN)
    {
        try {
            // 加载网关
            $this->loadGateWay($orderSN, $payway);
            // v3 配置格式：嵌套在 wechat.default 中
            $config = [
                'wechat' => [
                    'default' => [
                        'app_id' => $this->payGateway->merchant_id,
                        'mch_id' => $this->payGateway->merchant_key,
                        'mch_secret_key' => $this->payGateway->merchant_pem,
                        'mch_secret_cert' => '', // 如果使用证书需配置
                        'mch_public_cert_path' => '', // 如果使用证书需配置
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
                'total_fee' => bcmul($this->order->actual_price, 100, 0),
                'body' => $this->order->order_sn
            ];
            switch ($payway){
                case 'wescan':
                    try{
                        // v3: 后调用，无需传递 config
                        $result = Pay::wechat()->scan($order)->toArray();
                        $result['qr_code'] = $result['code_url'];
                        $result['payname'] =$this->payGateway->pay_name;
                        $result['actual_price'] = (float)$this->order->actual_price;
                        $result['orderid'] = $this->order->order_sn;
                        return $this->render('static_pages/qrpay', $result, __('dujiaoka.scan_qrcode_to_pay'));
                    } catch (\Exception $e) {
                        throw new RuleValidationException(__('dujiaoka.prompt.abnormal_payment_channel') . $e->getMessage());
                    }
                    break;

            }
        } catch (RuleValidationException $exception) {
            return $this->err($exception->getMessage());
        }
    }

    /**
     * 异步通知
     */
    public function notifyUrl()
    {
        $xml = file_get_contents('php://input');
        // 防止 XXE 攻击
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET | LIBXML_NOERROR)), true);
        $oid = $arr['out_trade_no'] ?? '';
        $this->logPaymentCallback('WeChat', '收到回调', ['order_sn' => $oid]);

        $order = $this->orderService->detailOrderSN($oid);
        if (!$order) {
            $this->logPaymentError('WeChat', '订单不存在', ['order_sn' => $oid]);
            return 'error';
        }
        $payGateway = $this->payService->detail($order->pay_id);
        if (!$payGateway) {
            $this->logPaymentError('WeChat', '支付网关不存在', ['order_sn' => $oid, 'pay_id' => $order->pay_id]);
            return 'error';
        }
        if($payGateway->pay_handleroute != '/pay/wepay'){
            $this->logPaymentError('WeChat', '路由不匹配', ['order_sn' => $oid, 'route' => $payGateway->pay_handleroute]);
            return 'error';
        }

        // v3 配置格式
        $config = [
            'wechat' => [
                'default' => [
                    'app_id' => $payGateway->merchant_id,
                    'mch_id' => $payGateway->merchant_key,
                    'mch_secret_key' => $payGateway->merchant_pem,
                    'mch_secret_cert' => '',
                    'mch_public_cert_path' => '',
                ],
            ],
        ];

        // v3: 先配置
        Pay::config($config);

        try{
            // v3: 使用 callback() 代替 verify()
            $result = Pay::wechat()->callback();

            $total_fee = bcdiv($result->total_fee, 100, 2);
            $this->orderProcessService->completedOrder($result->out_trade_no, $total_fee, $result->transaction_id);
            $this->logPaymentCallback('WeChat', '订单完成', ['order_sn' => $result->out_trade_no, 'trade_no' => $result->transaction_id]);
            return 'success';
        } catch (\Exception $exception) {
            $this->logPaymentError('WeChat', '处理失败', ['order_sn' => $oid, 'error' => $exception->getMessage()]);
            return 'fail';
        }
    }

}
