<?php
header("Content-Type: text/html; charset=utf-8");
 date_default_timezone_set('PRC');
//引入发送邮件类
require("smtp.php");
//使用163邮箱服务器
$smtpserver = "smtp.163.com";
//163邮箱服务器端口
$smtpserverport = 25;
//你的163服务器邮箱账号
$smtpusermail = "git_smtp@163.com";
//收件人邮箱
$smtpemailto = "1873645345@qq.com";
 
//你的邮箱账号(去掉@163.com)
$smtpuser = "git_smtp";//你的163邮箱去掉后面的163.com
//你的邮箱密码
$smtppass = "jiang22513822"; //你的163邮箱SMTP的授权码，千万不要填密码！！！
 
//邮件主题
$mailsubject = "纸巾缺货通知";
//邮件内容
$mailbody = "设备号104800的纸巾机请及时补货!";
//邮件格式（HTML/TXT）,TXT为文本邮件
$mailtype = "TXT";
//这里面的一个true是表示使用身份验证,否则不使用身份验证.
$smtp = new smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);
//是否显示发送的调试信息
$smtp->debug = TRUE;
//发送邮件
$smtp->sendmail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype);
 
?>