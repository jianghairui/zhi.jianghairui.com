<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2018/10/15
 * Time: 20:30
 */
namespace app\admin\controller;

use think\Db;
class System extends Base {

    public function syslog() {

        $param['logmin'] = input('param.logmin');
        $param['logmax'] = input('param.logmax');
        $param['search'] = input('param.search');

        $page['query'] = http_build_query(input('param.'));

        $curr_page = input('param.page',1);
        $perpage = input('param.perpage',20);

        $where = [];
        if($param['logmin']) {
            $where[] = ['s.create_time','>=',strtotime(date('Y-m-d 00:00:00',strtotime($param['logmin'])))];
        }

        if($param['logmax']) {
            $where[] = ['s.create_time','<=',strtotime(date('Y-m-d 23:59:59',strtotime($param['logmax'])))];
        }

        if($param['search']) {
            $where[] = ['a.realname|s.detail','like',"%{$param['search']}%"];
        }
        try {
            $count = Db::table('syslog')->alias('s')
                ->join('admin a','s.admin_id=a.id','left')
                ->where($where)->count();
            $list = Db::table('syslog')->alias('s')
                ->join('admin a','s.admin_id=a.id','left')
                ->where($where)
                ->order(['create_time'=>'DESC'])
                ->field('s.*,a.realname,a.username')
                ->limit(($curr_page - 1)*$perpage,$perpage)
                ->select();
        } catch(\Exception $e) {
            die($e->getMessage());
        }

        $page['count'] = $count;
        $page['curr'] = $curr_page;
        $page['totalPage'] = ceil($count/$perpage);
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }



}