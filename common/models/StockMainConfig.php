<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_main_config".
 *
 * @property integer $c_id
 * @property string $c_cat
 * @property string $c_content
 * @property string $c_note
 * @property integer $c_status
 * @property string $c_added_on
 * @property string $c_update_on
 */
class StockMainConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'im_stock_main_config';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'c_id' => 'C ID',
            'c_cat' => 'phone:手机号',
            'c_content' => '内容',
            'c_note' => '备注',
            'c_status' => '1使用 9删除',
            'c_added_on' => 'add时间',
            'c_update_on' => '修改时间',
        ];
    }

    const CAT_PHONE = 'phone';
    const CAT_SMS_ST = 'sms_st';
    const CAT_SMS_ET = 'sms_et';
    const CAT_SMS_TIMES = 'sms_times';
    static $catDict = [
        self::CAT_PHONE => '推送短信手机',
        self::CAT_SMS_ST => '推送短信开始时间',
        self::CAT_SMS_ET => '推送短信结束时间',
        self::CAT_SMS_TIMES => '每日每个手机号推送短信次数',
    ];

    const ST_ACTIVE = 1;
    const ST_DEL = 9;
    static $stDict = [
        self::ST_ACTIVE => '使用',
        self::ST_DEL => '禁用',
    ];

    public static function add($values = [])
    {
        if (!$values) {
            return [false, false];
        }

        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->c_added_on = date('Y-m-d H:i:s');
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
        $entity->c_update_on = date('Y-m-d H:i:s');
        $res = $entity->save();

        return [$res, $entity];
    }

    /**
     * 获取列表
     *
     * @time 2019-12-19 PM
     */
    public static function get_items_by_cat($cat = self::CAT_PHONE)
    {
        $ret = static::find()->where([
            'c_cat' => $cat,
        ])->asArray()->orderBy('c_update_on desc,c_id desc')->all();

        return $ret;
    }

    /**
     * 获取推送短信手机号
     *
     * @time 2019-12-19 PM
     */
    public static function get_sms_phone()
    {
        $ret = self::get_items_by_cat();

        if (!$ret) {
            return [];
        }

        $phones = [];
        foreach ($ret as $v) {
            $phone = trim($v['c_content']);
            $status = trim($v['c_status']);
            if (AppUtil::checkPhone($phone) && $status == self::ST_ACTIVE) {
                $phones[] = $phone;
            }
        }
        $phones = array_unique($phones);

        return $phones;
    }

}
