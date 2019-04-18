<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/4/18
 * Time: 15:00
 */
namespace app\admin\controller;
use wx\Jssdk;
class Menu extends Common {

    public function createMenu() {
        $data = [
            'button' => [
                [
                    'name' => '博物馆商店',
                    'type' => 'view',
                    'url' => 'http://bwgsd.com'
                ],
                [
                    'name' => '自主产品',
                    'sub_button' => [
                        [
                            'name' => '山洞文创',
                            'type' => 'miniprogram',
                            'url' => 'http://mp.weixin.qq.com',
                            'appid' => 'wxe7bbb3344157bd09',
                            'pagepath' => 'pages/auth/auth'
                        ],
                        [
                            "type"=>"view",
                            "name"=>"公司官网",
                            "url"=>"https://www.wcip.net/"
                        ]
                    ],
                ]
            ]
        ];
        $jssdk = new Jssdk($this->config['appid'], $this->config['app_secret']);
        $access_token = $jssdk->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token;
        $result = curl_post_data($url,json_encode($data,JSON_UNESCAPED_UNICODE));
        $obj = json_decode($result,true);
        halt($obj);

    }









}