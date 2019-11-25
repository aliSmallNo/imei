<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_main_rule".
 *
 * @property integer $r_id
 * @property integer $r_cat
 * @property integer $r_status
 * @property string $r_name
 * @property float $r_stocks_gt
 * @property float $r_stocks_lt
 * @property float $r_cus_gt
 * @property float $r_cus_lt
 * @property float $r_turnover_gt
 * @property float $r_turnover_lt
 * @property float $r_sh_turnover_gt
 * @property float $r_sh_turnover_lt
 * @property float $r_diff
 * @property float $r_sh_close_avg_gt
 * @property float $r_sh_close_avg_lt
 * @property string $r_note
 * @property string $r_added_on
 * @property string $r_update_on
 */
class StockMainRule extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'im_stock_main_rule';
    }

    public function attributeLabels()
    {
//        上证涨跌大于
//        上证涨跌小于
//        散户比值均值比例大于
//        散户比值均值比例小于
//        合计交易额均值比例大于
//        合计交易额均值比例小于
//        上证指数均值比例大于
//        上证指数均值比例小于
        return [
            'r_id' => 'id',
            'r_name' => '买卖名称',
            'r_status' => '1使用，9删除',
            'r_cat' => '1买入，2卖出',
            'r_stocks_gt' => '大盘大于',
            'r_stocks_lt' => '大盘小于',
            'r_cus_gt' => '散户大于',
            'r_cus_lt' => '散户小于',
            'r_turnover_gt' => '交易额大于',
            'r_turnover_lt' => '交易额小于',
            'r_sh_turnover_gt' => '上证交易额大于',
            'r_sh_turnover_lt' => '上证交易额小于',
            'r_diff' => '差值',
            'r_sh_close_avg_gt' => '上证指数均值大于',
            'r_sh_close_avg_lt' => '上证指数均值小于',
            'r_note' => '备注',
            'r_added_on' => 'add',
            'r_update_on' => 'update',
        ];
    }

    const CAT_BUY = 1;
    const CAT_SOLD = 2;
    static $cats = [
        self::CAT_BUY => '买入',
        self::CAT_SOLD => '卖出',
    ];

    const ST_ACTIVE = 1;
    const ST_DEL = 9;
    static $stDict = [
        self::ST_ACTIVE => '使用',
        self::ST_DEL => '禁用',
    ];

    public static function init_excel_data()
    {
        $data = [
            self::CAT_BUY => [
                [0, -0.90, -9.00, 9.00, 10.00, 0],
                [0, -0.90, 0, 9.00, 10.00, 0],
            ],
            self::CAT_SOLD => [
                [0.90, 0, 0, -9.00, 10.00, -10.00],
                [0.90, 0, 9.00, 0, 10.00, 0],
            ],
        ];
        foreach ($data as $cat => $v1) {
            foreach ($v1 as $k => $v2) {
                continue;
                self::add([
                    'r_name' => self::$cats[$cat] . ($k + 1),
                    'r_cat' => $cat,
                    'r_status' => self::ST_ACTIVE,
                    'r_stocks_gt' => $v2[0],
                    'r_stocks_lt' => $v2[1],
                    'r_cus_gt' => $v2[2],
                    'r_cus_lt' => $v2[3],
                    'r_turnover_gt' => $v2[4],
                    'r_turnover_lt' => $v2[5],
                ]);
            }
        }
    }

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->r_added_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
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
            if ($val) {
                $entity->$key = $val;
            }
        }
        $entity->r_update_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    /**
     * 按$cat获取购买策略
     *
     * @time 2019-11-20 AM
     */
    public static function get_rules($cat = self::CAT_BUY)
    {
        return StockMainRule::find()->where([
            'r_status' => self::ST_ACTIVE,
            'r_cat' => $cat
        ])->asArray()->orderBy('r_id asc')->all();
    }

    public static function items($criteria, $params, $page, $pageSize = 20)
    {
        $limit = " limit " . ($page - 1) * $pageSize . "," . $pageSize;
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND ' . implode(' AND ', $criteria);
        }

        $sql = "select r.*
				from im_stock_main_rule as r
				where r_id>0 $strCriteria 
				order by r_id asc 
				$limit ";
        $res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();

        foreach ($res as $k => $v) {
            $res[$k]['r_status_t'] = self::$stDict[$v['r_status']] ?? '';
            $res[$k]['r_cat_t'] = self::$cats[$v['r_cat']] ?? '';
        }
        $sql = "select count(1) as co
				from im_stock_main_rule as r
				where r_id>0 $strCriteria ";
        $count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

        return [$res, $count];
    }

}
