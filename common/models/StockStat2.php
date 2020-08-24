<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_stat2".
 *
 * @property integer $s_id
 * @property integer $s_cat
 * @property string $s_stock_id
 * @property string $s_trans_on
 * @property string $s_added_on
 * @property string $s_updated_on
 */
class StockStat2 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_stat2';
    }

    public function attributeLabels()
    {
        return [
            's_id' => 'S ID',
            's_cat' => '4:符合标准4',
            's_stock_id' => 'S Stock ID',
            's_trans_on' => '交易日期',
            's_added_on' => '添加时间',
            's_updated_on' => '添加时间',
        ];
    }

    const STANDARD_4 = 4;

    /**
     * add
     *
     * @time 2020-05-31 PM
     */
    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }
        if ($entity = self::unique_one($values['s_stock_id'], $values['s_trans_on'], $values['s_cat'])) {
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

    /**
     * one
     *
     * @time 2020-05-31 PM
     */
    public static function unique_one($stock_id, $date, $cat)
    {
        return self::findOne([
            's_stock_id' => $stock_id,
            's_trans_on' => $date,
            's_cat' => $cat,
        ]);
    }

    /**
     * edit
     *
     * @time 2020-05-31 PM
     */
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
            if ($val == '') {
                continue;
            }
            $entity->$key = $val;
        }
        $entity->s_updated_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    /**
     * 每日更新 初始化数据
     * @param string $dt
     * @return bool
     * @time 2020-05-31 PM
     */
    public static function init_today_data($dt = "")
    {
        if (!$dt) {
            $dt = date('Y-m-d');
        } else {
            $dt = date('Y-m-d', strtotime($dt));
        }

        list($select1, $select2) = StockTurn::stock171_new($dt, 0);
        $list3 = StockTurn::get_pb_pe_stock($dt, 0);
        $list4 = StockTurn::get_intersect_2and3($select2, $list3);

        // 删除旧的
        self::deleteAll(['s_trans_on' => $dt,]);

        if ($list4) {
            foreach ($list4 as $v) {
                self::add([
                    's_cat' => self::STANDARD_4,
                    's_stock_id' => $v['id'],
                    's_trans_on' => $dt,
                ]);
            }
        }

        return true;
    }

    /**
     * 初始化数据 所有日期
     * @param string $dt
     * @return bool
     * @time 2020-05-31 PM
     */
    public static function init_data()
    {
        $dts = StockMain::get_trans_dates();
        foreach ($dts as $dt) {
            if (strtotime($dt) < strtotime('2020-08-10')) {
                continue;
            }
            echo $dt.PHP_EOL;
            self::init_today_data($dt);

        }
    }


    public static function items($criteria, $params, $page, $pageSize = 20)
    {
        $conn = AppUtil::db();
        $offset = ($page - 1) * $pageSize;
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND '.implode(' AND ', $criteria);
        }
        // group_concat 有长度限制：show variables like 'group_concat_max_len'; =>  1024
        $sql = "select 
                group_concat(s_stock_id) as stock_ids ,s_trans_on
				from im_stock_stat2 as s
				left join im_stock_menu as m on m.mStockId=s.s_stock_id 
				where s.s_id>0  $strCriteria
				group by s_trans_on
				order by s.s_trans_on desc
				limit $offset,$pageSize";
        $res = $conn->createCommand($sql, [])->bindValues($params)->queryAll();

        $stocks = StockMenu::get_all_stocks_kv();
        $stock_ids_c = StockStat2Mark::get_all_stock_ids();
        foreach ($res as $k => $v) {
            $stock_ids = explode(',', $v['stock_ids']);
            foreach ($stock_ids as $stock_id) {
                $stock_name = $stocks[$stock_id] ?? '';
                $stock_item = $stock_ids_c[$stock_id] ?? '';
                $stock_bg = $stock_item ? $stock_item['bg_color'] : '';
                $stock_desc = $stock_item ? $stock_item['desc'] : '';

                $res[$k]['stock_arr'][] = [
                    'id' => $stock_id,
                    'name' => $stock_name,
                    'stock_bg' => $stock_bg,
                    'desc' => $stock_desc,
                ];
            }
        }

        $sql = "select count(1) from (
                select 
                s_trans_on
				from im_stock_stat2 as s
				left join im_stock_menu as m on m.mStockId=s.s_stock_id 
				where s.s_id>0  $strCriteria
				group by s_trans_on
                ) as a";
        $count = $conn->createCommand($sql, [])->bindValues($params)->queryScalar();

        return [$res, $count];
    }

}
