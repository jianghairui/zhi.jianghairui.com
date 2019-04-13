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
class Pay extends Controller {

    protected $config = [];
    protected $weburl = '';
    protected $cmd = '';

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->cmd = request()->controller() . '/' . request()->action();
        $this->weburl = 'www.caves.vip';
        $this->config = [
            'appid' => 'wx1ef69d837c12a709',
            'app_secret' => 'b95d230bfbd56c0e1944e7f5d36b01f2',
            'mch_id' => '1251831001',
            'appkey' => '123654aaaZZZcaifu888aaazzzaaazzz',
        ];
    }

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
        $appid = $this->config['appid'];
        $secret = $this->config['app_secret'];
        $mch_id = $this->config['mch_id'];
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
                $total_price = $device_exist['unit_price'] * $num;
                $insert_data['pay_order_sn'] = $this->genPayOrderSn('');
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
                'nonce_str' => $this->randomkeys(32),
                'sign_type' => 'MD5',
                'body' => '山海文化-文创纸巾',
                'out_trade_no' => $insert_data['pay_order_sn'],
                'total_fee' => floatval($total_price)*100,
                'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
                'notify_url' => $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/api/pay/notify",
                'trade_type' => 'JSAPI',
                'openid' => $openid
            ];
            $arr['sign'] = $this->getSign($arr);
            $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
            $result = $this->curl_post_datas($url, $this->array2xml($arr));
            /*--------------微信统一下单--------------*/
            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                $prepay['appId'] = $arr['appid'];
                $prepay['timeStamp'] = time();
                $prepay['nonceStr'] = $arr['nonce_str'];
                $prepay['signType'] = $arr['sign_type'];
                $prepay['package'] = 'prepay_id=' . $result['prepay_id'];
                $prepay['paySign'] = $this->getSign($prepay);
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
        $data = $this->xml2array($xml);
        if($data) {
            $this->log('notify',var_export($data,true));
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
                    }
                }catch (\Exception $e) {
                    $this->excep($this->cmd,$e->getMessage());
                    exit($this->array2xml(['return_code'=>'SUCCESS','return_msg'=>'OK']));
                }
            }
        }
        exit($this->array2xml(['return_code'=>'SUCCESS','return_msg'=>'OK']));

    }

    public function getGift() {
        $device_num = input('post.device_num','');
        $num = input('post.num',1);
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
                $this->gift_log($this->cmd,var_export($result,true));
                return ajax();
            }else {
                $this->gift_log($this->cmd,var_export($result,true));
                return ajax('出纸失败',-1);
            }
        }else {
            return ajax('get access_token failed',-1);
        }

    }










    private function log($cmd = '',$msg = '') {
        $file= ROOT_PATH . '/notify.txt';
        $text='[Time ' . date('Y-m-d H:i:s') ."]  cmd:".$cmd."\n".$msg."\n---END---" . "\n";
        if(false !== fopen($file,'a+')){
            file_put_contents($file,$text,FILE_APPEND);
        }else{
            echo '创建失败';
        }
    }

    private function gift_log($cmd = '',$msg = '') {
        $file= ROOT_PATH . '/gift_log.txt';
        $text='[Time ' . date('Y-m-d H:i:s') ."]  cmd:".$cmd."\n".$msg."\n---END---" . "\n";
        if(false !== fopen($file,'a+')){
            file_put_contents($file,$text,FILE_APPEND);
        }else{
            echo '创建失败';
        }
    }

    private function excep($cmd = '',$msg = '') {
        $file= ROOT_PATH . '/exception.txt';
        $text='[Time ' . date('Y-m-d H:i:s') ."]  cmd:".$cmd."\n".$msg."\n---END---" . "\n";
        if(false !== fopen($file,'a+')){
            file_put_contents($file,$text,FILE_APPEND);
        }else{
            echo '创建失败';
        }
    }
//生成签名
    private function getSign($arr)
    {
        //去除数组中的空值
        $arr = array_filter($arr);
        //如果数组中有签名删除签名
        if(isset($arr['sing']))
        {
            unset($arr['sing']);
        }
        //按照键名字典排序
        ksort($arr);
        //生成URL格式的字符串
        $str = http_build_query($arr)."&key=" . $this->config['appkey'];
        $str = $this->arrToUrl($str);
        return  strtoupper(md5($str));
    }
    //URL解码为中文
    private function arrToUrl($str)
    {
        return urldecode($str);
    }

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

    private function curl_post_datas($url, $curlPost,$userCert = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if($userCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $this->config['sslcert_path']);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $this->config['sslkey_path']);
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, 1, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);
        $arr = $this->xml2array($data);
        return $arr;
    }

    private function array2xml($arr) {
        if(!is_array($arr) || count($arr) <= 0) {
            return false;
        }
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    private function xml2array($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    private function genPayOrderSn($letter = '') {
        $time = explode (" ", microtime ());
        $timeArr = explode('.',$time [0]);
        $mtime = array_pop($timeArr);
        $fulltime = $letter.$time[1] . $mtime . mt_rand(100,999);
        return $fulltime;
    }

    private function randomkeys($length) {
        $returnStr='';
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for($i = 0; $i < $length; $i ++) {
            $returnStr .= $pattern {mt_rand ( 0, 61 )};
        }
        return $returnStr;
    }


}