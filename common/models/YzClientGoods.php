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

	public static function clients($criteria, $params = [], $page = 1, $pageSize = 20)
	{
		$items = [];
		$count = 0;
		$strCriteria = "";
		if ($criteria) {
			$strCriteria = " AND " . implode(" AND ", $criteria);
		}

		$conn = AppUtil::db();

		$sql = "select count(1) as cnt 
				FROM im_yz_client_goods as g 
				LEFT JOIN im_yz_client AS c ON c.cId = g.gCId 
				WHERE cDeletedFlag=0 $strCriteria";
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();

		$offset = ($page - 1) * $pageSize;
		$limit = $pageSize + 1;

		$sql = "select g.*,c.*
 				FROM im_yz_client_goods as g 
				LEFT JOIN im_yz_client AS c ON c.cId = g.gCId
 				WHERE cDeletedFlag=0 $strCriteria 
 				order by gAddedDate desc limit $offset, $limit ";
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$nextPage = 0;
		if ($ret && count($ret) > $pageSize) {
			array_pop($ret);
			$nextPage = $page + 1;
		}
		foreach ($ret as $key => $row) {
			$ret[$key]['images'] = json_decode($row['gImage'], 1);
		}

		return [array_values($ret), $count, $nextPage];
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