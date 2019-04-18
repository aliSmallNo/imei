<?php

namespace common\models;

use common\utils\AppUtil;
use Yii;

/**
 * This is the model class for table "im_crm_stock_track".
 *
 * @property integer $tId
 * @property integer $tCId
 * @property integer $tAddressId
 * @property string $tNote
 * @property string $tImage
 * @property integer $tStatus
 * @property integer $tAction
 * @property string $tDate
 * @property string $tAddedDate
 * @property integer $tAddedBy
 * @property integer $tDeletedFlag
 * @property integer $tDeletedBy
 * @property string $tDeletedOn
 */
class CRMStockTrack extends \yii\db\ActiveRecord
{

	const ACTION_USER = 100;
	const ACTION_SYS = 104;
	static $ActionDict = [
		self::ACTION_USER => "用户操作",
		self::ACTION_SYS => "系统添加",
	];

	public static function tableName()
	{
		return '{{%crm_stock_track}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['tCId', 'tAddressId', 'tStatus', 'tAction', 'tAddedBy', 'tDeletedFlag', 'tDeletedBy'], 'integer'],
			[['tDate', 'tAddedDate', 'tDeletedOn'], 'safe'],
			[['tNote'], 'string', 'max' => 512],
			[['tImage'], 'string', 'max' => 1024],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'tId' => 'T ID',
			'tCId' => 'T Cid',
			'tAddressId' => 'T Address ID',
			'tNote' => 'T Note',
			'tImage' => 'T Image',
			'tStatus' => 'T Status',
			'tAction' => 'T Action',
			'tDate' => 'T Date',
			'tAddedDate' => 'T Added Date',
			'tAddedBy' => 'T Added By',
			'tDeletedFlag' => 'T Deleted Flag',
			'tDeletedBy' => 'T Deleted By',
			'tDeletedOn' => 'T Deleted On',
		];
	}


	public static function del($id, $adminId = 1)
	{
		$item = self::findOne(["tId" => $id]);
		if (!$item) {
			return false;
		}
		$item->tDeletedBy = $adminId;
		$item->tDeletedFlag = 1;
		$item->tDeletedOn = date("Y-m-d H:i:s");
		$item->save();
		return true;
		/*$conn = AppUtil::db();
		$sql = "update im_crm_stock_client set cStatus=:status,cUpdatedDate=now(),cUpdatedBy=$adminId  WHERE cId=:cid";
		$conn->createCommand($sql)->bindValues([
			":cid" => $cid,
			":status" => $params["status"],
		])->execute();*/
	}

	public static function add($cid, $params, $adminId)
	{
		$item = new self();

		$fieldMap = [
			"note" => "tNote",
			"status" => "tStatus",
			"image" => "tImage",
			"aId" => "tAddressId",
			"action" => "tAction",
		];
		foreach ($params as $key => $val) {
			if (isset($fieldMap[$key])) {
				$field = $fieldMap[$key];
				$item[$field] = $val;
			}
		}
		$item->tCId = $cid;
		$item->tAddedBy = $adminId;
		$item->tAddedDate = date("Y-m-d H:i:s");
		$item->save();

		$conn = AppUtil::db();
		$sql = "update im_crm_stock_client set cStatus=:status,cUpdatedDate=now(),cUpdatedBy=$adminId  WHERE cId=:cid";
		$conn->createCommand($sql)->bindValues([
			":cid" => $cid,
			":status" => $params["status"],
		])->execute();

		return $item->tId;
	}

	public static function tracks($cid)
	{
		$items = [];
		$conn = AppUtil::db();
		$sql = "select c.cName as nickname, c.cPhone as phone, concat(c.cProvince, ' - ',c.cCity) as address,
 				  c.cBDAssign as bd,c.cStatus as status, c.cSource
 				from im_crm_stock_client as c WHERE cId=" . $cid;
		$clientInfo = [
			"nickname" => "",
			"phone" => "",
			"address" => "",
			"src" => "",
			"bd" => 0,
			"status" => 0
		];
		$ret = $conn->createCommand($sql)->queryOne();
		if ($ret) {
			if (isset(CRMClient::$SourceMap[$ret["cSource"]])) {
				$ret["src"] = CRMClient::$SourceMap[$ret["cSource"]];
			}
			$clientInfo = $ret;
		}

		$sql = " SELECT t.*, IFNULL(a.aName,'未知') as bdname,dz.aProvince,dz.aCity,dz.aDistrict,dz.aTown,dz.aLatitude,dz.aLongitude,
 			(CASE WHEN t.tAddedBy>0 AND t.tAddedBy=c.cBDAssign THEN 'dark' WHEN t.tAddedBy=0 THEN 'gray' ELSE 'light' END) as cls
 			FROM im_crm_stock_track as t 
 			JOIN im_crm_stock_client as c on c.cId=t.tCId
 			LEFT JOIN im_admin as a on a.aId=t.tAddedBy  
 			left join im_address as dz on dz.aId=t.tAddressId
 			WHERE t.tCId=:cid AND t.tDeletedFlag=0 order by t.tAddedDate DESC";
		$ret = $conn->createCommand($sql)->bindValues([":cid" => $cid])->queryAll();

		foreach ($ret as $row) {
			$row["addedDate"] = AppUtil::prettyDateTime($row["tAddedDate"]);
			$row["shortname"] = "无";
			if ($row["bdname"] != "未知") {
				$row["shortname"] = mb_substr($row["bdname"], 1);
			}
			$images = json_decode($row["tImage"], 1);
			if ($images) {
				$row["images"] = $images;
			} else {
				$row["images"] = [];
			}
			$items[] = $row;
		}
		return [$items, $clientInfo];
	}

	public static function trackStat($category, $beginDate, $endDate, $id = "", $conn = "")
	{
		$strCriteria = "";
		if ($id) {
			$strCriteria = " AND c.cBDAssign=" . $id;
		}
		$action = self::ACTION_USER;
		/*$sql = "select COUNT(DISTINCT t.tCId) as cnt, t.tAddedBy, a.aName as title
		 from im_crm_stock_client as c 
		 join im_crm_stock_track as t on t.tCId=c.cId AND t.tAddedBy=c.cBDAssign AND t.tDeletedFlag=0
		 join im_admin as a on a.aId=t.tAddedBy
		 WHERE c.cDeletedFlag=0 AND c.cCategory=$category AND a.aId not in (1002)
		 and t.tAddedDate BETWEEN '$beginDate' and '$endDate 23:59' $strCriteria and t.tAction=$action
		 GROUP BY t.tAddedBy,a.aName
		 order by cnt desc";*/

		// 有照片跟进的算跟进记录
		$pic_where=" and CHAR_LENGTH(t.tImage)>5 ";
		$sql = "select COUNT(DISTINCT t.tCId) as cnt, t.tAddedBy, a.aName as title
		 from im_crm_stock_track as t 
		 join im_admin as a on a.aId=t.tAddedBy
		 WHERE t.tDeletedFlag=0 AND a.aId not in (1002)
		 and t.tAddedDate BETWEEN '$beginDate' and '$endDate 23:59' $strCriteria and t.tAction=$action
		 $pic_where
		 GROUP BY t.tAddedBy,a.aName
		 order by cnt desc";
		if (!$conn) {
			$conn = \Yii::$app->db;
		}
		$ret = $conn->createCommand($sql)->queryAll();
		$items = [];
		foreach ($ret as $key => $row) {
			$items[] = [$row["title"] . ' ' . ($key + 1), intval($row["cnt"])];
		}
		return $items;
	}

	public static function trackStatDetail($category, $beginDate, $endDate, $id, $conn = "")
	{
		$strCriteria = "";
		if ($id) {
			$strCriteria = " AND t.tAddedBy=" . $id;
		}
		/*$sql = "select COUNT(DISTINCT t.tCId) as cnt, t.tAddedBy, a.aName as name, DATE_FORMAT(t.tAddedDate,'%m-%d') as title
		 from im_crm_stock_client as c 
		 join im_crm_stock_track as t on t.tCId=c.cId AND t.tAddedBy=c.cBDAssign AND t.tDeletedFlag=0
		 join im_admin as a on a.aId=t.tAddedBy
		 WHERE c.cDeletedFlag=0 AND c.cCategory=$category
		 and t.tAddedDate BETWEEN '$beginDate' and '$endDate 23:59' $strCriteria 
		 GROUP BY t.tAddedBy, title
		 order by title";*/

		$sql = "select 
		 COUNT(DISTINCT t.tCId) as cnt, t.tAddedBy, a.aName as name, DATE_FORMAT(t.tAddedDate,'%m-%d') as title
		 from im_crm_stock_track as t
		 join im_admin as a on a.aId=t.tAddedBy
		 WHERE t.tDeletedFlag=0 
		 and t.tAddedDate BETWEEN '$beginDate' and '$endDate 23:59' $strCriteria 
		 GROUP BY t.tAddedBy, title
		 order by title";
		if (!$conn) {
			$conn = \Yii::$app->db;
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

	public static function visit($aId, $constr = '')
	{
		$leader = [1464358879, 1453807803, 1467788165, 1464592894, 1464561266];//lm dashixiong, zp, holmes, kingbird
		$ads = [1850366, 1921658, 1464358879, 1952936];//hcb  xy  lm,df
		$condition = $constr;
		if ($aId && !in_array($aId, $leader)) {//卢明 zp
			$condition .= " and aId=$aId ";
		}
		$sql = <<<EEE
select count(1) as co,c.cIntro,c.cName,cPhone,a.aName,a.aId,t.tAddedDate
from im_crm_stock_track as t
left join im_crm_stock_client as c on c.cId=t.tCId
left join im_admin as a on a.aId=c.cBDAssign 
where t.tDeletedFlag=0 AND cCategory=110 $condition and aId not in (1467788165)
group by c.cPhone 
order by aName desc
EEE;
		$ret = AppUtil::db()->createCommand($sql)->queryAll();
		$aIds = [];
		$name = [];
		$count = [];
		foreach ($ret as $k => $v) {
			if (!in_array($v['aId'], $aIds)) {
				$aIds[] = $v['aId'];
			}
			$name[$v['aId']] = $v['aName'];
		}
		$items = [];
		foreach ($aIds as $k1 => $v1) {
			$item = [];
			$num = 0;
			foreach ($ret as $k2 => $v2) {
				if ($v1 == $v2['aId']) {
					$item[] = $v2;
					$num += $v2['co'];
				}
			}
			$items[$k1]['info'] = $item;
			$items[$k1]['co'] = $num;
			$items[$k1]['name'] = $name[$v1];
		}

		return [
			'list' => $items,
		];

	}


	public static function trackNum($cid)
	{
		$sql = "select count(*) as co from im_crm_stock_client as c
				left join im_crm_stock_track as t on c.cId=t.tCId
				where cId=$cid and t.tDeletedFlag=0 ";
		$conn = \Yii::$app->db;
		$ret = $conn->createCommand($sql)->queryOne();
		$count = 0;
		if ($ret) {
			$count = $ret["co"];
		}
		return $count;
	}

}
