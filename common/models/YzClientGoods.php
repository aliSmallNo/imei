<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 17/11/2016
 * Time: 7:32 PM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\ImageUtil;
use yii\db\ActiveRecord;

class YzClientGoods extends ActiveRecord
{

	const STATUS_DEFAULT = 2;

	static $StatusMap = [
		self::STATUS_DEFAULT => "默认",
	];


	public static function tableName()
	{
		return '{{%yz_client_goods}}';
	}

	public static function add($values = [])
	{
		if (!$values) {
			return 0;
		}
		$newItem = new self();
		foreach ($values as $key => $val) {
			$newItem->$key = $val;
		}

		$newItem->save();
		return $newItem->gId;

	}

	public static function mod($gId, $values)
	{
		if (!$gId || !$values) {
			return 0;
		}
		$newItem = self::findOne(["gId" => $gId]);

		foreach ($values as $key => $val) {
			$newItem->$key = $val;
		}

		$newItem->save();
		return $newItem->gId;

	}


	public static function del($id, $adminId = 0)
	{
		$item = self::findOne(["gId" => $id]);
		if ($item) {
			$item->gDeletedFlag = 1;
			$item->gDeletedDate = date("Y-m-d H:i:s");
			$item->gUpdatedDate = date("Y-m-d H:i:s");
			$item->gUpdatedBy = $adminId;
			$item->save();
		}
	}

	public static function validity($phone)
	{
		if (!$phone || !AppUtil::checkPhone($phone)) {
			return "手机号码格式不正确";
		}
		$ret = self::findOne(["cPhone" => $phone]);
		if ($ret) {
			return "手机号已经存在了，请勿重复添加";
		}
		return "";
	}

	public static function edit($params, $id = "", $adminId = 0)
	{
		$item = new self();
		$addFlag = false;
		if ($id) {
			$item = self::findOne(["gId" => $id]);
		} else {
			$item->gAddedBy = $adminId;
			$item->gStatus = self::STATUS_DEFAULT;
			$addFlag = true;
		}
		$fieldMap = [
			'clue_goods_name' => 'gName',
			'clue_goods_brand' => 'gBrand',
			'clue_goods_standards' => 'gStandards',
			'clue_goods_store' => 'gStore',
			'clue_goods_cycle' => 'gCycle',
			'clue_goods_price' => 'gPrice',
			'id' => 'gCId',
		];
		foreach ($params as $key => $val) {
			if (isset($fieldMap[$key])) {
				$field = $fieldMap[$key];
				$item[$field] = $val;
			}
		}

		$images = ImageUtil::upload2Server($_FILES['clue_goods_image'], 1);
		$item->gImage = json_encode($images);

		$item->save();

		return $item->gId;
	}

	public static function addFromUser($uid, $adminId = 0)
	{
		$conn = AppUtil::db();
		$sql = "select u.uId,u.uPhone,u.uName, u.uShopAddress, IFNULL(c.cId,0) as cid 
			from im_user as u 
 			left join im_yz_client as c on u.uPhone = c.cPhone
 			where u.uId=$uid ";
		$ret = $conn->createCommand($sql)->queryOne();
		if ($ret && $ret["cid"] > 0) {
			return [159, "手机号已经存在了，请勿重复添加"];
		}

		$address = $ret["uShopAddress"];
		$address = json_decode($address, true);
		$prov = $city = "";
		if ($address && is_array($address)) {
			$address = $address[0];
			if (isset($address['address'])) {
				list($city) = explode(" ", trim($address['address']));
				$sql = "select distinct provinceName, cityName from im_chinazone WHERE cityName=:city";
				$res = $conn->createCommand($sql)->bindValues([":city" => $city])->queryOne();
				if ($res) {
					$prov = $res["provinceName"];
				}
			}
		}

		$item = new self();
		$item->cName = $ret["uName"];
		$item->cPhone = $ret["uPhone"];
		$item->cProvince = $prov;
		$item->cCity = $city;
		$item->cUId = $ret["uId"];
		$item->cIntro = "从注册用户转化过来的";
		$item->cSource = self::SRC_WEBSITE;
		$item->cUpdatedDate = date("Y-m-d H:i:s");
		$item->cUpdatedBy = $adminId;
		$item->cAddedBy = $adminId;
		$item->save();

		CRMTrack::add($item->cId, [
			"status" => self::STATUS_FRESH,
			"note" => "从注册用户转化过来的"
		], $adminId);
		return [0, "添加客户线索成功！"];
	}

	public static function transfer()
	{
		$conn = AppUtil::db();
		$sql = "delete from im_yz_client";
		$conn->createCommand($sql)->execute();
		$sql = "delete from im_crm_track";
		$conn->createCommand($sql)->execute();

		$sql = "insert into im_yz_client(cName,cPhone,cWechat,cEmail,cProvince,cCity,cIntro,cSource,cAddedDate,cUpdatedDate,cNote,cBDAssignDate,cBDAssign)
			VALUES(:cName,:cPhone,:cWechat,:cEmail,:cProvince,:cCity,:cIntro,:cSource,:cAddedDate,:cAddedDate,:cNote,:cBDAssignDate,:cBDAssign)";
		$cmd = $conn->createCommand($sql);


		$sql = "select aId from im_admin where aName=:name";
		$cmdSel = $conn->createCommand($sql);

		$sql = "select * from im_message where mBranchId=1000 order by mPushDate";
		$ret = $conn->createCommand($sql)->queryAll();
		foreach ($ret as $row) {
			$info = $row["mContent"];
			$info = json_decode($info, true);

			$note = [
				"name" => $row["mName"],
				"phone" => $row["mPhone"],
				"wechat" => $info["wechat"],
				"intro" => $info["message"],
				"province" => $info["bigCat"],
				"city" => $info["smallCat"],
				"source" => self::SRC_WEBSITE,
				"addedDate" => $row["mPushDate"]
			];

			$aid = 0;
			$sel = $cmdSel->bindValues([":name" => $row["mAssignBD"]])->queryOne();
			if ($sel) {
				$aid = $sel["aId"];
			}

			$cmd->bindValues([
				":cEmail" => $row["mId"],
				":cName" => $row["mName"],
				":cPhone" => $row["mPhone"],
				":cWechat" => $info["wechat"],
				":cIntro" => $info["message"],
				":cProvince" => $info["bigCat"],
				":cCity" => $info["smallCat"],
				":cSource" => "website",
				":cAddedDate" => $row["mPushDate"],
				":cBDAssignDate" => $row["mAssignDate"],
				":cBDAssign" => $aid,
				":cNote" => json_encode($note)
			])->execute();
		}

		$sql = "insert into im_crm_track(tCId, tNote,tStatus,tDate,tAddedDate,tAddedBy)
  			SELECT cId,(case when cAddedBy>0 then '添加新的客户线索' else '客户在官网上填写的信息' END),100,
   	    	cAddedDate, cAddedDate, cAddedBy from im_yz_client";
		$conn->createCommand($sql)->execute();

		$sql = "insert into im_crm_track(tCId,tNote,tDate,tAddedDate,tAddedBy)
			SELECT c.cId,m.mBDNote,m.mBDNoteDate, m.mBDNoteDate, IFNULL(a.aId, 0)
			FROM im_yz_client as c 
			JOIN im_message as m on c.cEmail=m.mId AND m.mBDNote!='' 
			LEFT JOIN im_admin as a on a.aName=m.mAssignBD ";
		$conn->createCommand($sql)->execute();

		$conn->createCommand("update im_yz_client set cEmail=''")->execute();
	}

	public static function counts($aid, $criteria, $params = [])
	{
		$strCriteria = "";
		if ($criteria) {
			$strCriteria = " AND " . implode(" AND ", $criteria);
		}
		$sql = "select 
		count(case when cBDAssign=:aid then 1 else null end) as mine,
		count(case when cBDAssign=0 then 1 else null end) as sea,
		count(1) as cnt
 		from im_yz_client where cDeletedFlag=0 $strCriteria";
		$conn = AppUtil::db();
		$params[":aid"] = $aid;
		$ret = $conn->createCommand($sql)->bindValues($params)->queryOne();
		if ($ret) {
			return $ret;
		}
		return ["mine" => 0, "sea" => 0, "cnt" => 0];
	}

	public static function clients($criteria, $params = [], $sort = "dd", $page = 1, $pageSize = 20, $cFlag = false)
	{
		$items = [];
		$count = 0;
		$strCriteria = "";
		if ($criteria) {
			$strCriteria = " AND " . implode(" AND ", $criteria);
		}
		$sorts = [
			"dd" => "order by cUpdatedDate DESC",
			"da" => "order by cUpdatedDate ASC",
			"sd" => "order by cStatus DESC,cUpdatedDate DESC",
			"sa" => "order by cStatus ASC,cUpdatedDate DESC"
		];
		$orderBy = isset($sorts[$sort]) ? $sorts[$sort] : $sorts["dd"];
		$conn = AppUtil::db();
		$category = self::CATEGORY_YANXUAN;

		if ($cFlag) {
			$category = self::CATEGORY_ADVERT;
		}
		$sql = "select count(1) as cnt 
				FROM im_yz_client 
				WHERE cCategory=$category AND cDeletedFlag=0 $strCriteria";
		$ret = $conn->createCommand($sql)->bindValues($params)->queryOne();
		if ($ret) {
			$count = $ret["cnt"];
		}

		$offset = ($page - 1) * $pageSize;
		$limit = $pageSize + 1;
		$sql = "select IFNULL(a.aName,'') as bdName,
				c.cId,
				c.cName,
				c.cAge,
				c.cGender,
				c.cJob,
				c.cPhone,
				c.cWechat,
				c.cEmail,
				c.cCity,
				c.cProvince,
				c.cAddress,
				c.cIntro,
				c.cBDAssign,
				c.cBDAssignDate,
				c.cSource,
				c.cStatus,
				c.cAddedDate,
				c.cUpdatedDate,
				c.cAddedBy,
				c.cUpdatedBy
 				FROM im_yz_client as c 
				LEFT JOIN im_admin AS a ON c.cBDAssign = a.aId
 				WHERE cCategory=$category AND cDeletedFlag=0 $strCriteria 
 				$orderBy limit $offset, $limit";
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$nextPage = 0;
		if ($ret && count($ret) > $pageSize) {
			array_pop($ret);
			$nextPage = $page + 1;
		}
		foreach ($ret as $row) {
			$row["status"] = $row["cStatus"];
			if ($row["cStatus"] < 1) {
				$row["status"] = self::STATUS_DISLIKE;
			}
			$row["statusText"] = self::$StatusMap[$row["status"]];
			$row["genderText"] = self::$genderMap[$row["cGender"]] ?? '';
			$row["ageText"] = self::$ageMap[$row["cAge"]] ?? '';
			$row["percent"] = $row["status"] - 100;
			//$row["addedDate"] = Utils::prettyDateTime($row["cAddedDate"]);
			$row["addedDate"] = AppUtil::prettyDateTime($row["cAddedDate"]);
			$row["src"] = isset(self::$SourceMap[$row["cSource"]]) ? self::$SourceMap[$row["cSource"]] : "";
			$row["bdAbbr"] = $row["bdName"];
			if (mb_strlen($row["bdAbbr"]) > 2) {
				$row["bdAbbr"] = mb_substr($row["bdAbbr"], mb_strlen($row["bdAbbr"]) - 2, 2);
			}
			$row["assignDate"] = AppUtil::prettyDateTime($row["cBDAssignDate"]);
			$items[$row["cId"]] = $row;
		}


		$ids = array_keys($items);
		if ($ids) {
			$sql = "SELECT t.* 
					FROM im_crm_track as t
 					JOIN (select max(tId) as lastId,tCId from im_crm_track 
 					WHERE tDeletedFlag=0 AND tCId in (" . implode(",", $ids) . ") GROUP BY tCId) as c on c.lastId=t.tId";
			$ret = $conn->createCommand($sql)->queryAll();
			foreach ($ret as $row) {
				$cid = $row["tCId"];
				if (isset($items[$cid])) {
					$items[$cid]["lastId"] = $row["tId"];
					$items[$cid]["lastDate"] = AppUtil::prettyDateTime($row["tDate"]);
					$items[$cid]["lastNote"] = $row["tNote"];
				}
			}
		}

		return [array_values($items), $count, $nextPage];
	}

	public static function funnelStat($category, $beginDate, $endDate, $id = "", $conn = "")
	{
		$strCriteria = "";
		if ($id) {
			$strCriteria = " AND c.cBDAssign=" . $id;
		}
		$sql = "select count(1) as cnt,cStatus from im_yz_client as c
 			WHERE cCategory=$category AND cDeletedFlag=0 AND cUpdatedDate BETWEEN '$beginDate' AND '$endDate 23:59' $strCriteria
 			group by cStatus";
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$ret = $conn->createCommand($sql)->queryAll();
		$items = [];
		foreach ($ret as $key => $row) {
			$cnt = intval($row["cnt"]);
			$title = isset(self::$StatusMap[$row["cStatus"]]) ? self::$StatusMap[$row["cStatus"]] : self::$StatusMap[self::STATUS_DISLIKE];
			$color = isset(self::$StatusColors[$row["cStatus"]]) ? self::$StatusColors[$row["cStatus"]] : self::$StatusColors[self::STATUS_DISLIKE];
			$items[] = [$title, $cnt, $color];
		}
		return $items;
	}

	public static function sourceStat($category, $beginDate, $endDate, $id = "", $conn = "", $status = "")
	{
		$strCriteria = "";
		if ($id) {
			$strCriteria .= " AND c.cBDAssign=" . $id;
		}
		if ($status) {
			$strCriteria .= " AND cstatus = $status ";
		}
		$sql = "select COUNT(DISTINCT c.cId) as cnt, c.cSource
		 from im_yz_client as c 
		 join im_admin as a on a.aId=c.cBDAssign
		 WHERE c.cDeletedFlag=0 AND cCategory=$category 
		 and c.cAddedDate BETWEEN '$beginDate' and '$endDate 23:59' $strCriteria
		 GROUP BY c.cSource
		 order by cnt desc";
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$ret = $conn->createCommand($sql)->queryAll();
		$items = [];
		foreach ($ret as $key => $row) {
			$src = $row["cSource"];
			$items[] = [
				"name" => isset(self::$SourceMap[$src]) ? self::$SourceMap[$src] : self::$SourceMap[self::SRC_OTHER],
				"y" => intval($row["cnt"])
			];
		}
		return $items;
	}

	public static function statusDonut($category, $beginDate, $endDate, $id = "", $conn = "")
	{
		$strCriteria = "";
		if ($id) {
			$strCriteria = " AND c.cBDAssign=" . $id;
		}
		$sql = "select COUNT(DISTINCT c.cId) as cnt,c.cStatus,c.cSource
		 from im_yz_client as c 
		 join im_admin as a on a.aId=c.cBDAssign
		 WHERE c.cDeletedFlag=0 AND cCategory=$category 
		 and c.cAddedDate BETWEEN '$beginDate' and '$endDate 23:59' $strCriteria
		 and cStatus>100
		 GROUP BY c.cSource,c.cStatus
		 order by cStatus, cnt desc";
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$ret = $conn->createCommand($sql)->queryAll();
		$statusItems = $sourceItems = [];
		$amount = 0;
		foreach ($ret as $key => $row) {
			$src = $row["cSource"];
			$source = isset(self::$SourceMap[$src]) ? self::$SourceMap[$src] : self::$SourceMap[self::SRC_OTHER];
			$st = $row["cStatus"];
			$status = isset(self::$StatusMap[$st]) ? self::$StatusMap[$st] : self::$StatusMap[self::STATUS_DISLIKE];
			$color = isset(self::$StatusColors[$st]) ? self::$StatusColors[$st] : self::$StatusColors[self::STATUS_DISLIKE];
			$cnt = intval($row["cnt"]);
			if (!isset($statusItems[$st])) {
				$statusItems[$st] = [
					"name" => $status,
					"y" => 0,
					"color" => $color
				];
			}
			$statusItems[$st]["y"] += $cnt;
			$sourceItems[] = [
				"name" => $source,
				"y" => $cnt,
				"color" => $color,
				"sta" => $st,
				"y2" => $cnt
			];
			$amount += $cnt;
		}
		foreach ($statusItems as $k => $item) {
			$statusItems[$k]["y"] = floatval(sprintf("%.1f", $item["y"] / $amount * 100.0));
		}
		foreach ($sourceItems as $k => $item) {
			$sourceItems[$k]["y"] = floatval(sprintf("%.1f", $item["y"] / $amount * 100.0));
			$sourceItems[$k]["y2"] = floatval(sprintf("%.1f", $item["y2"] / $statusItems[$item["sta"]]["y"] * 100.0));
		}

		return [array_values($statusItems), $sourceItems];
	}

	public static function newClientStat($category, $beginDate, $endDate, $id = "", $conn = "")
	{
		$strCriteria = "";
		if ($id) {
			$strCriteria = " AND c.cBDAssign=" . $id;
		}
		$sql = "select COUNT(DISTINCT c.cId) as cnt, c.cBDAssign, a.aName as title
		 from im_yz_client as c 
		 join im_admin as a on a.aId=c.cBDAssign
		 WHERE c.cDeletedFlag=0 AND cCategory=$category AND a.aId not in (1453348809, 1453807803, 1467788165, 1843540)
		 and c.cAddedDate BETWEEN '$beginDate' and '$endDate 23:59' $strCriteria
		 GROUP BY c.cBDAssign,a.aName
		 order by cnt desc";
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$ret = $conn->createCommand($sql)->queryAll();
		$items = [];
		foreach ($ret as $key => $row) {
			$items[] = [$row["title"] . ' ' . ($key + 1), intval($row["cnt"])];
		}
		return $items;
	}

	public static function newClientStatDetail($category, $beginDate, $endDate, $id, $conn = "")
	{
		$strCriteria = "";
		if ($id) {
			$strCriteria = " AND c.cBDAssign=" . $id;
		}
		$sql = "select COUNT(DISTINCT c.cId) as cnt, c.cBDAssign, DATE_FORMAT(c.cAddedDate,'%m-%d') as title
		 from im_yz_client as c 
		 join im_admin as a on a.aId=c.cBDAssign
		 WHERE c.cDeletedFlag=0 AND cCategory=$category and a.aId not in (1453348809, 1453807803, 1467788165, 1843540)
		 and c.cAddedDate BETWEEN '$beginDate' and '$endDate 23:59' $strCriteria
		 GROUP BY c.cBDAssign, title
		 order by title";
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$ret = $conn->createCommand($sql)->queryAll();
		$items = [];
		for ($k = strtotime($beginDate); $k <= strtotime($endDate); $k += 86400) {
			$items[date("m-d", $k)] = 0;
		}
		foreach ($ret as $key => $row) {
			$items[$row["title"]] = intval($row["cnt"]);
		}
		return $items;
	}

	public static function clientStat($beginDate, $endDate, $category, $id = "", $conn = "")
	{
		$strCriteria = "";
		if ($id) {
			$strCriteria = " AND c.cBDAssign=" . $id;
		}
		$staffLevel = Admin::LEVEL_STAFF;
		$sql = "select a.aId, a.aName as title,c.cStatus,COUNT(1) as cnt 
 			from im_yz_client as c 
 			join im_admin as a on a.aId=c.cBDAssign and a.aLevel>=$staffLevel AND cCategory=$category and c.cDeletedFlag=0
 			 	and c.cAddedDate BETWEEN '$beginDate' and '$endDate 23:59' $strCriteria
 			group by a.aName,c.cStatus";
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$ret = $conn->createCommand($sql)->queryAll();
		$items = [];
		foreach ($ret as $key => $row) {
			$aid = $row["aId"];
			$status = isset(self::$StatusMap[$row["cStatus"]]) ? self::$StatusMap[$row["cStatus"]] : self::$StatusMap[self::STATUS_DISLIKE];
			if (!isset($items[$aid])) {
				foreach (self::$StatusMap as $val) {
					$items[$aid][$val] = 0;
				}
				$items[$aid]["cnt"] = 0;
			}
			$ret[$key]["cnt"] = intval($row["cnt"]);
			$items[$aid]["title"] = $row["title"];
			$items[$aid][$status] = intval($row["cnt"]);
			$items[$aid]["cnt"] += intval($row["cnt"]);
		}
		usort($items, function ($a, $b) {
			return $a["cnt"] < $b["cnt"];
		});

		$titles = [];
		$series = [];
		$map = self::$StatusMap;
		foreach ($items as $key => $item) {
			$titles[] = $item["title"];
			foreach ($map as $k => $status) {
				if (!isset($series[$status])) {
					$color = isset(self::$StatusColors[$k]) ? self::$StatusColors[$k] : self::$StatusColors[self::STATUS_DISLIKE];
					$series[$status] = [
						"name" => $status,
						"key" => $k,
						"color" => $color,
						"data" => []
					];
				}
				$series[$status]["data"][] = isset($item[$status]) ? $item[$status] : 0;
			}
		}
		$series = array_values($series);
		usort($series, function ($a, $b) {
			return $a["key"] < $b["key"];
		});
		return [$series, $titles];
	}

	public static function getList($condition, $page, $pagesize)
	{
		$offset = ($page - 1) * $pagesize;
		$sql = "select * from im_yz_client 
			  where cCategory=110 and cDeletedFlag=0 $condition
			  order by cUpdatedDate desc
			  limit $offset,$pagesize";

		$conn = AppUtil::db();
		$ret = $conn->createCommand($sql)->queryAll();
		$sql = "select count(*) as co from im_yz_client where cCategory=110 and cDeletedFlag=0 $condition";
		$count = 0;
		if ($resCount = $conn->createCommand($sql)->queryOne()) {
			$count = $resCount["co"];
		}
		foreach ($ret as &$v) {

		}
		return [$ret, $count];
	}


}