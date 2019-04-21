<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/9/25
 * Time: 13:57
 */

return array(
    'app_trace' => false,
    'trace'     =>  [
        //支持Html,Console
        'type'  =>  'html',
    ],

    'layout_on'     =>  true,
    'layout_name'   =>  'layout',
    'login_key' => 'jiang',
    'superman'  => 'jianghairui',
    'auth'  => [
        'auth_on' => true,
        'auth_type'         => 1, // 认证方式，1为实时认证；2为登录认证。
        'auth_out'          => [
            'Index/index'
        ]
    ],

//博物馆商店商户号,公众号
    'appid' => 'wx1ef69d837c12a709',
    'app_secret' => 'b95d230bfbd56c0e1944e7f5d36b01f2',
    'mch_id' => '1251831001',
    'cert_path'  => '/var/www/zhi.jianghairui.com/public/cert/bwg_cert/apiclient_cert.pem',
    'key_path'  => '/var/www/zhi.jianghairui.com/public/cert/bwg_cert/apiclient_key.pem',
    'mch_key'  => '123654aaaZZZcaifu888aaazzzaaazzz',



);