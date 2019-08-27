<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_action_change".
 *
 * @property integer $acId
 * @property string $acType
 * @property string $acTxtBefore
 * @property string $acTxtAfter
 * @property string $acPhone
 * @property string $acAddedOn
 *
 * @create 2019.8.27
 * @author zp
 */
class StockActionChange extends \yii\db\ActiveRecord
{
    // 10:已注册,15:已认证,20:已操作
    const TYPE_REG = 10;
    const TYPE_IDENTITY = 15;
    const TYPE_OPT = 20;
    static $types = [
        self::TYPE_REG => '已注册',
        self::TYPE_IDENTITY => '已认证',
        self::TYPE_OPT => '已操作',
    ];

    public static function tableName()
    {
        return "{{%stock_action_change}}";
    }

    public static function add($values = [])
    {
        if (!$values) {
            return false;
        }
        $entity = new self();
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->acAddedOn = date('Y-m-d H:i:s');
        $entity->save();
        return $entity->acId;
    }

    public static function pre_add($values = [])
    {
        $acPhone = $values['acPhone'] ?? '';
        $acType = $values['acType'] ?? '';
        if (!AppUtil::checkPhone($acPhone)
            || !in_array($acType, array_keys(self::$types))) {
            return false;
        }

        if (self::has_one($acPhone, $acType)) {
            return false;
        }

        return self::add($values);

    }

    public static function has_one($acPhone, $acType)
    {
        if (self::findOne([
            'acPhone' => $acPhone,
            'acType' => $acType,
        ])) {
            return true;
        }
        return false;
    }

    public static function insert_today_change($day)
    {
        // $day = '2019-8-22';
        $type = StockAction::TYPE_ACTIVE;
        $sql = "select 
              aTypeTxt,substring(aTypeTxt,1,3) as st,aPhone,aAddedOn
              from im_stock_action 
              where DATEDIFF(aAddedOn,'$day')=0 and aType=$type 
              and (`aTypeTxt` like '已认证%' or `aTypeTxt` like '已注册%' or `aTypeTxt` like '已操作%')";

        $res = AppUtil::db()->createCommand($sql)->queryAll();
        foreach ($res as $v) {
            self::get_origin_data([
                'aPhone' => $v['aPhone'],
                'st' => $v['st'],
            ], $day);
        }
    }

    public static function get_origin_data($data, $day)
    {
        $acPhone = $data['aPhone'];
        $acTxtAfter = $data['st'];
        switch ($acTxtAfter) {
            case "已认证":
                $acType = self::TYPE_IDENTITY;
                break;
            case "已注册":
                $acType = self::TYPE_REG;
                break;
            case "已操作":
                $acType = self::TYPE_OPT;
                break;
        }

        if (self::has_one($acPhone, $acType)) {
            return false;
        }

        $insert = [
            'acPhone' => $acPhone,
            'acType' => $acType,
            'acTxtAfter' => $acTxtAfter,
            'acTxtBefore' => self::get_before_txt($acPhone, $day),
        ];

        return self::pre_add($insert);

    }

    public static function get_before_txt($acPhone, $day)
    {
        $type = StockAction::TYPE_DELETE;
        $sql = "select substring(aTypeTxt,1,3) 
                from im_stock_action 
                where aPhone=$acPhone and aType=$type and aAddedOn<'$day' order by aId desc LIMIT 1";
        $acTxtBefore = AppUtil::db()->createCommand($sql)->queryScalar();

        return $acTxtBefore ? $acTxtBefore : '';
    }

}
