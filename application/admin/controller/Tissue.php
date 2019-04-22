<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/4/19
 * Time: 13:44
 */
namespace app\admin\controller;
use think\Db;
use tissue\Token;
class Tissue extends Base {

    protected $domain = 'zhi.jianghairui.com';

    public function deviceList() {
        try {
            $param['search'] = input('param.search');
            $page['query'] = http_build_query(input('param.'));

            $curr_page = input('param.page',1);
            $perpage = input('param.perpage',10);
            $where = [];
            if($param['search']) {
                $where[] = ['name|device_num','like',"%{$param['search']}%"];
            }
            try {
                $count = Db::table('device')->where($where)->count();
                $list = Db::table('device')
                    ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
            }catch (\Exception $e) {
                die('SQL错误: ' . $e->getMessage());
            }

            $page['count'] = $count;
            $page['curr'] = $curr_page;
            $page['totalPage'] = ceil($count/$perpage);

        } catch(\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function orderList() {
        try {
            $param['search'] = input('param.search');
            $page['query'] = http_build_query(input('param.'));

            $curr_page = input('param.page',1);
            $perpage = input('param.perpage',10);
            $where = [];
            if($param['search']) {
                $where[] = ['pay_order_sn','like',"%{$param['search']}%"];
            }

            try {
                $count = Db::table('order')->where($where)->count();
                $list = Db::table('order')
                    ->where($where)->limit(($curr_page - 1)*$perpage,$perpage)->select();
            }catch (\Exception $e) {
                die('SQL错误: ' . $e->getMessage());
            }

            $page['count'] = $count;
            $page['curr'] = $curr_page;
            $page['totalPage'] = ceil($count/$perpage);

        } catch(\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function orderRefund() {
        $val['id'] = input('post.id');
        checkPost($val);
        try {
            $where = [
                ['id','=',$val['id']],
                ['status','=',1],
                ['refund_apply','in',[0,1]],
            ];
            $exist = Db::table('order')->where($where)->find();
            if(!$exist) {
                return ajax('订单不存在或状态已改变',-1);
            }
            $pay_order_sn = $exist['pay_order_sn'];
//            $exist['pay_price'] = 0.01;
            $arr = [
                'appid' => config('appid'),
                'mch_id' => config('mch_id'),
                'nonce_str' => randomkeys(32),
                'sign_type'=>'MD5',
                'transaction_id'=> $exist['trans_id'],
                'out_trade_no'=> $pay_order_sn,
                'out_refund_no'=> 'r' . $pay_order_sn,
                'total_fee'=> floatval($exist['total_price'])*100,
                'refund_fee'=> floatval($exist['total_price'])*100,
                'refund_fee_type'=> 'CNY',
                'refund_desc'=> '订单退款',
                'notify_url'=> $_SERVER['REQUEST_SCHEME'] . '://'.$_SERVER['HTTP_HOST'].'/wxRefundNotify',
                'refund_account' => 'REFUND_SOURCE_UNSETTLED_FUNDS'
            ];

            $arr['sign'] = getSign($arr);
            $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
            $result = curl_post_data($url,array2xml($arr),true);
            $res = xml2array($result);
            if($res && $res['return_code'] == 'SUCCESS') {
                if($res['result_code'] == 'SUCCESS') {
                    $update_data = [
                        'refund_apply' => 2,
                        'refund_time' => time()
                    ];
                    Db::table('order')->where($where)->update($update_data);
                    $whereDevice = [
                        ['device_num','=',$exist['device_num']]
                    ];
                    Db::table('device')->where($whereDevice)->setDec('total_price',$exist['total_price']);
                    return ajax();
                }else {
                    return ajax($res['err_code_des'],-1);
                }
            }else {
                return ajax('退款通知失败',-1);
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }
    }

    public function deviceDetail() {
        $id = input('param.id',0);
        try {
            $where = [
                ['id','=',$id]
            ];
            $exist = Db::table('device')->where($where)->find();
            if(!$exist) {
                die('非法参数');
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
        $this->assign('info',$exist);
        return $this->fetch();
    }

    public function deviceModPost() {
        $val['unit_price'] = input('post.unit_price');
        $val['stock'] = input('post.stock');
        $val['email'] = input('post.email');
        $val['id'] = input('post.id');
        try {
            $where = [
                ['id','=',$val['id']]
            ];
            $exist = Db::table('device')->where($where)->find();
            if(!$exist) {
                return ajax('非法参数',-1);
            }
            Db::table('device')->where($where)->update($val);

            $tissue = new Token();
            $access_token = $tissue->getAccessToken();
            if($access_token) {
                $url = "http://server.songzb.com:30000/partner/updateDevicePaper";
                $post_data = [
                    "deviceCode" => $exist['device_num'],
                    "totalCount" => $val['stock']
                ];
                $json = $this->curl_post_data($url, json_encode($post_data),['Content-type: application/json', 'accessToken: '.$access_token]);
                $result = json_decode($json,true);
                if($result['resultStatus'] === true) {
                    return ajax();
                }else {
                    $this->excep($this->cmd,var_export($result,true));
                }
            }else {
                return ajax('get access_token failed',-1);
            }
        } catch (\Exception $e) {
            return ajax($e->getMessage(), -1);
        }


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

//    public function updateDevicePaper() {
//
//        $deviceCode = '103602';
//        $totalCount = 3;
//        try {
//            $device_exist = Db::table('device')->where('device_num',$deviceCode)->find();
//            if(!$device_exist) {
//                return ajax('操作异常',-1);
//            }
//        } catch (\Exception $e) {
//            return ajax($e->getMessage(), -1);
//        }
//        $tissue = new Token();
//        $access_token = $tissue->getAccessToken();
//        if($access_token) {
//            $url = "http://server.songzb.com:30000/partner/updateDevicePaper";
//            $post_data = [
//                "deviceCode" => $deviceCode,
//                "totalCount" => $totalCount
//            ];
//            $json = $this->curl_post_data($url, json_encode($post_data),['Content-type: application/json', 'accessToken: '.$access_token]);
//            $result = json_decode($json,true);
//            halt($result);
//
//            if($result['resultStatus'] === true) {
//                return ajax();
//            }else {
//                return ajax('补充纸巾失败',-1);
//            }
//        }else {
//            return ajax('get access_token failed',-1);
//        }
//
//    }



}