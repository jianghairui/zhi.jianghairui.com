<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/4/18
 * Time: 15:13
 */
namespace app\admin\controller;
use think\Controller;

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
        /*-------山洞公众号------*/
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