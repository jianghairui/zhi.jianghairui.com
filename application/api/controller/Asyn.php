<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/4/19
 * Time: 16:53
 */
namespace app\api\controller;

use think\Controller;
use my\smtp;
use think\Db;

class Asyn extends Controller {

    public function sendSmtp() {
        $device_id = input('param.id');
        if($_SERVER['REMOTE_ADDR'] == '47.105.162.170') {
            $where = [
                ['id','=',$device_id]
            ];
            try {
                $device_exist = Db::table('device')->where($where)->find();
                //使用163邮箱服务器
                $smtpserver = "smtp.163.com";
//163邮箱服务器端口
                $smtpserverport = 465;
//你的163服务器邮箱账号
                $smtpusermail = "git_smtp@163.com";
//收件人邮箱
                $smtpemailto = $device_exist['email'];

//你的邮箱账号(去掉@163.com)
                $smtpuser = "git_smtp";//你的163邮箱去掉后面的163.com
//你的邮箱密码
                $smtppass = "jiang22513822"; //你的163邮箱SMTP的授权码，千万不要填密码！！！

//邮件主题
                $mailsubject = "纸巾机缺货通知";
//邮件内容
                $mailbody = $device_exist['name'] . "设备号".$device_exist['device_num']."的纸巾机缺纸,请及时补货!";
            } catch(\Exception $e) {
                die($e->getMessage());
            }

//邮件格式（HTML/TXT）,TXT为文本邮件
            $mailtype = "TXT";
//这里面的一个true是表示使用身份验证,否则不使用身份验证.
            $smtp = new smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);
//是否显示发送的调试信息
            $smtp->debug = false;
//发送邮件
            $smtp->sendmail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);
        }

    }

    protected function excep($cmd = '',$msg = '') {
        $file= ROOT_PATH . '/exception.txt';
        $text='[Time ' . date('Y-m-d H:i:s') ."]  cmd:".$cmd."\n".$msg."\n---END---" . "\n";
        if(false !== fopen($file,'a+')){
            file_put_contents($file,$text,FILE_APPEND);
        }else{
            echo '创建失败';
        }
    }






}