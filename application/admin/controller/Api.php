<?php
/**
 * Created by PhpStorm.
 * User=>JHR
 * Date=>2019/1/30
 * Time=>14:15
 */
namespace  app\admin\controller;
use function Couchbase\defaultDecoder;
use think\Db;
use wx\Jssdk;
class Api extends Common {

    public function index() {
        //验证第三方服务器
        $param = input('param.');
        if(isset($param['echostr'])){
            $this->weixinLog($this->cmd,var_export($param,true));
//            $this->xmllog($this->cmd,var_export($param,true));
            $token = $this->token;
            $tmpArr = array($token,$param["timestamp"], $param["nonce"]);
            sort($tmpArr, SORT_STRING);
            $tmpStr = implode('',$tmpArr);
            $tmpStr = sha1( $tmpStr );
            if($tmpStr === $param["signature"]) {
                //第一次接入微信API
                exit($param['echostr']);
            }
        }else{
            $xml = file_get_contents('php://input');
            $data = xml2array($xml);
//            if($data) {
//                $this->xmllog($this->cmd,var_export($data,true));
//            }
            switch ($data['MsgType']) {
                case 'event':
                    switch ($data['Event']) {
                        case 'SCAN':
//                                $response_data = [
//                                    "ToUserName" => $data['FromUserName'],
//                                    "FromUserName" => $data['ToUserName'],
//                                    "CreateTime" => time(),
//                                    "MsgType" => "text",
//                                    "Content" => "纸巾机系统维护中",
//                                ];
//                                exit(arr2xml($response_data));
                            $device_id = $data['EventKey'];
                            try {
                                $device = Db::table('device')->where('id','=',$device_id)->find();
                                //$this->weixinLog($this->cmd,var_export($device,true));
                            } catch (\Exception $e) {
                                $this->excep($this->cmd,$e->getMessage());
                                exit('success');
                            }
                            $response_data = [
                                "ToUserName" => $data['FromUserName'],
                                "FromUserName" => $data['ToUserName'],
                                "CreateTime" => time(),
                                "MsgType" => "news",
                                "ArticleCount" => 1,
                                "Articles" => [
                                    "item" => [
                                        "Title" => $device['name'],
                                        "Description" => "设备编号" . $device['device_num'],
                                        "PicUrl" => "https://ss2.bdstatic.com/70cFvnSh_Q1YnxGkpoWK1HF6hhy/it/u=2297371377,1524008543&fm=26&gp=0.jpg",
                                        "Url" => $device['gift_url']
                                    ]
                                ]
                            ];
                            exit(arr2xml($response_data));
                            break;

                        case 'subscribe':
                            if($data['EventKey']) {
                                try {
                                    $device_id = explode('_',$data['EventKey'])[1];
                                    $device = Db::table('device')->where('id','=',$device_id)->find();
                                } catch (\Exception $e) {
                                    $this->excep($this->cmd,$e->getMessage());
                                    exit('success');
                                }
                                $response_data = [
                                    "ToUserName" => $data['FromUserName'],
                                    "FromUserName" => $data['ToUserName'],
                                    "CreateTime" => time(),
                                    "MsgType" => "news",
                                    "ArticleCount" => 1,
                                    "Articles" => [
                                        "item" => [
                                            "Title" => $device['name'],
                                            "Description" => "设备编号" . $device['device_num'],
                                            "PicUrl" => "https://ss2.bdstatic.com/70cFvnSh_Q1YnxGkpoWK1HF6hhy/it/u=2297371377,1524008543&fm=26&gp=0.jpg",
                                            "Url" => $device['gift_url']
                                        ]
                                    ]
                                ];
                                exit(arr2xml($response_data));
                            }
                            ;break;

                        case 'unsubscribe':
                            //todo
                                break;


                    };break;
                default:;
            }
            exit('success');
        }

    }





//7 查询授权完成状态
    public function cardinfo() {
        $jssdk = new Jssdk($this->config['appid'], $this->config['app_secret']);
        $access_token = $jssdk->getAccessToken();
        $s_pappid = 'd3gxZGM2NGFjYzliZDllYjA5X_i4jn6NAgX0Dl14GNauU5bkCCn9w7W5w7xmCvvUMvV1';

        $url = 'https://api.weixin.qq.com/card/invoice/getauthdata?access_token=' . $access_token;
        $data = [
            "s_pappid" => $s_pappid,
            "order_id" => "102",
        ];
        $result = curl_post_data($url,json_encode($data));
        $obj = json_decode($result);
        halt($obj);
    }
//8 拒绝开票
    public function rejectInvoice() {
        $jssdk = new Jssdk($this->config['appid'], $this->config['app_secret']);
        $access_token = $jssdk->getAccessToken();
        $s_pappid = 'd3gxZGM2NGFjYzliZDllYjA5X_i4jn6NAgX0Dl14GNauU5bkCCn9w7W5w7xmCvvUMvV1';

        $url = 'https://api.weixin.qq.com/card/invoice/rejectinsert?access_token=' . $access_token;
        $data = [
            "s_pappid" => $s_pappid,
            "order_id" => "102",
            "reason" => 'invalid order_sn',
            "url"   => 'http://www.baidu.com'
        ];
        $result = curl_post_data($url,json_encode($data));
        $obj = json_decode($result);
        halt($obj);
    }
//11 关联商户号与开票平台
    public function set_pay_mch() {
        $jssdk = new Jssdk($this->config['appid'], $this->config['app_secret']);
        $access_token = $jssdk->getAccessToken();
        $s_pappid = 'd3gxZGM2NGFjYzliZDllYjA5X_i4jn6NAgX0Dl14GNauU5bkCCn9w7W5w7xmCvvUMvV1';

        $url = 'https://api.weixin.qq.com/card/invoice/setbizattr?action=set_pay_mch&access_token=' . $access_token;
        $data['paymch_info'] = [
            "s_pappid" => $s_pappid,
            "mchid" => $this->config['mch_id'],
        ];
        $result = curl_post_data($url,json_encode($data));
        $obj = json_decode($result);
        halt($obj);
    }
//12 查询商户号与开票平台关联情况
    public function get_pay_mch() {
        $jssdk = new Jssdk($this->config['appid'], $this->config['app_secret']);
        $access_token = $jssdk->getAccessToken();
        $url = 'https://api.weixin.qq.com/card/invoice/setbizattr?action=get_pay_mch&access_token=' . $access_token;
        $result = curl_post_data($url,json_encode([]));
        $obj = json_decode($result);
        halt($obj);
    }

    public function makeoutinvoice() {
        $data['invoiceinfo'] = [
            "wxopenid"=>"oNEu_s8TWzpK6p6-kUFnFHaS1GiI",
            "ddh" =>"155032941148455400978",
            "fpqqlsh"=>"155032941148455400978",
            "nsrsbh"=>"91120116MA05MAH15R",
            "nsrmc"=>"山海文化有限公司",
            "nsrdz"=>"天津滨海高新区华科三路华鼎智地2号楼613室",
            "nsrdh"=>"022-87797523",
            "nsrbank"=>"天津银行融源支行",
            "nsrbankid"=>"106601201090299967",
            "ghfmc"=>"天津文博艺术品销售有限公司",
            "kpr"=>"姜海蕤",
            "jshj"=> "1.17",
            "hjje"=> "1",
            "hjse"=> "0.17",
            "hylx"=>"0",
            "invoicedetail_list"=>[
                "fphxz"=>"0",
                "spbm"=>"1060500000000000000",
                "xmmc"=>"文创产品",
                "xmsl"=>"1",
                "xmdj"=>"1",
                "xmje"=>"1",
                "sl"=>"0.17",
                "se"=>"0.17"
            ],
        ];
        $jssdk = new Jssdk($this->config['appid'], $this->config['app_secret']);
        $access_token = $jssdk->getAccessToken();
        $url = 'https://api.weixin.qq.com/card/invoice/makeoutinvoice?access_token=' . $access_token;
        $result = curl_post_data($url,json_encode($data));
        $obj = json_decode($result);
        halt($obj);
    }

}