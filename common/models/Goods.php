<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 13/12/2017
 * Time: 9:39 PM
 */

namespace common\models;


use common\utils\AppUtil;
use yii\db\ActiveRecord;

class Goods extends ActiveRecord
{
	const CAT_BAG = 100;
	const CAT_STUFF = 110;
	const CAT_PREMIUM = 120;
	static $CatDict = [
		self::CAT_BAG => '特权礼包',
		self::CAT_STUFF => '普通礼物',
		self::CAT_PREMIUM => '特权礼物',
	];

	const ST_OFFLINE = 0;
	const ST_ONLINE = 1;
	const ST_REMOVED = 9;

	const UNIT_FLOWER = "媒桂花";
	const UNIT_YUAN = "元";

	public static function tableName()
	{
		return '{{%goods}}';
	}

	public static function items($criteria, $page = 1, $pageSize = 20)
	{
		$strCriteria = '';
		$params = [];
		foreach ($criteria as $field => $val) {
			if ($field == 'gName') {
				$strCriteria .= ' AND ' . $field . ' like :' . $field;
				$params[':' . $field] = '%' . $val . '%';
			} else {
				$strCriteria .= ' AND ' . $field . ' =:' . $field;
				$params[':' . $field] = $val;
			}
		}
		$conn = AppUtil::db();
		$limit = ' limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
		$sql = "select gId as id,gCategory as cat,gName as `name`,gImage as image,gPrice as price,gUnit as unit
		 FROM im_goods 
		 WHERE gId>0 " . $strCriteria . $limit;
		return $conn->createCommand($sql)->bindValues($params)->queryAll();

	}

	public static function getGiftList($subtag, $uid)
	{
		$ret = [];
		$conn = AppUtil::db();
		$sql = "select gId as id,gCategory as cat,gName as `name`,gImage as image,gPrice as price,gUnit as unit
				 FROM im_goods 
				 WHERE gId>0 and gCategory=:cat and gStatus=:st";
		$CMD = $conn->createCommand($sql);
		switch ($subtag) {
			case "normal":
				$res = $CMD->bindValues([
					':cat' => Goods::CAT_STUFF,
					':st' => 1,
				])->queryAll();
				break;
			case "vip":
				$res = $CMD->bindValues([
					':cat' => Goods::CAT_PREMIUM,
					':st' => 1,
				])->queryAll();
				break;
			case "bag":
				$sql = "select gId as id,gCategory as cat,gName as `name`,gImage as image,gPrice as price,gUnit as unit
						FROM im_goods as g join im_order as o on o.`oGId`=g.gId 
						where o.oStatus=:st and oUId=:uid and g.gCategory in (110,120)";
				$res = $conn->createCommand($sql)->bindValues([
					":uid" => $uid,
					":st" => Order::ST_PAY,
				])->queryAll();
				break;
		}
		if ($res) {
			foreach ($res as $k => $v) {
				$ret[floor($k / 8)]["items"][] = $v;
			}
		}
		return $ret;
	}
}