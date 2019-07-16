<?php

namespace common\models;

use admin\models\Admin;
use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_stock_user_admin".
 *
 * @property integer $uaId
 * @property string $uaPhone
 * @property string $uaName
 * @property string $uaPtPhone
 * @property string $uaPtName
 * @property integer $uaType
 * @property string $uaNote
 * @property integer $uaStatus
 * @property string $uaAddedOn
 * @property integer $uaAddedBy
 * @property string $uaUpdatedOn
 * @property integer $uaUpdatedBy
 */
class StockUserAdmin extends \yii\db\ActiveRecord
{
    const TYPE_DEFAULT = 1;
    const TYPE_PARTNER = 2;
    static $types = [
        self::TYPE_DEFAULT => '普通用户',
        self::TYPE_PARTNER => 'BD',
    ];

    const ST_USE = 1;
    const ST_VOID = 9;
    static $stDict = [
        self::ST_USE => '有效',
        self::ST_VOID => '无效',
    ];

    /* public static function tableName()
     {
         return '{{%stock_user}}';
     }
     */

    public static function tableName()
    {
        return 'im_stock_user_admin';
    }

    public function rules()
    {
        return [
            [['uaType', 'uaStatus', 'uaAddedBy', 'uaUpdatedBy'], 'integer'],
            [['uaAddedOn', 'uaUpdatedOn'], 'safe'],
            [['uaPhone', 'uaPtPhone'], 'string', 'max' => 16],
            [['uaName', 'uaPtName'], 'string', 'max' => 128],
            [['uaNote'], 'string', 'max' => 256],
        ];
    }

    public function attributeLabels()
    {
        return [
            'uaId' => 'Ua ID',
            'uaPhone' => 'Ua Phone',
            'uaName' => 'Ua Name',
            'uaPtPhone' => 'Ua Pt Phone',
            'uaPtName' => 'Ua Pt Name',
            'uaType' => 'Ua Type',
            'uaNote' => 'Ua Note',
            'uaStatus' => 'Ua Status',
            'uaAddedOn' => 'Ua Added On',
            'uaAddedBy' => 'Ua Added By',
            'uaUpdatedOn' => 'Ua Updated On',
            'uaUpdatedBy' => 'Ua Updated By',
        ];
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
        $entity->uaStatus = self::ST_USE;
        $entity->uaAddedOn = date('Y-m-d H:i:s');
        $entity->uaAddedBy = Admin::getAdminId();
        $entity->save();
        return $entity->uaId;
    }

    public static function edit($id, $values = [])
    {
        if (!$values) {
            return false;
        }
        $entity = self::findOne(['uaId' => $id]);
        if (!$entity) {
            return false;
        }
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->uaUpdatedOn = date('Y-m-d H:i:s');
        $entity->uaUpdatedBy = Admin::getAdminId();
        $entity->save();
        return $entity->uaId;
    }

    public static function edit_admin($uaId, $uaPhone, $uaPtPhone, $uaStatus, $uaNote)
    {
        $data = [
            'uaPhone' => $uaPhone,
            'uaPtPhone' => $uaPtPhone,
            'uaStatus' => $uaStatus,
            'uaNote' => $uaNote,
        ];

        if (!AppUtil::checkPhone($uaPhone)) {
            return [0, '渠道手机格式不正确', $data];
        }
        if ($uaPtPhone && !AppUtil::checkPhone($uaPtPhone)) {
            return [0, 'BD手机格式不正确', $data];
        }
        if ($uaPtPhone == $uaPhone) {
            return [0, '不用给自己分配', $data];
        }
        if (!array_key_exists($uaStatus, self::$stDict)) {
            return [0, '状态未填写', $data];
        }

        $stock_user1 = StockUser::findOne(['uPhone' => $uaPhone]);
        $stock_user2 = StockUser::findOne(['uPhone' => $uaPtPhone]);
        if (!$stock_user1) {
            return [0, '渠道手机号填写有误', $data];
        }
        if (!$stock_user2) {
            return [0, 'BD手机号填写有误', $data];
        }

        $data = array_merge($data, [
            'uaName' => $stock_user1->uName,
            'uaPtName' => $stock_user2->uName,
        ]);

        $user_admin1 = self::findOne(['uaPhone' => $uaPhone, 'uaStatus' => self::ST_USE]);
        $user_admin2 = self::findOne($uaId);

        if ($user_admin2) {
            if ($user_admin1 && $user_admin1->uaId != $user_admin2->uaId) {
                return [0, '此渠道已分配BD', $data];
            }
            $res = self::edit($user_admin2->uaId, $data);
            $edit_st = "修改";
        } else {
            if ($user_admin1) {
                return [0, '此渠道已分配BD', $data];
            }
            $res = self::add($data);
            $edit_st = "添加";
        }
        $res_text = $res ? '成功' : '失败';
        $code = $res ? 0 : 129;

        return [$code, $edit_st . $res_text, $data];

    }

    public static function items($criteria, $params, $page, $pageSize = 20)
    {

        $offset = ($page - 1) * $pageSize;
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND ' . implode(' AND ', $criteria);
        }

        $order_str = " uaUpdatedOn desc";

        $sql = "select *
				from im_stock_user_admin  
				where uaStatus=:uaStatus $strCriteria 
				order by  $order_str
				limit $offset,$pageSize";
        $res = AppUtil::db()->createCommand($sql, [':uaStatus' => self::ST_USE])->bindValues($params)->queryAll();
        foreach ($res as $k => $v) {
            $res[$k]['st_t'] = self::$stDict[$v['uaStatus']];
        }
        $sql = "select count(1) as co
				from im_stock_user_admin  
				 where uaStatus=:uaStatus $strCriteria  ";
        $count = AppUtil::db()->createCommand($sql, [':uaStatus' => self::ST_USE])->bindValues($params)->queryScalar();

        return [$res, $count];
    }

    public static function bds()
    {
        $sql = "select * from im_stock_user_admin group by uaPtPhone order by uaPtPhone asc ";
        $res = AppUtil::db()->createCommand($sql)->queryAll();
        return array_combine(array_column($res, 'uaPtPhone'), array_column($res, 'uaPtName'));
    }
}
