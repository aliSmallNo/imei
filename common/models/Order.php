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
		$oNum = $entity->oNum;
		$gInfo = Goods::findOne(["gId" => $entity->oGId])->toArray();
		if ($gInfo["gDesc"]) {
			$desc = json_decode($gInfo["gDesc"], 1);
			self::addByDesc($desc, $uid, $oNum, $pid, $oid);
		}
		return $entity->oId;
	}

	public static function addByDesc($desc, $uid, $oNum = 1, $pid = 0, $oid = 0)
	{
		// 礼包商品
		if (isset($desc["glist"]) && $desc["glist"]) {
			foreach ($desc["glist"] as $g) {
				Order::add(["oUId" => $uid, "oGId" => $g["gid"], "oNum" => $g["num"] * $oNum, "oAmount" => 0, "oStatus" => self::ST_PAY, "oNote" => $oid]);
			}
		}
		// 礼包卡(目前只有月卡，三天卡，七天卡赠送)
		if (isset($desc["klist"]) && $desc["klist"]) {
			foreach ($desc["klist"] as $k) {
				if ($k["cat"] == "chat_month") {
					for ($i = 0; $i < $oNum; $i++) {
						UserTag::addByPId(UserTag::CAT_CHAT_MONTH, $pid, '', $oid);
					}
				}
				if ($k["cat"] == "chat_3") {
					for ($i = 0; $i < $oNum; $i++) {
						UserTag::addByPId(UserTag::CAT_CHAT_DAY3, $pid, '', $oid);
					}
				}
				if ($k["cat"] == "chat_7") {
					for ($i = 0; $i < $oNum; $i++) {
						UserTag::addByPId(UserTag::CAT_CHAT_DAY7, $pid, '', $oid);
					}
				}
			}
		}
		return true;
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
	 * 我的背包(我收到的礼物 我的背包里礼物 我送出去的礼物)
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
						where oUId=:uid and gCategory in (110,120)
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
						where oUId=:uid  and gCategory in (110,120)
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
						where oUId=:uid  and gCategory in (110,120)
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
				return [129, '您的等级不够~', ["gid" => 0]];
				break;
		}

		$msg = '<button data-tag="shopbag">' . "礼物: " . $gInfo["name"] . '</button>';
		$info = ChatMsg::addChat($wx_uid, $sid, $msg);
		return [0, '赠送成功~', $info];
	}

	public static function santaExchange($gid, $uid)
	{
		$sugar = Log::SANTA_SUGAR;
		$hat = Log::SANTA_HAT;
		$sock = Log::SANTA_SOCK;
		$olaf = Log::SANTA_OLAF;
		$tree = Log::SANTA_TREE;
		$sj = [
			6021 => [$sugar => 12, $hat => 12, $sock => 3, $olaf => 3, $tree => 1],
			6022 => [$sugar => 6, $hat => 6, $sock => 1, $olaf => 1, $tree => 0],
			6023 => [$sugar => 3, $hat => 3, $sock => 1, $olaf => 1, $tree => 0],
		];
		$gInfo = Goods::items(['gCategory' => Goods::CAT_BAG, 'gStatus' => 1, 'gId' => $gid])[0];
		if (!$gInfo) {
			return [129, '商品不存在~', ''];
		}
		$desc = json_decode($gInfo["desc"], 1);
		if (!$desc) {
			return [129, '订单错误~', ''];
		}
		// 是否兑换过
		if (Order::findOne(["oGId" => $gid, "oUId" => $uid])) {
			return [129, '您已经兑换过了~', ''];
		}
		// 是否集齐
		$stat = Log::santaStat($uid);
		// 测试 $stat = ['sugar' => 5, 'hat' => 12, 'sock' => 3, 'olaf' => 3, 'tree' => 0];$gid = 6022;
		if ($stat["sugar"] < $sj[$gid][$sugar]
			|| $stat["hat"] < $sj[$gid][$hat]
			|| $stat["sock"] < $sj[$gid][$sock]
			|| $stat["olaf"] < $sj[$gid][$olaf]
			|| ($gid == 6021 && $stat["tree"] < $sj[$gid][$tree])
		) {
			return [129, '还没集齐哦~', ''];
		}

		// 添加
		$oid = Order::add(["oUId" => $uid, "oGId" => $gid, "oNum" => 1, "oAmount" => 0, "oStatus" => self::ST_PAY]);
		self::addByDesc($desc, $uid, 1, 'santa', $oid);

		// 扣除道具
		foreach ($sj[$gid] as $k => $co) {
			if ($co == 0) {
				continue;
			}
			Log::add(['oUId' => $uid, 'oKey' => $k, 'oCategory' => Log::CAT_SANTA, 'oBefore' => -$co, 'oAfter' => $oid]);
		}
		return [0, "兑换成功", ''];
	}

	public static function hasGetMouthGift($uid, $gid = 6024)
	{
		$dt = date("Y-m");
		$sql = "select count(1) from im_order where oUId=:uid and oGId=:gid and oAddedOn like '%$dt%'  ";
		$co = AppUtil::db()->createCommand($sql)->bindValues([
			":uid" => $uid,
			":gid" => $gid,
		])->queryScalar();

		return $co;
	}
}