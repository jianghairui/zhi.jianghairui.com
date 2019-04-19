<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/4/19
 * Time: 16:53
 */
namespace app\api\controller;

use think\Controller;
use my\AliSmtp;
class Test extends Controller {

    public function index() {
        $mailto='1873645345@qq.com';
        $mailsubject="测试邮件";
        $mailbody='这里是邮件内容';
        $smtpserver     = "smtp.mxhichina.com";
        $smtpserverport = 25;
        $smtpusermail   = "boss@jianghairui.com";
        $smtpuser       = "boss@jianghairui.com";
        $smtppass       = "Boss22513822";
        $mailsubject    = "=?UTF-8?B?" . base64_encode($mailsubject) . "?=";
        $mailtype       = "HTML";
        $smtp           = new AliSmtp($smtpserver, $smtpserverport, true, $smtpuser, $smtppass);
        $smtp->debug    = true;
        $smtp->sendmail($mailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);

    }

    public function test() {
        phpinfo();die();
    }

}