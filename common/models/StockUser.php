<?php

namespace common\models;

use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\Pinyin;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "im_stock_user".
 *
 * @property integer $uId
 * @property string $uPhone
 * @property string $uName
 * @property string $uPtPhone
 * @property string $uPtName
 * @property string $uRate
 * @property string $uType
 * @property string $uNote
 * @property integer $uStatus
 * @property string $uAddedOn
 * @property string $uUpdatedOn
 * @property string $uContributeRate
 */
class StockUser extends \yii\db\ActiveRecord
{

    const TYPE_DEFAULT = 1;
    const TYPE_PARTNER = 2;
    static $types = [
        self::TYPE_DEFAULT => '普通用户',
        self::TYPE_PARTNER => '渠道',
    ];

    public static function tableName()
    {
        return '{{%stock_user}}';
    }

    public function rules()
    {
        return [
            [['uStatus'], 'integer'],
            [['uAddedOn'], 'safe'],
            [['uPhone'], 'string', 'max' => 16],
            [['uName'], 'string', 'max' => 128],
            [['uNote'], 'string', 'max' => 256],
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
        $entity->save();
        return $entity->uId;
    }

    public static function edit($id, $values = [])
    {
        if (!$values) {
            return false;
        }
        $uPtPhone = isset($values['uPtPhone']) && AppUtil::checkPhone($values['uPtPhone']) ? $values['uPtPhone'] : '';
        if ($uPtPhone) {
            $pt_user = self::findOne(['uPhone' => $uPtPhone, 'uType' => self::TYPE_PARTNER]);
            if ($pt_user) {
                $values['uPtName'] = $pt_user['uName'];
            }
        } else {
            $values['uPtName'] = null;
        }
        $entity = self::findOne(['uId' => $id]);
        if (!$entity) {
            return false;
        }
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->uUpdatedOn = date('Y-m-d H:i:s');
        $entity->save();
        return $entity->uId;
    }

    public static function edit_by_phone($phone, $values = [])
    {
        if (!$values) {
            return false;
        }
        $entity = self::findOne(['uPhone' => $phone]);
        if (!$entity) {
            return false;
        }
        foreach ($values as $key => $val) {
            $entity->$key = $val;
        }
        $entity->save();
        return $entity->uId;
    }

    public static function pre_add($phone, $values)
    {
        $user = self::findOne(['uPhone' => $phone]);
        if ($user) {
            // 2018-1-21
//			if (!AppUtil::hasHans($user->uName) && mb_strlen($user->uName) == 11 && AppUtil::hasHans($values['uName'])) {
//				self::edit($user->uId, [
//					"uName" => $values['uName'],
//				]);
//			}
            return false;
        }
        if (isset($values['uName']) && isset($values['uPhone']) && AppUtil::checkPhone($values['uPhone'])) {
            return self::add($values);
        }
        return false;
    }

    public static function edit_admin($uName, $uPhone, $uPtPhone, $uRate, $uType, $uNote)
    {
        $data = [
            'uName' => $uName,
            'uPhone' => $uPhone,
            'uPtPhone' => $uPtPhone,
            'uNote' => $uNote,
            'uRate' => $uRate,
            'uType' => $uType,
        ];
        if (!AppUtil::checkPhone($uPhone)) {
            return [0, '用户手机格式不正确', $data];
        }
        if (!$uName) {
            return [0, '用户名不能为空', $data];
        }
        if ($uPtPhone && !AppUtil::checkPhone($uPtPhone)) {
            return [0, '渠道手机格式不正确', $data];
        }
        $user = self::findOne(['uPhone' => $uPhone]);

        if ($user) {
            $res = self::edit($user->uId, $data);
            $edit_st = "修改";
        } else {
            $res = self::add($data);
            $edit_st = "添加";
        }
        $res_text = $res ? '成功' : '失败';
        $code = $res ? 0 : 129;

        return [$code, $edit_st . $res_text, $data];

    }

    public static function bds()
    {
        $res = self::find()->where(['uType' => self::TYPE_PARTNER])->asArray()->all();
        return array_combine(array_column($res, 'uPhone'), array_column($res, 'uName'));
    }

    public static function items($criteria, $params, $page, $order_f = '', $pageSize = 20)
    {
        $offset = ($page - 1) * $pageSize;
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND ' . implode(' AND ', $criteria);
        }

        if ($order_f == 'last_opt_asc') {
            $order_str = " uLastOptOn asc";
        } elseif ($order_f == 'last_opt_desc') {
            $order_str = " uLastOptOn desc";
        } else {
            $order_str = " uUpdatedOn desc";
        }

        $cond = StockOrder::channel_condition();

        $sql = "select *
				from im_stock_user  
				where uId>0 $strCriteria $cond
				order by  $order_str
				limit $offset,$pageSize";
        $res = AppUtil::db()->createCommand($sql)->bindValues($params)->queryAll();
        foreach ($res as $k => $v) {
            $res[$k]['type_t'] = self::$types[$v['uType']];
            $res[$k]['opt_dt'] = date('Y-m-d', strtotime($v['uLastOptOn']));
        }
        $sql = "select count(1) as co
				from im_stock_user  
				 where uId>0 $strCriteria $cond ";
        $count = AppUtil::db()->createCommand($sql)->bindValues($params)->queryScalar();

        return [$res, $count];
    }

    /*
     * 更新最近一次的订单操作
     */
    public static function update_last_opt()
    {
        $conn = AppUtil::db();
        $sql = "select * from im_stock_user order by uId desc";
        $users = $conn->createCommand($sql)->queryAll();

        foreach ($users as $user) {
            self::update_last_opt_one($user, $conn);
        }
    }

    public static function update_last_opt_one($user, $conn)
    {
        $phone = $user['uPhone'];
        $sql = "select * from im_stock_order where oPhone=:phone order by oId desc limit 1";
        $order = $conn->createCommand($sql)->bindValues([':phone' => $phone])->queryOne();
        if ($order) {
            $oid = $order['oId'];
            $add = $order['oAddedOn'];
            self::edit_by_phone($phone, [
                "uLastOptOn" => $add,
                "uLastOptOId" => $oid,
            ]);
        }
    }

    /**
     *
     * @time 2019-12-11 PM
     */
    public static function get_partners()
    {
        $type = StockUser::TYPE_PARTNER;
        $sql = "select uName,uPhone from im_stock_user where uType=$type";
        $salers = AppUtil::db()->createCommand($sql)->queryAll();

        foreach ($salers as $k => $v) {
            $name = Pinyin::encode($v['uName'], 'all');
            $name = str_replace(" ", '', ucwords($name));
            $salers[$k]['pinyin_name'] = $name;
        }

        $partners = ArrayHelper::map($salers, 'pinyin_name', 'uName');
        $partners['total'] = '合计';

        return $partners;

    }

}
