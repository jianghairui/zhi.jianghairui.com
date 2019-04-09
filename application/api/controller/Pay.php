<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/10/4
 * Time: 10:50
 */
namespace app\api\controller;
use think\Db;
use think\Exception;

class Pay extends Common {

    public function index() {
        halt($_SERVER);
    }

    //订单支付
    public function orderPay() {
        $val['pay_order_sn'] = input('post.pay_order_sn');
        $this->checkPost($val);
        $where = [
            ['pay_order_sn','=',$val['pay_order_sn']],
            ['status','=',0],
            ['uid','=',$this->myinfo['uid']]
        ];
        $app = Factory::payment($this->mp_config);
        try {
            $order_exist = Db::table('mp_order')->where($where)->find();
            if(!$order_exist) {
                return ajax($val['pay_order_sn'],-4);
            }
            $total_price = $order_exist['pay_price'];
            $result = $app->order->unify([
                'body' => '山洞文创产品',
                'out_trade_no' => $val['pay_order_sn'],
                'total_fee' => 1,
//                'total_fee' => floatval($total_price)*100,
                'notify_url' => $this->weburl . 'api/pay/order_notify',
                'trade_type' => 'JSAPI',
                'openid' => $this->myinfo['openid']
            ]);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        if($result['return_code'] != 'SUCCESS' || $result['result_code'] != 'SUCCESS') {
            return ajax($result['err_code_des'],-1);
        }
        try {
            $sign['appId'] = $result['appid'];
            $sign['timeStamp'] = strval(time());
            $sign['nonceStr'] = $result['nonce_str'];
            $sign['signType'] = 'MD5';
            $sign['package'] = 'prepay_id=' . $result['prepay_id'];
            $sign['paySign'] = $this->getSign($sign);
        }catch (\Exception $e) {
            return ajax($e->getMessage(),-1);
        }
        return ajax($sign);
    }

    //订单支付回调接口
    public function order_notify() {
//将返回的XML格式的参数转换成php数组格式
        $xml = file_get_contents('php://input');
        $data = $this->xml2array($xml);
        $this->paylog($this->cmd,var_export($data,true));
        if($data) {
            if($data['return_code'] == 'SUCCESS' && $data['result_code'] == 'SUCCESS') {
                $map = [
                    ['pay_order_sn','=',$data['out_trade_no']],
                    ['status','=',0]
                ];
                try {
                    $order_exist = Db::table('mp_order')->where($map)->find();
                    if($order_exist) {
                        $update_data = [
                            'status' => 1,
                            'trans_id' => $data['transaction_id'],
                            'pay_time' => time()
                        ];
                        Db::table('mp_order')->where('pay_order_sn','=',$data['out_trade_no'])->update($update_data);
                    }
                }catch (\Exception $e) {
                    $this->log($this->cmd,$e->getMessage());
                }
            }
        }
        exit($this->array2xml(['return_code'=>'SUCCESS','return_msg'=>'OK']));
    }


//    public function test() {
//        echo $this->weburl . 'api/pay/order_notify';
//    }






}