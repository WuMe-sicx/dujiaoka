<?php
namespace App\Http\Controllers\Pay;

use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YipayController extends PayController
{
    /**
     * 构建签名字符串
     */
    private function buildSignString(array $data): string
    {
        ksort($data);
        $signParts = [];
        foreach ($data as $key => $val) {
            if ($key === 'sign' || $key === 'sign_type' || $val === '') {
                continue;
            }
            $signParts[] = "{$key}={$val}";
        }
        return implode('&', $signParts);
    }

    /**
     * 验证回调签名
     */
    private function verifySignature(array $data, string $merchantKey): bool
    {
        if (empty($data['sign'])) {
            return false;
        }
        $signString = $this->buildSignString($data);
        $expectedSign = md5($signString . $merchantKey);
        return hash_equals($expectedSign, $data['sign']);
    }

    public function gateway(string $payway, string $orderSN)
    {
        try {
            $this->loadGateWay($orderSN, $payway);

            $parameter = [
                'pid'          => $this->payGateway->merchant_id,
                'type'         => $payway,
                'out_trade_no' => $this->order->order_sn,
                'return_url'   => route('yipay-return', ['order_id' => $this->order->order_sn]),
                'notify_url'   => url($this->payGateway->pay_handleroute . '/notify_url'),
                'name'         => $this->order->order_sn,
                'money'        => (float)$this->order->actual_price,
                'sign_type'    => 'MD5',
            ];

            $signString = $this->buildSignString($parameter);
            $parameter['sign'] = md5($signString . $this->payGateway->merchant_pem);

            $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='" . e($this->payGateway->merchant_key) . "' method='get'>";
            foreach ($parameter as $key => $val) {
                $sHtml .= "<input type='hidden' name='" . e($key) . "' value='" . e($val) . "'/>";
            }
            $sHtml .= "<input type='submit' value=''></form>";
            $sHtml .= "<script>document.forms['alipaysubmit'].submit();</script>";

            return $sHtml;
        } catch (RuleValidationException $exception) {
            return $this->err($exception->getMessage());
        }
    }

    public function notifyUrl(Request $request)
    {
        $data = $request->all();
        Log::info('YiPay callback received', ['data' => $data]);

        $order = $this->orderService->detailOrderSN($data['out_trade_no'] ?? '');
        if (!$order) {
            Log::warning('YiPay callback: order not found', ['out_trade_no' => $data['out_trade_no'] ?? '']);
            return 'fail';
        }

        $payGateway = $this->payService->detail($order->pay_id);
        if (!$payGateway) {
            Log::warning('YiPay callback: payment gateway not found', ['pay_id' => $order->pay_id]);
            return 'fail';
        }

        if ($payGateway->pay_handleroute != '/pay/yipay') {
            Log::warning('YiPay callback: route mismatch', ['route' => $payGateway->pay_handleroute]);
            return 'fail';
        }

        if (empty($data['trade_no'])) {
            Log::warning('YiPay callback: missing trade_no', ['order_sn' => $order->order_sn]);
            return 'fail';
        }

        if (!$this->verifySignature($data, $payGateway->merchant_pem)) {
            Log::warning('YiPay callback: signature verification failed', ['order_sn' => $order->order_sn]);
            return 'fail';
        }

        try {
            $this->orderProcessService->completedOrder($data['out_trade_no'], $data['money'], $data['trade_no']);
            Log::info('YiPay callback: order completed', ['order_sn' => $order->order_sn]);
            return 'success';
        } catch (\Exception $e) {
            Log::error('YiPay callback: order completion failed', [
                'order_sn' => $order->order_sn,
                'error'    => $e->getMessage(),
            ]);
            return 'fail';
        }
    }

    public function returnUrl(Request $request)
    {
        $oid = $request->get('order_id');
        sleep(2);
        return redirect(url('detail-order-sn', ['orderSN' => $oid]));
    }
}
