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

	public static function editByPId($pid, $tid)
	{
		$pInfo = Pay::findOne(["pId" => $pid]);
		if (!$pInfo) {
			return false;
		}
		$oid = $pInfo->pRId;
		$entity = self::findOne(["oId" => $oid]);
		$entity->oPayId = $tid;
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
		$uid = isset($data["oUId"]) ? $data["oUId"] : 0;
		$amt = isset($data["oAmount"]) ? $data["oAmount"] : 0;
		if ($unit == Goods::UNIT_FLOWER && $uid && $amt) {
			$tid = UserTrans::add($uid, 0, UserTrans::CAT_EXCHANGE_FLOWER, '', $amt, UserTrans::UNIT_GIFT, $note = '');
			$data["oStatus"] = self::ST_PAY;
			$data["oPayId"] = $tid;
			return self::add($data);
		}
	}

	public static function QTItems($subtag, $page = 1, $pagesize = 12)
	{
		$conn = AppUtil::db();
		$nextpage = 0;
		$limit = " limit " . ($page - 1) * $pagesize . ',' . ($pagesize + 1);
		$ret = [];
		switch ($subtag) {
			case "gift":
				$sql = "select g.*,sum(oNum) as co from im_order as o 
						join im_goods as g on o.oGId=g.gId
						where oStatus=:st 
						group by oGId 
						order by oId desc $limit";
				$ret = $conn->createCommand($sql)->bindValues([
					":st" => Order::ST_PAY
				])->queryAll();
				break;
			case "receive":
				$sql = "select g.*,sum(oNum) as co,oAddedOn as dt from im_order as o 
						join im_goods as g on o.oGId=g.gId
						where oStatus=:st 
						group by oGId 
						order by oId desc $limit";
				$ret = $conn->createCommand($sql)->bindValues([
					":st" => Order::ST_PAY
				])->queryAll();
				break;
			case "prop":

				break;
		}
		if ($ret) {
			if (count($ret) > $pagesize) {
				$nextpage = $page++;
			}
		}
		return [$ret, $nextpage];
	}
}