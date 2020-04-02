<?php

namespace common\models;

use common\utils\AppUtil;
use common\utils\ExcelUtil;
use Yii;

/**
 * This is the model class for table "im_stock_main_pb_stat".
 *
 * @property integer $s_id
 * @property integer $s_pb_co
 * @property integer $s_stock_co
 * @property string $s_rate
 * @property string $s_sh_close
 * @property string $s_trans_on
 * @property string $s_added_on
 * @property string $s_update_on
 */
class StockMainPbStat extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return 'im_stock_main_pb_stat';
    }

    public function attributeLabels()
    {
        return [
            's_id' => 'S ID',
            's_pb_co' => '市净率小于1的股票数',
            's_stock_co' => '大盘股票数',
            's_rate' => '市净率小于1的股票数/大盘股票数',
            's_sh_close' => '上证 收盘价',
            's_trans_on' => '交易日期',
            's_added_on' => 'add时间',
            's_update_on' => '修改时间',
        ];
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }
        if ($entity = self::unique_one($values['s_trans_on'])) {
            return self::edit($entity->s_id, $values);
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->s_added_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function unique_one($TransOn)
    {
        return self::findOne([
            's_trans_on' => $TransOn,
        ]);
    }

    public static function edit($id, $values = [])
    {
        if (!$values) {
            return [false, false];
        }

        $entity = self::findOne($id);

        if (!$entity) {
            return [false, false];
        }

        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->s_update_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function pre_edit($values = [])
    {
        if (!$values) {
            return [false, false];
        }

        $entity = self::unique_one($values['s_trans_on']);
        if (!$entity) {
            return [false, false];
        }

        return self::edit($entity->s_id, $values);
    }

    /**
     * 更新每日市净率 统计数据
     *
     * @time 2020-03-30 PM
     */
    public static function update_one($dt = '')
    {
        if (!$dt) {
            $dt = date('Y-m-d');
        }
        $main = StockMain::findOne(['m_trans_on' => $dt]);
        $s_pb_co = StockMainPb::get_pb_count($dt);
        $s_stock_co = count(StockMenu::get_valid_stocks());
        StockMainPbStat::add([
            's_pb_co' => $s_pb_co,
            's_stock_co' => $s_stock_co,
            's_sh_close' => $main ? $main->m_sh_close : 0,
            's_rate' => $s_stock_co != 0 ? round($s_pb_co / $s_stock_co, 4) * 100 : 0,
            's_trans_on' => $dt,
        ]);
    }

    /**
     * 市净率统计 列表
     *
     * @time 2020-03-31 PM
     */
    public static function items($criteria, $params, $page = 1, $pageSize = 100)
    {
        $limit = " limit ".($page - 1) * $pageSize.",".$pageSize;
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND '.implode(' AND ', $criteria);
        }

        $sql = "select m.m_sh_close,s.s_sh_change,ps.* 
                from im_stock_main_pb_stat as ps
                join im_stock_main as m on ps.s_trans_on=m.m_trans_on
                left join im_stock_main_stat as s on m.m_trans_on=s.s_trans_on and s.s_cat=5
                where ps.s_id >0 $strCriteria
                order by ps.s_trans_on desc 
                $limit";
        $res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();


        $sql = "select count(1) as co
				from im_stock_main_pb_stat as ps
                join im_stock_main as m on ps.s_trans_on=m.m_trans_on
                left join im_stock_main_stat as s on m.m_trans_on=s.s_trans_on and s.s_cat=5
                where ps.s_id >0 $strCriteria ";
        $count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

        return [$res, $count];
    }

    /**
     * 市净率统计 导出
     *
     * @time 2020-04-01 AM
     */
    public static function export($sdate, $edate)
    {
        $filename_time = date("Y-m-d");

        $params = [];
        $where = "";
        if ($sdate && $edate) {
            $where = " and ps.s_trans_on between :st and :et ";
            $params = [
                ':st' => date('Y-m-d', strtotime($sdate)),
                ':et' => date('Y-m-d', strtotime($edate)),
            ];
        }

        $sql = "select m.m_sh_close,s.s_sh_change,ps.* 
                from im_stock_main_pb_stat as ps
                join im_stock_main as m on ps.s_trans_on=m.m_trans_on
                left join im_stock_main_stat as s on m.m_trans_on=s.s_trans_on and s.s_cat=5
                where ps.s_id >0 $where
                order by ps.s_trans_on desc";
        $conn = AppUtil::db();
        $res = $conn->createCommand($sql, $params)->queryAll();

        $header = $content = [];
        $header = [
            "交易日期",
            "市净率小于1的股票数",
            "股票总数",
            "占比",
            "上证指数",
            "上证涨幅",
        ];
        $cloum_w = [
            12,
            20,
            12,
            12,
            12,
            12,
        ];

        foreach ($res as $v) {
            $row = [
                $v['s_trans_on'],
                $v['s_pb_co'],
                $v['s_stock_co'],
                $v['s_rate'].'%',
                $v['m_sh_close'],
                $v['s_sh_change'].'%',
            ];

            $content[] = $row;
        }

        $filename = "破1市净率".$filename_time;

        ExcelUtil::getYZExcel($filename, $header, $content, $cloum_w);
        exit;
    }

    /**
     * 市净率统计 highstock 数据
     *
     * @time 2020-04-01 PM
     */
    public static function charts()
    {
        $sql = "select s.* 
                from im_stock_main_pb_stat as s
                join im_stock_main as m on s.s_trans_on=m.m_trans_on
                where s.s_id >0 order by s_trans_on asc";
        $conn = AppUtil::db();
        $res = $conn->createCommand($sql)->queryAll();
        $pb_cos = $pb_rates = $pb_sh_closes = [];
        foreach ($res as $v) {
            $pb_co = intval($v['s_pb_co']);
            $rate = floatval($v['s_rate']);
            $sh_close = floatval($v['s_sh_close']);
            // Highchart 有时区问题 WTF 改为前端设置了
            // $time = (strtotime($v['s_trans_on'].' 08:00:00')) * 1000;
            $time = (strtotime($v['s_trans_on'])) * 1000;
            $pb_cos[] = [$time, $pb_co, $v['s_trans_on']];
            $pb_rates[] = [$time, $rate, $v['s_trans_on']];
            $pb_sh_closes[] = [$time, $sh_close, $v['s_trans_on']];
        }

        return [$pb_rates, $pb_cos, $pb_sh_closes];
    }

    /**
     * 市净率统计 highstock 数据 测试bug
     *
     * @time 2020-04-01 PM
     */
    public static function charts2()
    {
        $data_s = file_get_contents(__DIR__.'/../../console/data/pbs.txt');
        $data_arr = AppUtil::json_decode($data_s);

        foreach ($data_arr as $v) {
            $pb_co = intval($v['belowNetAsset']);
            $sum = intval($v['totalCompany']);
            $rate = round($pb_co / $sum, 2);
            $time = $v['date'];
            $date = date('Y-m-d H:i:s', $time / 1000);
            $pb_cos[] = [$time, $pb_co, $date];
            $pb_rates[] = [$time, $rate, $date];
        }

        $data = [
            ['name' => '破净股比例', 'data' => $pb_rates],
            ['name' => '破净股数量', 'data' => $pb_cos, 'yAxis' => 1],
        ];

        return [$pb_rates, $pb_cos, $data];
    }

}
