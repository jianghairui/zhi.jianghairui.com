<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/4/8
 * Time: 17:09
 */
namespace app\api\controller;
use think\Controller;
use think\Db;
use tissue\Token;
class Pay extends Common {

    public function prepay() {
        $device_num = input('param.device_num',0);
        try {
            $exist = Db::table('device')->where('device_num',$device_num)->find();
            if(!$exist) {
                die('操作异常');
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('info',$exist);
        return $this->fetch();
    }

    public function pay() {
        $appid = config('appid');
        $secret = config('app_secret');
        $mch_id = config('mch_id');
        $param = input('param.');
        $query = http_build_query($param);
        if(!isset($_GET['code'])){
            $redirect_uri=urlencode($_SERVER['REQUEST_SCHEME'] . "://".$_SERVER['HTTP_HOST']."/api/pay/pay?" . $query);
            $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=1#wechat_redirect";
            header("Location:".$url);
            exit();
        }else {
            /*-------------获取OPENID开始-------------*/
            $code = $_GET["code"];
            $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . '&secret=' . $secret . '&code=' . $code . '&grant_type=authorization_code';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $get_token_url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            $res = curl_exec($ch);
            curl_close($ch);
            $json_obj = json_decode($res, true);
            $openid = $json_obj['openid'];
            /*-------------获取OPENID结束-------------*/
            try {
                $device_num = $param['device_num'];
                $num = $param['buy_num'];
                $device_exist = Db::table('device')->where('device_num',$device_num)->find();
                if(!$device_exist || !preg_match('/^\d{1}$/', $num) || $num > 5 || $num < 1) {
                    exit('<script>alert("操作异常");document.addEventListener("WeixinJSBridgeReady", function(){ WeixinJSBridge.call("closeWindow"); }, false);</script>');
                }
                if($device_exist['stock'] < 1 || $device_exist['stock'] < $num) {
                    $this->asyn_smtp(['id'=>$device_exist['id']]);
                    exit('<script>alert("数量不足");document.addEventListener("WeixinJSBridgeReady", function(){ WeixinJSBridge.call("closeWindow"); }, false);</script>');
                }
                $total_price = $device_exist['unit_price'] * $num;
                $insert_data['pay_order_sn'] = create_unique_number('');
                $insert_data['device_num'] = $device_num;
                $insert_data['num'] = $num;
                $insert_data['unit_price'] = $device_exist['unit_price'];
                $insert_data['total_price'] = $total_price;
                $insert_data['create_time'] = time();
                Db::table('order')->insert($insert_data);
            } catch (\Exception $e) {
                exit('<script>alert("'.$e->getMessage().'");document.addEventListener("WeixinJSBridgeReady", function(){ WeixinJSBridge.call("closeWindow"); }, false);</script>');
            }
            $arr = [
                'appid' => $appid,
                'mch_id' => $mch_id,
                'nonce_str' => randomkeys(32),
                'sign_type' => 'MD5',
                'body' => '山海文化-文创纸巾',
                'out_trade_no' => $insert_data['pay_order_sn'],
                'total_fee' => floatval($total_price)*100,
                'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
                'notify_url' => $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/api/pay/notify",
                'trade_type' => 'JSAPI',
                'openid' => $openid
            ];
            session('openid',$openid);
            $arr['sign'] = getSign($arr);
            $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
            $xmlData = curl_post_data($url, array2xml($arr));
            $result = xml2array($xmlData);
            /*--------------微信统一下单--------------*/
            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                $prepay['appId'] = $arr['appid'];
                $prepay['timeStamp'] = time();
                $prepay['nonceStr'] = $arr['nonce_str'];
                $prepay['signType'] = $arr['sign_type'];
                $prepay['package'] = 'prepay_id=' . $result['prepay_id'];
                $prepay['paySign'] = getSign($prepay);
            } else {
                exit('<script>alert("'.$result['return_msg'].'");document.addEventListener("WeixinJSBridgeReady", function(){ WeixinJSBridge.call("closeWindow"); }, false);</script>');
            }
        }
        $this->assign('gift',[
            'device_num' => $device_num,
            'num' => $num,
        ]);
        $this->assign('prepay',$prepay);
        return $this->fetch();
    }

    public function notify() {
        //将返回的XML格式的参数转换成php数组格式
        $xml = file_get_contents('php://input');
        $data = xml2array($xml);
        if($data) {
            $this->paylog($this->cmd,var_export($data,true));
            if($data['return_code'] == 'SUCCESS' && $data['result_code'] == 'SUCCESS') {
                $pay_order_sn = $data['out_trade_no'];
                $map = [
                    'pay_order_sn'=>$pay_order_sn,
                    'status' => 0
                ];
                try {
                    $exist = Db::table('order')->where($map)->find();
                    if($exist) {
                        $update_data = [
                            'status' => 1,
                            'pay_time' => time(),
                            'trans_id' => $data['transaction_id'],
                            'openid' => $data['openid']
                        ];
                        Db::table('device')->where('device_num',$exist['device_num'])->setInc('total_price',$exist['total_price']);
                        Db::table('order')->where($map)->update($update_data);
                        $where = [
                            ['device_num','=',$exist['device_num']]
                        ];
                        Db::table('device')->where($where)->setDec('stock',$exist['num']);
                    }
                }catch (\Exception $e) {
                    $this->excep($this->cmd,$e->getMessage());
                    exit(array2xml(['return_code'=>'SUCCESS','return_msg'=>'OK']));
                }
            }
        }
        exit(array2xml(['return_code'=>'SUCCESS','return_msg'=>'OK']));

    }

    public function getGift() {
        $device_num = input('post.device_num','');
        $num = input('post.num',1);
        $openid = session('openid');
        if(!$openid) {
            return ajax('token 无效',-1);
        }
        try {
            $device_exist = Db::table('device')->where('device_num',$device_num)->find();
            if(!$device_exist) {
                return ajax('操作异常',-1);
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
        $tissue = new Token();
        $access_token = $tissue->getAccessToken();
        if($access_token) {
            $url = "http://server.songzb.com:30000/partner/getGift";
            $post_data = [
                "partnerId" => 'shanhaiwenhua',
                "oid" => gen_unique_number(),
                "qrCode" => $device_exist['qrcode'],
                "outCount" => $num,
                "notifyUrl" => ""
            ];
            $json = $this -> curl_post_data($url, json_encode($post_data),['Content-type: application/json', 'accessToken: '.$access_token]);
            $result = json_decode($json,true);
            if($result['resultStatus'] === true) {
                $this->gift_log($this->cmd,var_export($result,true),$device_num);
                return ajax();
            }else {
                $this->gift_log($this->cmd,var_export($result,true),$device_num);
                return ajax('出纸失败,抱歉!发送设备编号给此公众号,我们将在2天内退款',-1);
            }
        }else {
            return ajax('get access_token failed',-1);
        }

    }



//纸巾机出纸单独接口
    private function curl_post_data($url,$curlPost,$Header = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $Header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, 1, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);
        return $data;
    }







}