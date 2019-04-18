<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/4/18
 * Time: 15:13
 */
namespace app\admin\controller;
use think\Controller;
use think\Db;

class Common extends Controller {

    protected $cmd;
    protected $token;
    protected $encodingAesKey;
    protected $config;

    public function initialize()
    {
        parent::initialize();
        $this->cmd = request()->controller() . '/' . request()->action();
        $this->token = "shanhaiwenhua2019";
        $this->encodingAesKey = "bGuv54u5UmjFXkZefZ16OPZepcu3y6pbwMBneOKncZo";
        $this->config = [
            'appid' => 'wx1dc64acc9bd9eb09',
            'app_secret' => '18030345ebbbc089f628a5eb1db5cda3',
        ];
    }

    protected function checkSignature($param)
    {
        $token = $this->token;
        $tmpArr = array($token,$param["timestamp"], $param["nonce"]);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode('',$tmpArr);
        $tmpStr = sha1( $tmpStr );
        if($tmpStr === $param["signature"]){
            return true;
        }else{
            return false;
        }
    }
    //生成签名
    protected function getSign($arr)
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
    protected function arrToUrl($str)
    {
        return urldecode($str);
    }

    protected function curl_post_data($url, $curlPost)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, 1, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);
        return $data;
    }

    protected function xml2array($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    protected function array2xml($arr) {
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

    protected function arr2xml($data, $root = true){
        $str="";
        if($root)$str .= "<xml>";
        foreach($data as $key => $val){
            //去掉key中的下标[]
            $key = preg_replace('/\[\d*\]/', '', $key);
            if(is_array($val)){
                $child = $this->arr2xml($val, false);
                $str .= "<$key>$child</$key>";
            }else{
                $str.= "<$key><![CDATA[$val]]></$key>";
            }
        }
        if($root)$str .= "</xml>";
        return $str;
    }

    protected function genOrderSn($letter = '') {
        $time = explode (" ", microtime ());
        $timeArr = explode('.',$time [0]);
        $mtime = array_pop($timeArr);
        $fulltime = $letter.$time[1] . $mtime . mt_rand(100,999);
        return $fulltime;
    }


    protected function weixinLog($cmd = '',$msg = '') {
        $file= ROOT_PATH . '/wechat.txt';
        $text='[Time ' . date('Y-m-d H:i:s') ."]  cmd:".$cmd."\n".$msg."\n---END---" . "\n";
        if(false !== fopen($file,'a+')){
            file_put_contents($file,$text,FILE_APPEND);
        }else{
            echo '创建失败';
        }
    }

    protected function xmllog($cmd = '',$msg = '') {
        $file= ROOT_PATH . '/xmllog.txt';
        $text='[Time ' . date('Y-m-d H:i:s') ."]  cmd:".$cmd."\n".$msg."\n---END---" . "\n";
        if(false !== fopen($file,'a+')){
            file_put_contents($file,$text,FILE_APPEND);
        }else{
            echo '创建失败';
        }
    }

    //Exception日志
    protected function excep($cmd,$str) {
        $file= ROOT_PATH . '/exception.txt';
        $text='[Time ' . date('Y-m-d H:i:s') ."]\ncmd:" .$cmd. "\n" .$str. "\n---END---" . "\n";
        if(false !== fopen($file,'a+')){
            file_put_contents($file,$text,FILE_APPEND);
        }else{
            echo '创建失败';
        }
    }


}