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

class Order extends ActiveRecord
{
	const ST_DEFAULT = 1;
	const ST_PAY = 2;
	static $CatDict = [
		self::ST_DEFAULT => "未支付",
		self::ST_PAY => "已支付",
	];

	const ST_REMOVED = 9;

	public static function tableName()
	{
		return '{{%order}}';
	}

	public static function add($data)
	{
		if (!$data) {
			return false;
		}
		$entity = new self();
		foreach ($data as $key => $val) {
			$entity->$key = $val;
		}
		$entity->save();
		return $entity->oId;
	}

	public static function editByPId($pid)
	{
		$pInfo = Pay::findOne(["pId" => $pid]);
		if (!$pInfo) {
			return false;
		}
		$oid = $pInfo->pRId;
		$entity = self::findOne(["oId" => $oid]);
		$entity->oPayId = $pInfo->pId;
		$entity->oStatus = self::ST_PAY;
		$entity->oUpdatedOn = date("Y-m-d H:i:s");

		$entity->save();
		return $entity->oId;
	}

	public static function exchange($data, $unit)
	{
		if (!$data) {
			return false;
		}
		$uid = isset($data["oUId"]) ? isset($data["oUId"]) : 0;
		$amt = isset($data["oAmount"]) ? isset($data["oAmount"]) : 0;
		if ($unit == Goods::UNIT_FLOWER && $uid && $amt) {
			$tid = UserTrans::add($uid, 0, UserTrans::CAT_EXCHANGE_FLOWER, '', $amt, UserTrans::UNIT_GIFT, $note = '');
			$data["ST_DEFAULT"] = self::ST_PAY;
			$data["oPayId"] = $tid;
			return self::add($data);
		}
	}
}