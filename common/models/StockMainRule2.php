<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_main_rule2".
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
 * @property float $r_diff_gt
 * @property float $r_diff_lt
 * @property float $r_sh_close_avg_gt
 * @property float $r_sh_close_avg_lt
 * @property string $r_date_gt
 * @property string $r_date_lt
 * @property string $r_scat
 * @property string $r_note
 * @property string $r_added_on
 * @property string $r_update_on
 */
class StockMainRule2 extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'im_stock_main_rule2';
    }

    public function attributeLabels()
    {
        return [
            'r_id' => 'id',
            'r_name' => '买卖名称',
            'r_status' => '1使用，9删除',
            'r_cat' => '1买入，2卖出',
            'r_stocks_gt' => '大盘大于 => 上证涨跌 大于',
            'r_stocks_lt' => '大盘小于 => 上证涨跌 小于',
            'r_cus_gt' => '散户大于 => 散户比值均值比例 大于',
            'r_cus_lt' => '散户小于 => 散户比值均值比例 小于',
            'r_turnover_gt' => '交易额大于',
            'r_turnover_lt' => '交易额小于',
            'r_sh_turnover_gt' => '上证交易额大于',
            'r_sh_turnover_lt' => '上证交易额小于',
            'r_diff_gt' => '差值 合计交易额均值比例—散户比值均值比例 大于',
            'r_diff_lt' => '差值 合计交易额均值比例—散户比值均值比例 小于',
            'r_sh_close_avg_gt' => '上证指数均值大于',
            'r_sh_close_avg_lt' => '上证指数均值小于',
            'r_sh_close_60avg_10avg_offset_gt' => '差值 上证指数60日均值-上证指数10日均值 大于',
            'r_sh_close_60avg_10avg_offset_lt' => '差值 上证指数60日均值-上证指数10日均值 小于',

            // r_sh_close_60avg_10avg_offset_gt  r_sh_close_60avg_10avg_offset_lt 这两个条件的 且与或的 选择，默认是且
            //1.不勾选是，默认是“并”，即2个都满足才行
            //2.勾选后，是“或”，只要满足其中一个就可以。满足60-10日，这2个选项中，任何一个就可以。
            'r_sh_close_60avg_10avg_offset_choose' => '上证指数60日均值与10日均值选择 ’且‘ 与 ’或‘',

            'r_sh_close_avg_change_rate_gt' => '上证指数均值/上证涨跌 比例 大于',
            'r_sh_close_avg_change_rate_lt' => '上证指数均值/上证涨跌 比例 小于',

            'r_date_gt' => '日期大于',
            'r_date_lt' => '日期小于',
            'r_scat' => 'day类型 5日，10日，20日,，60日',
            'r_note' => '备注',
            'r_added_on' => 'add',
            'r_update_on' => 'update',
        ];
    }

    const CAT_BUY = 1;
    const CAT_SOLD = 2;
    const CAT_WARN = 3;
    static $cats = [
        self::CAT_BUY => '买入',
        self::CAT_SOLD => '卖出',
        self::CAT_WARN => '预警',
    ];

    const ST_ACTIVE = 1;
    const ST_DEL = 9;
    static $stDict = [
        self::ST_ACTIVE => '使用',
        self::ST_DEL => '禁用',
    ];

    const CAT_MAIN_RULE = 'main_rule2';

    const AVG_10_60_AND = 1;
    const AVG_10_60_OR = 3;
    static $r_sh_close_60avg_10avg_offset_choose_dict = [
        self::AVG_10_60_AND => '且',
        self::AVG_10_60_OR => '或',
    ];

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }
        $before = '';

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->r_added_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        // 记录ADD 日志 2019-12-19 AM
        Log::add([
            "oCategory" => self::CAT_MAIN_RULE,
            "oOpenId" => $entity->r_id,
            "oBefore" => $before,
            "oAfter" => static::find()->where(['r_id' => $entity->r_id])->asArray()->one(),
        ]);

        return [$res, $entity];
    }

    public static function edit($id, $values = [])
    {
        if (!$values) {
            return [false, false];
        }

        $entity = self::findOne($id);
        $before = static::find()->where(['r_id' => $entity->r_id])->asArray()->one();

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

        // 记录修改 日志 2019-12-19 AM
        Log::add([
            "oCategory" => self::CAT_MAIN_RULE,
            "oOpenId" => $entity->r_id,
            "oBefore" => $before,
            "oAfter" => static::find()->where(['r_id' => $entity->r_id])->asArray()->one(),
        ]);

        return [$res, $entity];
    }

    /**
     * 按$cat获取购买策略
     *
     * @time 2020-02-29 AM
     */
    public static function get_rules($cat = self::CAT_BUY)
    {
        return self::find()->where([
            'r_status' => self::ST_ACTIVE,
            'r_cat' => $cat,
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
				from im_stock_main_rule2 as r
				where r_id>0 $strCriteria 
				order by r_cat asc,r_name asc
				$limit ";
        $res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();

        foreach ($res as $k => $v) {
            $res[$k]['r_status_t'] = self::$stDict[$v['r_status']] ?? '';
            $res[$k]['r_cat_t'] = self::$cats[$v['r_cat']] ?? '';
            $res[$k]['r_sh_close_60avg_10avg_offset_choose_t'] = self::$r_sh_close_60avg_10avg_offset_choose_dict[$v['r_sh_close_60avg_10avg_offset_choose']] ?? '';
        }
        $sql = "select count(1) as co
				from im_stock_main_rule2 as r
				where r_id>0 $strCriteria ";
        $count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

        return [self::diff_from_old($res), $count];
    }

    /**
     * 新的策略列表中和老的，如果策略名称相同，但是里面数值不同的，麻烦帮我标红。
     *
     * @time 2020-03-27 PM
     */
    public static function diff_from_old($rules)
    {
        $fields = [
            'r_stocks_gt',
            'r_stocks_lt',
            'r_cus_gt',
            'r_cus_lt',
            'r_turnover_gt',
            'r_turnover_lt',
            'r_sh_turnover_gt',
            'r_sh_turnover_lt',
            'r_diff_gt',
            'r_diff_lt',
            'r_sh_close_avg_gt',
            'r_sh_close_avg_lt',
            'r_sh_close_60avg_10avg_offset_gt',
            'r_sh_close_60avg_10avg_offset_lt',
            'r_sh_close_avg_change_rate_gt',
            'r_sh_close_avg_change_rate_lt',
            'r_date_gt',
            'r_date_lt',
            'r_scat',
        ];
        foreach ($rules as $k => $rule) {
            $r_name = $rule['r_name'];
            $old = StockMainRule::findOne(['r_name' => $r_name, 'r_status' => StockMainRule::ST_ACTIVE]);
            foreach ($fields as $field) {
                $rules[$k][$field . '_cls'] = '';
                if ($old && $old->$field != $rule[$field]) {
                    //echo $r_name.' =old:'.$old->$field.' new:'.$rule[$field]."\n";
                    //$rules[$k][$field.'_old'] = $old->$field;
                    $rules[$k][$field . '_cls'] = 'rule_diff';
                }
            }
        }
        //print_r($rules);exit;

        return $rules;

    }

}
