<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 24/5/2017
 * Time: 11:15 AM
 */

namespace common\models;

use common\utils\AppUtil;
use common\utils\RedisUtil;
use yii\db\ActiveRecord;

class User extends ActiveRecord
{
	static $Scope = [
		100 => 'IT互联网', 102 => '金融', 104 => '文化传媒', 106 => '服务业', 108 => '教育培训', 110 => '通信电子', 112 => '房产建筑',
		114 => '轻工贸易', 116 => '医疗生物', 118 => '生产制造', 120 => '能源环保', 122 => '政法公益', 124 => '农林牧渔', 126 => '其他'
	];
	static $Birthyear = [
		1965 => 1965, 1966 => 1966, 1967 => 1967, 1968 => 1968, 1969 => 1969, 1970 => 1970, 1971 => 1971, 1972 => 1972, 1973 => 1973, 1974 => 1974,
		1975 => 1975, 1976 => 1976, 1977 => 1977, 1978 => 1978, 1979 => 1979, 1980 => 1980, 1981 => 1981, 1982 => 1982, 1983 => 1983, 1984 => 1984,
		1985 => 1985, 1986 => 1986, 1987 => 1987, 1988 => 1988, 1989 => 1989, 1990 => 1990, 1991 => 1991, 1992 => 1992, 1993 => 1993, 1994 => 1994,
		1995 => 1995, 1996 => 1996, 1997 => 1997, 1998 => 1998, 1999 => 1999
	];
	static $Height = [
		140 => '不到140厘米', 145 => '141-145厘米', 150 => '146-150厘米', 155 => '151-155厘米', 160 => '156-160厘米', 165 => '161-165厘米',
		170 => '166-170厘米', 175 => '171-175厘米', 180 => '176-180厘米', 185 => '181-185厘米', 190 => '185-190厘米', 195 => '191-195厘米',
		200 => '196-200厘米', 205 => '201厘米以上',
	];
	static $Weight = [
		45 => '不到45kg', 50 => '46~50kg', 55 => '51~55kg', 60 => "56~60kg", 65 => "61~65kg", 70 => "66~70kg", 75 => "71~75kg", 80 => "76~80kg",
		85 => '81~85kg', 90 => '86~90kg', 95 => '91~95kg', 100 => "96~100kg", 105 => "101~105kg", 110 => "106~110kg", 115 => "111~115kg", 120 => "115kg以上",
	];
	static $Income = [
		3 => "3万元以下", 5 => "3万~5万元", 10 => "6万~10万元", 15 => "11万~15万元", 25 => "16万~25万元", 35 => "26万~35万元", 45 => "36万~45万元",
		55 => "45万~55万元", 60 => "56万~60万元", 70 => "61万~70万元", 100 => "71万~100万元", 150 => "100万以上"
	];
	static $Education = [
		100 => "小学", 110 => "初中", 120 => "高中", 130 => "中专", 140 => "大专", 150 => "本科", 160 => "硕士", 170 => "博士"
	];
	static $Profession = [
		101 => "研发", 103 => "设计", 105 => "销售", 107 => "运营/编辑", 109 => "产品", 111 => "市场销售", 113 => "高管", 115 => "运维/安全",
		117 => "人力HR", 119 => "行政后勤", 121 => "测试客服", 123 => "项目管理"
	];
	static $Estate = [
		201 => "有房无贷", 203 => "有房有贷", 205 => "计划购房", 207 => "暂无购房计划"
	];
	static $Car = [
		211 => "有车无贷", 213 => "有车有贷", 215 => "计划购车", 217 => "暂无购车计划"
	];
	static $Smoke = [
		221 => "不吸，反感吸烟", 223 => "不吸，不反感吸烟", 225 => "偶尔吸烟", 227 => "每天都吸烟"
	];
	static $Alcohol = [
		231 => "不饮酒，反感饮酒", 233 => "不饮酒，不反感饮酒", 235 => "偶尔饮酒", 237 => "每天都饮酒"
	];
	static $Belief = [
		241 => "无", 243 => "佛教", 245 => "道教", 247 => "基督教", 249 => "伊斯兰教", 251 => "天主教", 253 => "其他宗教信仰"
	];
	static $Fitness = [
		261 => "每天健身", 263 => "每周两三次", 265 => "没时间健身", 267 => "不喜欢健身"
	];
	static $Diet = [
		271 => "无特殊习惯", 273 => "喜欢吃辣", 275 => "喜欢吃肉", 277 => "喜欢吃素"
	];
	static $Rest = [
		281 => "早睡早起", 283 => "夜猫子", 285 => "偶尔懒散", 287 => "无规律"
	];
	static $Pet = [
		291 => "不养，反感宠物", 293 => "不养，不反感宠物", 295 => "已养宠物", 297 => "无所谓"
	];

	const GENDER_FEMALE = 10;
	const GENDER_MALE = 11;
	static $Gender = [
		self::GENDER_FEMALE => "美女",
		self::GENDER_MALE => "帅哥"
	];
	static $Horos = [
		301 => "白羊座(3.21~4.20)", 303 => "金牛座(4.21~5.20)", 305 => "双子座(5.22~6.21)", 307 => "巨蟹座(6.22~6.22)",
		309 => "狮子座(7.23~8.22)", 311 => "处女座(8.23~9.22)", 313 => "天秤座(9.23~10.23)", 315 => "天蝎座(10.24~11.22)",
		317 => "射手座(11.23~12.21)", 319 => "摩羯座(12.22~1.20)", 321 => "水瓶座(1.21~2.19)", 323 => "双鱼座(2.20~3.20)"
	];

	static $Marital = [
		0 => "未婚", 1 => "已婚"
	];

	const STATUS_PENDING = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETE = 9;
	static $Status = [
		self::STATUS_PENDING => "待审核",
		self::STATUS_ACTIVE => "已通过",
		self::STATUS_DELETE => "已删除",
	];

	const ROLE_SINGLE = 10;
	const ROLE_MATCHER = 20;
	static $Role = [
		self::ROLE_SINGLE => "单身",
		self::ROLE_MATCHER => "媒婆",
	];

	protected static $SmsCodeLimitPerDay = 36;

	public static function tableName()
	{
		return '{{%user}}';
	}

	public static function add($data, $adminId = 1)
	{
		if (!$data) {
			return 0;
		}
		$openId = isset($data["uOpenId"]) ? $data["uOpenId"] : "";
		if ($entity = self::findOne(["uOpenId" => $openId])) {
			$entity->uUpdatedOn = date('Y-m-d H:i:s');
			$entity->uUpdatedBy = $adminId;
		} else {
			$entity = new self();
			$entity->uAddedOn = date('Y-m-d H:i:s');
			$entity->uAddedBy = $adminId;
		}
		foreach ($data as $key => $val) {
			$entity->$key = $val;
		}
		$entity->save();
		return $entity->uId;
	}

	public static function edit($uid, $params, $editBy = 1)
	{
		$entity = self::findOne(['uId' => $uid]);
		if (!$entity) {
			$entity = new self();
			$entity->uAddedBy = $editBy;
		}
		foreach ($params as $key => $val) {
			$entity->$key = $val;
		}
		$entity->uUpdatedBy = $editBy;
		$entity->uUpdatedOn = date('Y-m-d H:i:s');
		$uid = $entity->save();
		return $uid;
	}

	public static function bindPhone($openId, $phone, $role = 0)
	{
		$entity = self::findOne(['uOpenId' => $openId]);
		if ($entity) {
			$entity->uPhone = $phone;
			$entity->uRole = $role;
			$entity->uUpdatedOn = date('Y-m-d H:i:s');
			$entity->save();
			RedisUtil::delCache(RedisUtil::KEY_WX_USER, $openId);
			return true;
		}
		return false;
	}

	public static function addWX($wxInfo, $editBy = 1)
	{
		$openid = $wxInfo['openid'];
		$entity = self::findOne(['uOpenId' => $openid]);
		if (!$entity) {
			$entity = new self();
			$entity->uAddedBy = $editBy;
			$entity->uUpdatedBy = $editBy;
			$entity->uOpenId = $openid;
			$entity->uName = $wxInfo['nickname'];
			$entity->uThumb = AppUtil::getMediaUrl($wxInfo['headimgurl'], true, true);
			$entity->uAvatar = AppUtil::getMediaUrl($wxInfo['headimgurl'], false);
			$entity->save();
		}
		return $entity->uId;
	}

	protected static function fmtRow($row)
	{
		$keys = array_keys($row);
		$item = [];
		foreach ($keys as $key) {
			$newKey = strtolower(substr($key, 1));
			$val = $row[$key];
			if ($newKey == 'location') {
				$item[$newKey] = json_decode($val, 1);
				$item[$newKey . '_t'] = '';
				if ($item[$newKey]) {
					foreach ($item[$newKey] as $loc) {
						$item[$newKey . '_t'] .= $loc['text'] . ' ';
					}
					$item[$newKey . '_t'] = trim($item[$newKey . '_t']);
				}
				continue;
			}
			if ($newKey == 'birthyear') {
				$item['age'] = date('Y') - intval($val);
			}
			$item[$newKey] = $val;
			$newKey = ucfirst($newKey);
			if (isset(self::$$newKey) && is_array(self::$$newKey)) {
				$item[strtolower($newKey) . '_t'] = isset(self::$$newKey[$val]) ? self::$$newKey[$val] : '';
				$item[strtolower($newKey)] = intval($item[strtolower($newKey)]);
			}
		}
		$item['vip'] = intval($item['vip']);
		unset($item['approvedby'], $item['approvedon'], $item['addedby'], $item['updatedby']);
		return $item;
	}

	public static function users($criteria, $params, $page = 1, $pageSize = 20)
	{
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$offset = ($page - 1) * $pageSize;
		$conn = AppUtil::db();
		$sql = "SELECT * FROM im_user WHERE uId>0 $strCriteria 
					ORDER BY uUpdatedOn DESC Limit $offset, $pageSize";
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$items = [];
		foreach ($ret as $row) {
			$items[] = self::fmtRow($row);
		}
		$sql = "SELECT count(1) FROM im_user WHERE uId>0 $strCriteria ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();
		return [$items, $count];
	}

	public static function user($condition)
	{
		$criteria = $params = [];
		foreach ($condition as $key => $val) {
			$criteria[] = $key . '=:' . $key;
			$params[':' . $key] = $val;
		}
		list($users) = self::users($criteria, $params);
		if ($users && count($users)) {
			return $users[0];
		}
		return [];
	}

	public static function hint($uid)
	{
		$ret = '';
		return $ret;
	}

	public static function reg($data)
	{
		$fields = [
			"role" => "uRole",
			"name" => "uName",
			"intro" => "uIntro",
			"location" => "uLocation",
			"scope" => "uScope",
			"img" => "uAvatar",
			"thumb" => "uThumb",
			"openId" => "uOpenId",
			"belief" => "uBelief",
			"car" => "uCar",
			"diet" => "uDiet",
			"drink" => "uAlcohol",
			"edu" => "uEducation",
			"gender" => "uGender",
			"height" => "uHeight",
			"house" => "uEstate",
			"income" => "uIncome",
			"interest" => "uInterest",
			"job" => "uProfession",
			"pet" => "uPet",
			"rest" => "uRest",
			"smoke" => "uSmoke",
			"weight" => "uWeight",
			"workout" => "uFitness",
			"year" => "uBirthYear",
			"sign" => "uHoros",
		];
		$img = isset($data["img"]) ? $data["img"] : '';
		unset($data['img']);
		if ($img) {
			$url = AppUtil::getMediaUrl($img);
			if ($url) {
				$data["img"] = $url;
			}
			$url = AppUtil::getMediaUrl($img, true, true);
			if ($url) {
				$data["thumb"] = $url;
			}
		}
		$addData = [];
		foreach ($fields as $k => $v) {
			if (isset($data[$k])) {
				$addData[$v] = $data[$k];
			}
		}
		$uid = self::add($addData);
		return $uid;
	}

	public static function album($id, $openId)
	{
		$url = "";
		if ($id) {
			$url = AppUtil::getMediaUrl($id);
		}
		$Info = self::findOne(["uOpenId" => $openId]);
		if ($url && $Info) {
			$album = $Info->uAlbum;
			if ($album) {
				$album = json_decode($album, 1);
			} else {
				$album = [];
			}
			$album[] = $url;
			$album = json_encode($album);
			self::edit($Info->uId, ["uAlbum" => $album]);
			return $url;
		}
		return 0;

	}

	public static function getItem($openId)
	{
		$Info = self::find()->where(["uOpenId" => $openId])->asArray()->one();
		$Info["img4"] = [];
		$Info["imgList"] = [];
		$uAlbum = $Info["uAlbum"];
		if ($uAlbum) {
			$uAlbum = json_decode($uAlbum, 1);
			$Info["imgList"] = $uAlbum;
			if (count($uAlbum) <= 4) {
				$Info["img4"] = $uAlbum;
			} else {
				for ($i = 0; $i < 4; $i++) {
					$Info["img4"][] = array_pop($uAlbum);
				}
			}
		}
		return $Info;
	}

	public static function topSingle($uid, $page, $pageSize)
	{

	}

	public static function topMatcher($uid, $page = 1, $pageSize = 10)
	{
//		$uInfo = self::user(['uId' => $uid]);
		$conn = AppUtil::db();
		$offset = ($page - 1) * $pageSize;
		$sql = 'select u.*, count(n.nId) as uCnt 
			 from im_user as u 
			 LEFT JOIN im_user_net as n on u.uId=n.nUId AND n.nRelation=:rel AND n.nDeletedFlag=0
			 WHERE u.uRole=:role GROUP BY n.nUId ORDER BY uCnt DESC
			 limit ' . $offset . ',' . ($pageSize + 1);
		$ret = $conn->createCommand($sql)->bindValues([
			':rel' => UserNet::REL_BACKER,
			':role' => self::ROLE_MATCHER
		])->queryAll();
		$nextPage = 0;
		if ($ret && count($ret) > $pageSize) {
			$nextPage = $page + 1;
			array_pop($ret);
		}
		$items = [];
		foreach ($ret as $row) {
			$items[] = self::fmtRow($row);
		}
		return [$items, $nextPage];
	}

	/**
	 * @param $phone
	 * @return array
	 */
	public static function sendSMSCode($phone)
	{
		if (!AppUtil::checkPhone($phone)) {
			return ['code' => 159, 'msg' => '手机格式不正确'];
		}
		if (AppUtil::scene() == 'dev') {
			return ['code' => 159, 'msg' => '悲催啊~ 只能发布到服务器端才能测试这个功能~'];
		}
		$smsLimit = RedisUtil::getCache(RedisUtil::KEY_SMS_CODE_CNT, date('ymd'), $phone);
		if (!$smsLimit) {
			RedisUtil::setCache(1, RedisUtil::KEY_SMS_CODE_CNT, date('ymd'), $phone);
		} elseif ($smsLimit > self::$SmsCodeLimitPerDay) {
			return ['code' => 159, 'msg' => '每天获取验证码的次数不能超过' . self::$SmsCodeLimitPerDay . '次'];
		} else {
			RedisUtil::setCache($smsLimit + 1, RedisUtil::KEY_SMS_CODE_CNT, date('ymd'), $phone);
		}
		$code = rand(100000, 999999);
		$minutes = 10;

		AppUtil::sendTXSMS($phone, AppUtil::SMS_NORMAL, ["params" => [strval($code), strval($minutes)]]);
		RedisUtil::setCache($code, RedisUtil::KEY_SMS_CODE, $phone);
		return ['code' => 0, 'msg' => '验证码已发送到手机【' . $phone . '】<br>请注意查收手机短信'];
	}

	public static function verifySMSCode($phone, $code)
	{
		$smsCode = RedisUtil::getCache(RedisUtil::KEY_SMS_CODE, $phone);
		return ($smsCode && $code == $smsCode);
	}

}