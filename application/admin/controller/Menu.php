<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/4/18
 * Time: 15:00
 */
namespace app\admin\controller;
use wx\Jssdk;
use think\Db;
class Menu extends Common {

    public function menuList() {
        die('YOU DIE TOO');
        $jssdk = new Jssdk($this->config['appid'], $this->config['app_secret']);
        $access_token = $jssdk->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=' . $access_token;
        $result = file_get_contents($url);
        $obj = json_decode($result,true);
        halt($obj);
//        echo json_encode($obj);
//        try {
//            $list = Db::table('menu')->select();
//        } catch (\Exception $e) {
//            die($e->getMessage());
//        }
//        $newlist = $this->sortMerge($list);
//        $this->assign('list',$newlist);
//        return $this->fetch();
    }

    public function updateMenu() {
//        die('YOU DIE');
        $data = [
            'button' => [
                [
                    'name' => '往日精品',
                    'type' => 'view',
                    'url' => 'https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzAxNTY1ODI5Mg==&subscene=0#wechat_redirect'
                ],
                [
                    'name' => '山洞文创',
                    'type' => 'miniprogram',
                    'url' => 'http://mp.weixin.qq.com',
                    'appid' => 'wxe7bbb3344157bd09',
                    'pagepath' => 'pages/auth/auth'
                ],
                [
                    'name' => '联系我们',
                    'type' => 'view',
                    'url' => 'https://www.wcip.net'
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


    private function sortMerge($node,$pid=0)
    {
        $arr = array();
        foreach($node as $key=>$v)
        {
            if($v['pid'] == $pid)
            {
                $v['sub_button'] = $this->sortMerge($node,$v['id']);
                $arr[] = $v;
            }
        }
        return $arr;
    }








}