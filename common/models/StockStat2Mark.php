<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "im_stock_stat2_mark".
 *
 * @property integer $m_id
 * @property integer $m_cat
 * @property string $m_stock_id
 * @property string $m_added_on
 * @property string $m_updated_on
 */
class StockStat2Mark extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_stat2_mark';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'm_id' => 'M ID',
            'm_cat' => '1.可以，底色红色2.贵，底色紫色3.放弃，绿色4.观望，黄色',
            'm_stock_id' => 'M Stock ID',
            'm_added_on' => '添加时间',
            'm_updated_on' => '修改时间',
        ];
    }

    const CAT_OK = 1;
    const CAT_EXPENSIVE = 2;
    const CAT_GIVE_UP = 3;
    const CAT_WATCH = 4;

    static $cat_dict = [
        self::CAT_OK => '可以',
        self::CAT_EXPENSIVE => '贵',
        self::CAT_GIVE_UP => '放弃',
        self::CAT_WATCH => '观望',
    ];

    static $cat_colors = [
        self::CAT_OK => '红色',
        self::CAT_EXPENSIVE => '紫色',
        self::CAT_GIVE_UP => '绿色',
        self::CAT_WATCH => '黄色',
    ];

    /**
     * add
     *
     * @time 2020-08-20 PM
     */
    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }
        if ($entity = self::unique_one($values['m_stock_id'], $values['m_cat'])) {
            return self::edit($entity->m_id, $values);
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->m_added_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    /**
     * one
     *
     * @time 2020-08-20 PM
     */
    public static function unique_one($stock_id, $cat)
    {
        return self::findOne([
            'm_stock_id' => $stock_id,
            'm_cat' => $cat,
        ]);
    }

    /**
     * edit
     *
     * @time 2020-08-20 PM
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
        $entity->m_updated_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    public static function items($criteria, $params, $page, $pageSize = 1000)
    {
        $limit = " limit ".($page - 1) * $pageSize.",".$pageSize;
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND '.implode(' AND ', $criteria);
        }

        $sql = "select m.*,m1.mStockName
				from im_stock_stat2_mark as m
				left join im_stock_menu m1 on m1.mStockId=m.m_stock_id
				where m_id>0 $strCriteria 
				order by m_updated_on desc 
				$limit ";
        $res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();

        foreach ($res as $k => $v) {
            $res[$k]['m_cat_t'] = self::$cat_dict[$v['m_cat']] ?? '';
            $res[$k]['m_cat_c'] = self::$cat_colors[$v['m_cat']] ?? '';
        }

        $sql = "select count(1) as co
				from im_stock_stat2_mark as m
				left join im_stock_menu m1 on m1.mStockId=m.m_stock_id
				where m_id>0 $strCriteria";
        $count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

        return [array_values($res), $count];
    }

    public static function get_all_stock_ids()
    {
        $all = self::find()->select('m_stock_id,m_cat')->asArray()->all();
        $data = [];
        foreach ($all as $v) {
            $m_stock_id = $v['m_stock_id'];
            $m_cat = $v['m_cat'];
            if (!isset($data[$m_stock_id])) {
                $data[$m_stock_id] = 'bg_color_'.$m_cat;
            }
        }

        return $data;
    }
}
