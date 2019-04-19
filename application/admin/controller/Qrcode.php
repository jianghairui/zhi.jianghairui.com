<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/4/18
 * Time: 15:49
 */
namespace app\admin\controller;
use wx\Jssdk;
class Qrcode extends Common {

    public function getQrcodeWithParams() {
//        $data = [
//            'action_name' => 'QR_LIMIT_SCENE',
//            'action_info' => [
//                'scene' => [
//                    'scene_id' => 11
//                ]
//            ]
//        ];
//        $jssdk = new Jssdk($this->config['appid'], $this->config['app_secret']);
//        $access_token = $jssdk->getAccessToken();
//
//        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $access_token;
//        $result = curl_post_data($url,json_encode($data,JSON_UNESCAPED_UNICODE));
//        $obj = json_decode($result,true);
//        halt($obj);
    }

    public function test() {
//        $xml = '<xml>
//  <ToUserName><![CDATA[toUser]]></ToUserName>
//  <FromUserName><![CDATA[fromUser]]></FromUserName>
//  <CreateTime>12345678</CreateTime>
//  <MsgType><![CDATA[news]]></MsgType>
//  <ArticleCount>1</ArticleCount>
//  <Articles>
//    <item>
//      <Title><![CDATA[title1]]></Title>
//      <Description><![CDATA[description1]]></Description>
//      <PicUrl><![CDATA[picurl]]></PicUrl>
//      <Url><![CDATA[url]]></Url>
//    </item>
//  </Articles>
//</xml>';
//        $data = $this->xml2array($xml);
//        halt($data);

        $response_data = [
            "ToUserName" => 'FromUserName',
            "FromUserName" => 'ToUserName',
            "CreateTime" => time(),
            "MsgType" => "news",
            "ArticleCount" => 1,
            "Articles" => [
                "item" => [
                    "Title" => "山海图书馆",
                    "Description" => "description1",
                    "PicUrl" => "https://ss2.bdstatic.com/70cFvnSh_Q1YnxGkpoWK1HF6hhy/it/u=2297371377,1524008543&fm=26&gp=0.jpg",
                    "Url" => 'http://www.baidu.com'
                ]
            ]
        ];
        halt(arr2xml($response_data));
    }

}