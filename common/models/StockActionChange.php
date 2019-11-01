<?php

namespace common\models;

use admin\models\Admin;
use common\utils\AppUtil;
use Yii;
use yii\helpers\VarDumper;

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

    /**
     * 添加
     * @since 2019.8.27 PM
     * @author zp
     */
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

    /**
     * 添加前的检查
     * @since 2019.8.27 PM
     * @author zp
     */
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

    /**
     * 判断唯一性
     * @since 2019.8.27 PM
     * @author zp
     */
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

    /**
     * @param string $day 日期
     * @since 2019.8.27 PM
     * @author zp
     */
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

    /**
     * 整理要添加的数据
     * @since 2019.8.27 PM
     * @author zp
     */
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

    /**
     * 获取 acAeforeTxt 字段信息
     * @since 2019.8.27 PM
     * @author zp
     */
    public static function get_before_txt($acPhone, $day)
    {
        $type = StockAction::TYPE_DELETE;
        $sql = "select substring(aTypeTxt,1,3) 
                from im_stock_action 
                where aPhone=$acPhone and aType=$type and aAddedOn<'$day' order by aId desc LIMIT 1";
        $acTxtBefore = AppUtil::db()->createCommand($sql)->queryScalar();

        return $acTxtBefore ? $acTxtBefore : '';
    }

    // 渠道限制条件
    public static function channel_condition()
    {
        $cond = "";
        $phone = Admin::get_phone();
        $cId = Admin::getAdminId();
        if (!Admin::isGroupUser(Admin::GROUP_STOCK_LEADER)) {
            $cond = " and c.cBDAssign=$cId ";
        }
        return $cond;
    }

    public static function items($criteria, $params, $page, $pageSize = 20)
    {
        $conn = AppUtil::db();
        $offset = ($page - 1) * $pageSize;
        $strCriteria = '';
        if ($criteria) {
            $strCriteria = ' AND ' . implode(' AND ', $criteria);
        }

        $strCriteria .= self::channel_condition();

        $order_str = " acAddedOn desc,cUpdatedDate asc";

        $sql = "select 
                ac.*,
                c.*,
                a.aName,a.aPhone
				from im_stock_action_change as ac
				left join im_crm_stock_client as c on c.cPhone=ac.acPhone 
				left join im_admin as a on a.aId = c.cBDAssign 
				where acId>0 $strCriteria 
				order by  $order_str
				limit $offset,$pageSize";
        $res = $conn->createCommand($sql, [])->bindValues($params)->queryAll();
        if (!$res) {
            return [[], 0];
        }
        foreach ($res as $k => $v) {
            $ids[] = $v["cId"];
        }
        //去重
        $ids = array_unique($ids);
        // 去除空值
        $ids = array_filter($ids);
        if ($ids) {
            $sql = "SELECT t.* 
					FROM im_crm_stock_track as t
 					JOIN (
 					select max(tId) as lastId,tCId from im_crm_stock_track 
 					WHERE tDeletedFlag=0 and tAction=100 
 					AND tCId in (" . implode(",", $ids) . ") GROUP BY tCId
 					) as c on c.lastId=t.tId ";
            $ret = $conn->createCommand($sql)->queryAll();
            $items = [];
            foreach ($ret as $row) {
                $cid = $row["tCId"];

                $items[$cid]["lastId"] = $row["tId"];
                $items[$cid]["lastDate"] = AppUtil::prettyDateTime($row["tDate"]);
                $items[$cid]["lastNote"] = $row["tNote"];
            }

            // 插入最近的一条跟进信息
            foreach ($res as $k2 => $v2) {
                $cId = $v2['cId'];
                if (isset($items[$cId])) {
                    $res[$k2] = array_merge($v2, $items[$cId]);
                } else {
                    $res[$k2] = array_merge($v2, [
                        'lastId' => '',
                        'lastDate' => '',
                        'lastNote' => '无跟进信息',
                    ]);
                }
            }
        }

        $sql = "select count(1) as co
				from im_stock_action_change as ac
				left join im_crm_stock_client as c on c.cPhone=ac.acPhone 
				left join im_admin as a on a.aId = c.cBDAssign 
				where acId>0 $strCriteria ";
        $count = $conn->createCommand($sql, [])->bindValues($params)->queryScalar();

        return [$res, $count];
    }
}
