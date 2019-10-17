<?php
/**
 * Created by PhpStorm.
 * User: JHR
 * Date: 2019/6/17
 * Time: 15:03
 */
namespace app\admin\controller;
use think\Db;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class Excel extends Base {

    public function deviceToExcel() {
        try {
            $param['datemin'] = input('param.datemin');
            $param['datemax'] = input('param.datemax');
            $param['search'] = input('param.search');
            $page['query'] = http_build_query(input('param.'));

            $where = [];
            if($param['search']) {
                $where[] = ['name|device_num','like',"%{$param['search']}%"];
            }

            if(session('username') !== config('superman')) {
                $device_id = Db::table('admin')->where('id','=',session('admin_id'))->value('device_id');
                $device_ids = explode(',',$device_id);
                $where[] = ['id','in',$device_ids];
                if(empty($device_id)) {
                    $list = [];
                    $total_sum = 0;
                }else {
                    $list = Db::table('device')->where($where)->select();
                    $total_sum = Db::table('device')->where($where)->sum('total_price');
                }
            }else {
                $list = Db::table('device')->where($where)->select();
                $total_sum = Db::table('device')->where($where)->sum('total_price');
            }

            if($param['datemin'] || $param['datemax']) {
                $whereMoney = [
                    ['status','=',1]
                ];
                if($param['datemin']) {
                    $whereMoney[] = ['pay_time','>=',strtotime(date('Y-m-d 00:00:00',strtotime($param['datemin'])))];
                }
                if($param['datemax']) {
                    $whereMoney[] = ['pay_time','<=',strtotime(date('Y-m-d 23:59:59',strtotime($param['datemax'])))];
                }
                $total_sum = 0;
                foreach ($list as &$vv) {
                    $mapMoney = $whereMoney;
                    $mapMoney[] = ['device_num','=',$vv['device_num']];
                    $vv['total_price'] = Db::table('order')->where($mapMoney)->sum('total_price');
                    $total_sum += $vv['total_price'];
                    unset($mapMoney);
                }

            }
        } catch(\Exception $e) {
            die($e->getMessage());
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('纸巾机统计');

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getRowDimension('1')->setRowHeight(30);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(12);

        $sheet->getStyle('A:Z')->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A1')->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A')->getNumberFormat()->setFormatCode( \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
        $sheet->getStyle('C:D')->getNumberFormat()->setFormatCode( \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);

        $sheet->mergeCells('A1:E1');

        $sheet->setCellValue('A1', '纸巾机销售统计' . date('Y-m-d H:i:s') . ' 制表人:' . session('username'));
        $sheet->setCellValue('A2', '设备名');
        $sheet->setCellValue('B2', '设备号');
        $sheet->setCellValue('C2', '销售额(元)');
        $sheet->setCellValue('D2', '单价(元)');
        $sheet->setCellValue('E2', '余量(包)');
        $sheet->getStyle('A2:E2')->getFont()->setBold(true);

        $index = 3;
        foreach ($list as $v) {
            $sheet->setCellValue('A'.$index, $v['name']);
            $sheet->setCellValue('B'.$index, $v['device_num']);
            $sheet->setCellValue('C'.$index, $v['total_price']);
            $sheet->setCellValue('D'.$index, $v['unit_price']);
            $sheet->setCellValue('E'.$index, $v['stock']);
            $index++;
        }

        $sheet->setCellValue('C'.$index, $total_sum);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
//header(‘Content-Type:application/vnd.ms-excel‘);//告诉浏览器将要输出Excel03版本文件
        header('Content-Disposition: attachment;filename="tissue'.date('Y-m-d').'.xlsx"');//告诉浏览器输出浏览器名称
        header('Cache-Control: max-age=0');//禁止缓存

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

    }


}