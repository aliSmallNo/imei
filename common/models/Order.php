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
	const ST_GIVE = 3;
	const ST_RECEIVE = 9;
	static $CatDict = [
		self::ST_DEFAULT => "未支付",
		self::ST_PAY => "已支付",
		self::ST_GIVE => "已赠送",
		self::ST_RECEIVE => "已收到",
	];


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

		// 处理礼包内的商品
		$uid = $entity->oUId;
		$oNum = $entity->oNUm;
		$gInfo = Goods::findOne(["gId" => $entity->oGId])->toArray();
		if ($gInfo["gDesc"]) {
			$desc = json_decode($gInfo["gDesc"], 1);
			// 礼包商品
			if (isset($desc["glsit"]) && $desc["glsit"]) {
				foreach ($desc["glsit"] as $g) {
					Order::add(["oUId" => $uid, "oGId" => $g["gid"], "oNum" => $g["num"] * $oNum, "oAmount" => 0, "oStatus" => self::ST_PAY, "oNote" => $oid]);
				}
			}
			// 礼包卡(目前只有月卡赠送)
			if (isset($desc["klsit"]) && $desc["klsit"]) {
				foreach ($desc["klsit"] as $k) {
					if ($k["cat"] == "chat_month") {
						for ($i = 0; $i < $oNum; $i++) {
							UserTag::addByPId(UserTag::CAT_CHAT_MONTH, $pid);
						}
					}
				}
			}
		}

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

	/**
	 * 我的背包(我收到的礼物 我的背包里礼物 我的功能卡)
	 * @param $subtag
	 * @param int $page
	 * @param int $pagesize
	 * @return array
	 * @throws \yii\db\Exception
	 */
	public static function QTItems($uid, $subtag, $page = 1, $pagesize = 12)
	{
		$conn = AppUtil::db();
		$nextpage = 0;
		$limit = " limit " . ($page - 1) * $pagesize . ',' . ($pagesize + 1);
		$ret = [];
		switch ($subtag) {
			case "gift":
				$sql = "select g.*,sum(case when oStatus=2 then oNum when oStatus=3 then -oNum end) as co from im_order as o 
						join im_goods as g on o.oGId=g.gId
						where oUId=:uid 
						group by oGId 
						having co>0 
						order by oId desc $limit";
				$ret = $conn->createCommand($sql)->bindValues([
					":uid" => $uid
				])->queryAll();
				break;
			case "receive":
				$sql = "select g.*,sum(case when oStatus=9 then oNum  end) as co,oAddedOn as dt from im_order as o 
						join im_goods as g on o.oGId=g.gId
						where oUId=:uid  
						group by oGId 
						having co>0
						order by gPrice asc $limit";
				$ret = $conn->createCommand($sql)->bindValues([
					":uid" => $uid
				])->queryAll();
				break;
			case "sent":
				$sql = "select g.*,sum(case when oStatus=3 then oNum  end) as co,oAddedOn as dt from im_order as o 
						join im_goods as g on o.oGId=g.gId
						where oUId=:uid  
						group by oGId 
						having co>0
						order by gPrice asc $limit";
				$ret = $conn->createCommand($sql)->bindValues([
					":uid" => $uid
				])->queryAll();
				break;
		}
		if ($ret) {
			if (count($ret) > $pagesize) {
				$nextpage = $page++;
			}
		}
		return [$ret, $nextpage];
	}

	public static function giveGift($subtag, $sid, $gid, $wx_uid)
	{
		$gInfo = Goods::items(["gId" => $gid]);
		if (!$gInfo) {
			return [129, '商品错误~', ''];
		}
		$num = 1;
		$gInfo = $gInfo[0];
		$amt = $gInfo["price"] * $num;
		$unit = $gInfo["unit"];
		$insertData = [
			"oUId" => $wx_uid, "oGId" => $gid, "oNum" => $num, "oAmount" => $amt
		];

		$giveTo = function ($sid, $wx_uid, $insertData) {
			$insertData["oStatus"] = Order::ST_GIVE;
			$insertData["oPayId"] = $sid;
			Order::add($insertData);// 送出

			$insertData["oUId"] = $sid;
			$insertData["oPayId"] = $wx_uid;
			$insertData["oStatus"] = Order::ST_RECEIVE;
			Order::add($insertData);// 得到
		};
		$conn = AppUtil::db();
		switch ($subtag) {
			case "bag":
				$sql = "select sum(case when oStatus=2 then oNum when oStatus=3 then -oNum end) as co from im_order where oGId=:gid";
				$co = $conn->createCommand($sql)->bindValues([":gid" => $gid])->queryScalar();
				if ($co <= 0) {
					return [129, '商品数错误~', ''];
				}
				$giveTo($sid, $wx_uid, $insertData);
				break;
			case "normal":
				$flower = UserTrans::getStat($wx_uid, true)["flower"];
				if ($flower < $amt) {
					return [128, '您的账户媒瑰花数量不足~', ''];
				}
				$tid = UserTrans::add($wx_uid, 0, UserTrans::CAT_EXCHANGE_CHAT, '', $amt, UserTrans::UNIT_GIFT, $note = '');
				$insertData["oStatus"] = self::ST_PAY;
				$insertData["oPayId"] = $tid;
				Order::add($insertData);// 购买

				$giveTo($sid, $wx_uid, $insertData);
				break;
			case "vip":
				// $expInfo = UserTag::getExp($v["uId"]);
				return [129, '您的等级不够~', ''];
				break;
		}

		$msg = '<button href="/wx/shopbag">' . "礼物: " . $gInfo["name"] . '</button>';
		$info = ChatMsg::addChat($wx_uid, $sid, $msg);
		return [0, '赠送成功~', $info];
	}
}