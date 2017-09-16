<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 24/5/2017
 * Time: 11:15 AM
 */

namespace common\models;

use common\utils\AppUtil;
use common\utils\ImageUtil;
use common\utils\RedisUtil;
use console\utils\QueueUtil;
use yii\db\ActiveRecord;

class User extends ActiveRecord
{
	const SERVICE_UID = 120000;

	static $Scope = [
		100 => 'IT互联网', 102 => '金融', 104 => '文化传媒',
		106 => '服务业', 108 => '教育培训', 110 => '通信电子',
		112 => '房产建筑', 114 => '轻工贸易', 116 => '医疗生物',
		118 => '生产制造', 120 => '能源环保', 122 => '政法公益',
		124 => '农林牧渔', 126 => '其他'
	];
	static $ProfessionDict = [
		100 => ["研发", "设计", "销售", "运营/编辑", "产品", "市场商务", "高管", "运维/安全", "人力HR",
			"行政后勤", "测试", "客服", "项目管理"],
		102 => ["销售/理财", "银行", "财税审计", "交易证券", "高管", "市场商务", "风投/投行", "担保信贷",
			"保险", "人力资源", "行政后勤", "客户服务", "融资租赁", "咨询服务", "拍卖典当"],
		104 => ["设计", "动画", "行政人事", "销售", "品牌", "公关", "策划", "高管", "艺人", "经纪人",
			"演出/会展", "市场商务", "编导制作", "编辑记者", "艺术家", "收藏", "出版发行", "自由撰稿人"],
		106 => ["市场销售", "娱乐/餐饮", "经营管理", "个体/网店", "交通物流", "服务/领班", "美容/造型师",
			"保健", "酒店/旅游", "行政后勤", "人力HR", "机电维修", "安保", "运动健身", "摄影婚庆", "家政保洁", "客服"],
		108 => ["市场销售", "幼教", "艺术体育", "职业技能", "培训讲师", "人民教师", "行政人事", "教务/管理",
			"课外辅导", "科研学者", "留学移民"],
		110 => ["销售", "生产制造", "技工普工", "硬件开发", "工程/维护", "经营管理", "市场商务", "行政人事",
			"工业设计", "采购物控", "增值业务"],
		112 => ["装修施工", "销售", "经纪人", "设计规划", "项目管理", "高管", "市场商务", "行政人事",
			"质检造价", "开发/物业"],
		114 => ["销售", "纺织服装", "贸易进出口", "采购", "仓储/物流", "食品饮料", "建材家居", "商贸百货",
			"包装印刷", "市场商务", "行政人事", "工艺礼品", "产品设计", "产品研发", "珠宝首饰", "质检/认证", "机电仪表", "客服"],
		116 => ["销售", "医生", "护士/护理", "保健按摩", "行政人事", "经营管理", "市场商务", "医学研发",
			"药剂师", "宠物"],
		118 => ["生产管理", "生产运营", "销售与服务", "电子/电器", "汽车", "机械制造", "服装/纺织",
			"技工", "生物/制药", "医疗器械", "化工"],
		120 => ["能源/矿产", "地质勘查", "环境科学", "环保"],
		122 => ["国家机关", "事业单位", "科研机构", "国企", "后勤人事", "公安司/法", "律师/法务",
			"军队武警", "社会团体", "咨询", "顾问", "调研", "数据分析", "翻译"],
		124 => ["兽医", "饲养", "养殖", "销售", "项目管理", "技术员", "加工/质检", "市场商务", "机械设备"],
		126 => ["自由职业", "社会工作者", "学生"]
	];

	static $Birthyear = [
		1965 => 1965, 1966 => 1966, 1967 => 1967, 1968 => 1968,
		1969 => 1969, 1970 => 1970, 1971 => 1971, 1972 => 1972,
		1973 => 1973, 1974 => 1974, 1975 => 1975, 1976 => 1976,
		1977 => 1977, 1978 => 1978, 1979 => 1979, 1980 => 1980,
		1981 => 1981, 1982 => 1982, 1983 => 1983, 1984 => 1984,
		1985 => 1985, 1986 => 1986, 1987 => 1987, 1988 => 1988,
		1989 => 1989, 1990 => 1990, 1991 => 1991, 1992 => 1992,
		1993 => 1993, 1994 => 1994, 1995 => 1995, 1996 => 1996,
		1997 => 1997, 1998 => 1998, 1999 => 1999
	];
	static $AgeFilter = [
		0 => "年龄不限",
		16 => "16岁", 18 => "18岁", 20 => "20岁", 22 => "22岁", 24 => "24岁", 26 => "26岁", 28 => "28岁", 30 => "30岁",
		32 => "32岁", 34 => "34岁", 36 => "36岁", 38 => "38岁", 40 => "40岁", 42 => "42岁", 44 => "44岁", 46 => "46岁",
		48 => "48岁", 50 => "50岁", 52 => "52岁", 54 => "54岁", 56 => "56岁", 58 => "58岁", 60 => "60岁",
	];
	static $Height = [
		140 => '不到140厘米', 145 => '141~145厘米', 150 => '146~150厘米',
		155 => '151~155厘米', 160 => '156~160厘米', 165 => '161~165厘米',
		170 => '166~170厘米', 175 => '171~175厘米', 180 => '176~180厘米',
		185 => '181~185厘米', 190 => '185~190厘米', 195 => '191~195厘米',
		200 => '196~200厘米', 205 => '201厘米以上',
	];
	static $HeightFilter = [
		0 => "身高不限",
		140 => '不到140cm', 145 => '145cm', 150 => '150cm',
		155 => '155cm', 160 => '160cm', 165 => '165cm',
		170 => '170cm', 175 => '175cm', 180 => '180cm',
		185 => '185cm', 190 => '190cm', 195 => '195cm',
		200 => '200cm', 205 => '201cm以上',
	];
	static $Weight = [
		45 => '不到45kg', 50 => '46~50kg', 55 => '51~55kg',
		60 => "56~60kg", 65 => "61~65kg", 70 => "66~70kg",
		75 => "71~75kg", 80 => "76~80kg", 85 => '81~85kg',
		90 => '86~90kg', 95 => '91~95kg', 100 => "96~100kg",
		105 => "101~105kg", 110 => "106~110kg", 115 => "111~115kg",
		120 => "115kg以上",
	];
	static $Income = [
		3 => "3万元以下", 5 => "3万~5万元", 10 => "6万~10万元",
		15 => "11万~15万元", 25 => "16万~25万元", 35 => "26万~35万元",
		45 => "36万~45万元", 55 => "45万~55万元", 60 => "56万~60万元",
		70 => "61万~70万元", 100 => "71万~100万元", 150 => "100万以上"
	];
	static $IncomeFilter = [
		0 => "收入不限",
		3 => "3万元以下", 5 => "5万元以上", 10 => "10万元以上",
		15 => "15万元以上", 25 => "25万元以上", 35 => "35万元以上",
		45 => "45万元以上", 55 => "55万元以上", 60 => "60万元以上",
		70 => "70万元以上", 100 => "100万元以上", 150 => "100万以上"
	];
	static $Education = [
		100 => "小学", 110 => "初中", 120 => "高中",
		130 => "中专", 140 => "大专", 150 => "本科",
		160 => "硕士", 170 => "博士"
	];

	static $Marital = [
		100 => "未婚", 110 => "离异不带孩", 120 => "离异带孩"
	];

	static $EducationFilter = [
		0 => "学历不限",
		100 => "小学及以上", 110 => "初中及以上", 120 => "高中及以上",
		130 => "中专及以上", 140 => "大专及以上", 150 => "本科及以上",
		160 => "硕士及以上", 170 => "博士及以上"
	];

	static $Profession = [
		101 => "研发", 103 => "设计", 105 => "销售",
		107 => "运营/编辑", 109 => "产品", 111 => "市场销售",
		113 => "高管", 115 => "运维/安全", 117 => "人力HR",
		119 => "行政后勤", 121 => "测试客服", 123 => "项目管理"
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
		301 => "白羊座(3.21~4.20)", 303 => "金牛座(4.21~5.20)",
		305 => "双子座(5.22~6.21)", 307 => "巨蟹座(6.22~6.22)",
		309 => "狮子座(7.23~8.22)", 311 => "处女座(8.23~9.22)",
		313 => "天秤座(9.23~10.23)", 315 => "天蝎座(10.24~11.22)",
		317 => "射手座(11.23~12.21)", 319 => "摩羯座(12.22~1.20)",
		321 => "水瓶座(1.21~2.19)", 323 => "双鱼座(2.20~3.20)"
	];

	const STATUS_VISITOR = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_INVALID = 2;
	const STATUS_PENDING = 3;
	const STATUS_PRISON = 7;
	const STATUS_DUMMY = 8;
	const STATUS_DELETE = 9;
	static $Status = [
		self::STATUS_VISITOR => "游客",
		self::STATUS_PENDING => "待审核",
		self::STATUS_ACTIVE => "已通过",
		self::STATUS_INVALID => "不合规",
		self::STATUS_PRISON => "小黑屋",
		self::STATUS_DUMMY => "稻草人",
		self::STATUS_DELETE => "已删除",
	];

	static $StatusVisible = [
		self::STATUS_PENDING,
		self::STATUS_ACTIVE
	];

	const SUB_ST_NORMAL = 1;
	const SUB_ST_STAFF = 2;
	const SUB_ST_FISH = 3;
	static $Substatus = [
		self::SUB_ST_NORMAL => "普通用户",
		self::SUB_ST_STAFF => "员工用户",
		self::SUB_ST_FISH => "鲶鱼用户",
	];

	const ROLE_SINGLE = 10;
	const ROLE_MATCHER = 20;
	static $Role = [
		self::ROLE_SINGLE => "单身",
		self::ROLE_MATCHER => "媒婆",
	];

	const CERT_STATUS_DEFAULT = 0;
	const CERT_STATUS_PENDING = 1;
	const CERT_STATUS_PASS = 2;
	const CERT_STATUS_FAIL = 9;
	static $Certstatus = [
		self::CERT_STATUS_PENDING => "待实名",
		self::CERT_STATUS_PASS => "已实名",
		self::CERT_STATUS_FAIL => "未通过",
	];

	const ALERT_FAVOR = 'favor';
	const ALERT_PRESENT = 'fans';
	const ALERT_CHAT = 'chat';

	protected static $SmsCodeLimitPerDay = 36;
	private static $SMS_SUPER_PASS = 33092716;

	public static function tableName()
	{
		return '{{%user}}';
	}

	public static function add($data, $adminId = 1)
	{
		if (!$data) {
			return 0;
		}
		$openId = isset($data["uOpenId"]) ? $data["uOpenId"] : '';
		if ($openId) {
			$conn = AppUtil::db();
			$sql = 'INSERT INTO im_user(uOpenId,uUniqid,uAddedBy) 
				SELECT :id,:uni,:aid FROM dual 
				WHERE NOT EXISTS(SELECT 1 FROM im_user WHERE uOpenId=:id)';
			$conn->createCommand($sql)->bindValues([
				':id' => $openId,
				':uni' => uniqid(),
				':aid' => $adminId,
			])->execute();
		}
		$entity = self::findOne(["uOpenId" => $openId]);
		$entity->uUpdatedOn = date('Y-m-d H:i:s');
		$entity->uUpdatedBy = $adminId;
		foreach ($data as $key => $val) {
			if (is_array($val) && $val && $key == 'uAlbum') {
				$album = json_decode($entity->uAlbum, 1);
				if ($album) {
					$val = array_merge($album, $val);
				}
			}
			if (is_array($val)) {
				$entity->$key = json_encode($val, JSON_UNESCAPED_UNICODE);
			} else {
				$entity->$key = $val;
			}
		}
		$entity->save();

		return $entity->uId;
	}

	public static function edit($uid, $params, $editBy = 1)
	{
		if (strlen($uid) < 20) {
			$entity = self::findOne(['uId' => $uid]);
		} else {
			$entity = self::findOne(['uOpenId' => $uid]);
		}

		if (!$entity) {
			$entity = new self();
			$entity->uAddedBy = $editBy;
			$entity->uUniqid = uniqid();
		}
		foreach ($params as $key => $val) {
			$entity->$key = $val;
		}
		$entity->uUpdatedBy = $editBy;
		$entity->uUpdatedOn = date('Y-m-d H:i:s');
		$uid = $entity->save();
		return $uid;
	}


	public static function logDate($uid, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = 'update im_user set uLogDate=now() WHERE uId=:id';
		$conn->createCommand($sql)->bindValues([
			':id' => $uid
		])->execute();
	}

	public static function remove($id)
	{
		$entity = self::findOne(['uId' => $id]);
		if (!$entity) {
			return false;
		}
		$openid = $entity['uOpenId'];
		$conn = AppUtil::db();
		$sql = 'delete from im_user WHERE uId=:id ';
		$conn->createCommand($sql)->bindValues([
			':id' => $id
		])->execute();
		$sql = 'delete from im_user_wechat WHERE wOpenId=:openid ';
		$conn->createCommand($sql)->bindValues([
			':openid' => $openid
		])->execute();
		RedisUtil::delCache(RedisUtil::KEY_WX_USER, $openid);
		return true;
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

	public static function reg0($openId, $phone, $role, $gender = '', $location = '')
	{
		$entity = self::findOne(['uOpenId' => $openId]);
		if ($entity) {
			$entity->uPhone = $phone;
			$entity->uRole = $role;
			if ($gender) {
				$entity->uGender = $gender;
			}
			if ($location) {
				$entity->uLocation = json_encode($location, JSON_UNESCAPED_UNICODE);
			}
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
			$entity->uUniqid = uniqid();
			$entity->uName = $wxInfo['nickname'];
			list($thumb, $figure) = ImageUtil::save2Server($wxInfo['headimgurl'], false);
			$entity->uThumb = $thumb;
			$entity->uAvatar = $figure;
			$entity->save();
		}
		return $entity->uId;
	}

	public static function notes($uInfo)
	{
		$notes = [];
		$fields = ['age', 'height_t', 'income_t', 'horos_t', 'education_t'];
		foreach ($fields as $field) {
			if (isset($uInfo[$field]) && $uInfo[$field]) {
				$val = $uInfo[$field];
				$val = str_replace('厘米', 'cm', $val);
				$val = str_replace('万元', 'w', $val);
				$notes[] = $val;
			}
		}
		return $notes;
	}

	public static function fmtRow($row)
	{
		$keys = array_keys($row);
		$item = [];
		foreach ($keys as $key) {
			$newKey = strtolower(substr($key, 1));
			$val = $row[$key];

			if ($newKey == 'location' || $newKey == 'homeland') {
				$item[$newKey] = json_decode($val, 1);
				$item[$newKey . '_t'] = $item[$newKey] ? implode(' ', array_column($item[$newKey], 'text')) : '';
				continue;
			} elseif ($newKey == 'profession') {
				$item[$newKey] = $val;
				$item[$newKey . '_t'] = '';

				if (isset(self::$ProfessionDict[$row['uScope']])) {
					$professions = self::$ProfessionDict[$row['uScope']];
					$item[$newKey . '_t'] = isset($professions[$val]) ? $professions[$val] : '';
				}
				continue;
			}
			if ($newKey == "note") {
				if ($row["uNote"] == "dummy") {
					$item["note_t"] = "测试数据";
				} else {
					$item["note_t"] = "";
				}
			}

			if ($newKey == "filter") {
				list($dump, $item["filter_t"]) = User::Filter($row["uFilter"]);
			}
			if ($newKey == 'birthyear') {
				$item['age'] = intval($val) ? (date('Y') - intval($val)) . '岁' : '';
			}
			$item[$newKey] = $val;
			$newKey = ucfirst($newKey);
			if (isset(self::$$newKey) && is_array(self::$$newKey)) {
				$item[strtolower($newKey) . '_t'] = isset(self::$$newKey[$val]) ? self::$$newKey[$val] : '';
				$item[strtolower($newKey)] = intval($item[strtolower($newKey)]);
			}
		}
		if (!$item['thumb']) {
			$item['thumb'] = $item['avatar'];
		}
		if ($item['horos_t'] && mb_strlen($item['horos_t']) > 3) {
			$item['horos_t'] = mb_substr($item['horos_t'], 0, 3);
		}
		$item['vip'] = intval($item['vip']);
		$item['album'] = json_decode($item['album'], 1);
		if (!$item['album']) {
			$item['album'] = [];
		}
		$item['album_cnt'] = 0;
		if ($item['album'] && is_array($item['album'])) {
			$item['album_cnt'] = count($item['album']);
		}
		$item['cert'] = (isset($item['certstatus']) && $item['certstatus'] == self::CERT_STATUS_PASS ? 1 : 0);
		$item['gender_ico'] = $item['gender'] == self::GENDER_FEMALE ? 'female' : 'male';
		$item['encryptId'] = AppUtil::encrypt($item['id']);
		$fields = ['approvedby', 'approvedon', 'addedby', 'updatedby', 'rawdata'];
		foreach ($fields as $field) {
			unset($item[$field]);
		}

		$item["percent"] = self::percentage($item);
		$item["pending"] = $item['status'] == User::STATUS_PENDING ? 1 : 0;
		return $item;
	}

	protected static function percentage($info)
	{
		$fields = ["role", "name", "phone", "avatar", "location", 'homeland', "scope", "gender", "birthyear", "horos",
			'marital', "height", "weight", "income", "education", "estate", "car", "smoke", "alcohol", "belief", "fitness",
			"diet", "rest", "pet", "interest", "intro"];
		if ($info['role'] == self::ROLE_MATCHER) {
			$fields = ["role", "name", "phone", "avatar", "location", "scope", "intro"];
		}

		$fill = [];
		$percent = 0;
		foreach ($fields as $field) {
			if (isset($info[$field]) && $info[$field]) {
				$percent++;
				$fill[] = $field;
			}
		}
		return ceil($percent * 100.00 / count($fields));
	}

	public static function users($criteria, $params, $page = 1, $pageSize = 20, $orderbyUpdated = false, $inactive = 0)
	{
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$offset = ($page - 1) * $pageSize;
		$orderBy = ' order by uAddedOn desc ';
		if (isset($params[':status']) && in_array($params[':status'],
				[self::STATUS_INVALID, self::SUB_ST_NORMAL, self::STATUS_PENDING])) {
			$orderBy = ' order by uUpdatedOn desc,uAddedOn desc ';
		}
		if ($orderbyUpdated) {
			$orderBy = ' order by uUpdatedOn desc,uAddedOn desc ';
		}

		$inactive1 = $inactive2 = '';
		if ($inactive) {
			$edate = date("Y-m-d H:i:s");
			$sdate = date("Y-m-d H:i:s", time() - 86400 * 7);
			$inactive1 = " left join im_log_action as a on a.aUId=u.uId and a.aCategory in (1000,1002,1004) and a.aDate BETWEEN '$sdate' and '$edate' ";
			$inactive2 = " and a.aUId is null ";
		}

		$conn = AppUtil::db();
		$sql = "SELECT u.*, IFNULL(w.wSubscribe,0) as wSubscribe,w.wWechatId, count(t.tPId) as uco
 				  FROM im_user as u 
				  JOIN im_user_wechat as w on w.wUId=u.uId
				  left JOIN im_trace as t on u.uId=t.tPId
				  $inactive1
				  WHERE uId>0 $strCriteria $inactive2
				  group by uId
				  $orderBy Limit $offset, $pageSize";
		if ($inactive) {
			// echo $sql;exit;
		}
		$ret = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$items = [];
		foreach ($ret as $row) {
			$items[] = self::fmtRow($row);
		}
		$sql = "SELECT count(1) FROM im_user as u
				JOIN im_user_wechat as w on w.wUId=u.uId
				$inactive1
				WHERE uId>0 $strCriteria $inactive2 ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();
		return [$items, $count];
	}

	public static function partCount($criteria, $params, $inactive = 0)
	{
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$sqlPart = '';
		foreach (self::$Status as $k => $st) {
			$sqlPart .= 'SUM(CASE WHEN uStatus=' . $k . '  THEN 1 END) as c' . $k . ',';
		}
		$sqlPart = trim($sqlPart, ',');
		$inactive1 = $inactive2 = '';
		if ($inactive) {
			$edate = date("Y-m-d H:i:s");
			$sdate = date("Y-m-d H:i:s", time() - 86400 * 7);
			$inactive1 = " left join im_log_action as a on a.aUId=u.uId and a.aCategory in (1000,1002,1004) and a.aDate BETWEEN '$sdate' and '$edate' ";
			$inactive2 = " and a.aUId is null ";
		}
		$sql = "select $sqlPart
				from im_user as u
				JOIN im_user_wechat as w on w.wUId=u.uId
				$inactive1
				WHERE uId>0 $strCriteria $inactive2";
		$conn = AppUtil::db();
		unset($params[':status']);
		$res = $conn->createCommand($sql)->bindValues($params)->queryOne();
		$counts = [];
		foreach ($res as $k => $v) {
			$counts[substr($k, 1)] = isset($v) ? $v : 0;
		}
		return $counts;
	}

	public static function stat()
	{
		$conn = AppUtil::db();
		$st = self::STATUS_DELETE;
		$sql = "select 
				COUNT(1) as amt,
				COUNT(CASE WHEN uPhone!='' AND uGender=11 THEN  1 END ) as male,
				COUNT(CASE WHEN uPhone!='' AND uGender=10 THEN  1 END ) as female,
				COUNT(CASE WHEN uPhone!='' AND uScope>0 THEN  1 END ) as reg,
				COUNT(CASE WHEN wSubscribe=1 THEN  1 END ) as follow
				FROM im_user as u
				JOIN im_user_wechat as w on w.wUId=u.uId
				WHERE uStatus<8";
		$result = $conn->createCommand($sql)->queryOne();
		return $result;
	}

	public static function user($condition, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$criteria = $params = [];
		foreach ($condition as $key => $val) {
			$criteria[] = $key . '=:' . $key;
			$params[':' . $key] = $val;
		}
		list($users) = self::users($criteria, $params);
		if ($users && count($users)) {
			$user = $users[0];

			$sql = 'SELECT u.*,n.nNote as comment
 					FROM im_user_net as n 
					JOIN im_user as u ON n.nUId=u.uId
					WHERE n.nRelation=:rel AND n.nDeletedFlag=0 AND n.nSubUId=:id ';
			$ret = $conn->createCommand($sql)->bindValues([
				':rel' => UserNet::REL_BACKER,
				':id' => $user['id']
			])->queryOne();
			if ($ret) {
				$row = self::fmtRow($ret);
				$user['mp_name'] = $row['name'];
				$user['mp_thumb'] = $row['thumb'];
				$user['mp_scope'] = $row['scope_t'];
				$user['mp_encrypt_id'] = $row['encryptId'];
				$user['comment'] = $ret['comment'];
			} else {
				$user['mp_name'] = '';
				$user['mp_thumb'] = ImageUtil::DEFAULT_AVATAR;
				$user['mp_scope'] = '';
				$user['mp_encrypt_id'] = '';
				$user['comment'] = '';
			}
			/*if (!$user['comment']) {
				$user['comment'] = '(无)';
			}*/
			return $user;
		}
		return [];
	}

	public static function shrinkUser($uInfo)
	{
		if (!$uInfo) {
			return [];
		}
//		$uInfo["imgList"] = $uInfo["album"];
		$uInfo["img4"] = [];
		$gallery = User::gallery($uInfo['album']);
		$uInfo["gallery"] = $gallery;
		foreach ($gallery as $k => $val) {
			if ($k >= 4) break;
			$uInfo["img4"][] = $val['thumb'];
		}
		$fields = ['password', 'phone', 'location', 'album', 'province', 'city', 'certimage', 'certdate', 'logdate',
			'rank', 'rankdate', 'ranktmp', 'addedon', 'updatedon', 'subscribe', 'wechatid', 'weight', 'weight_t', 'marital',
			'income', 'homeland', 'pet', 'diet', 'scope', 'role', 'smoke', 'setting', 'mp_encrypt_id', 'horos', 'hint',
			'alcohol', 'belief', 'car', 'education', 'estate', 'fitness', 'height', 'invitedby', 'rest', 'profession', 'openid'];
		foreach ($fields as $field) {
			unset($uInfo[$field]);
		}
		return $uInfo;
	}

	public static function profile($uId, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$uInfo = self::user(['uId' => $uId], $conn);
		if (!$uInfo) {
			return [];
		}
		$uInfo["albumJson"] = json_encode($uInfo["album"]);
		$uInfo["album_str"] = implode(',', $uInfo["album"]);
		$uInfo["gallery3"] = [];
		if ($uInfo["album"]) {
			$uInfo["gallery3"] = array_slice(self::gallery($uInfo["album"]), 0, 3);
		}
		$baseInfo = [];
		$fields = ['marital_t', 'height_t', 'weight_t', 'income_t', 'education_t', 'estate_t'];
		foreach ($fields as $field) {
			if ($uInfo[$field]) {
				$baseInfo[] = $uInfo[$field];
			}
			if (count($baseInfo) >= 6) {
				break;
			}
		}
		$uInfo['baseInfo'] = $baseInfo;
		$brief = [];
		$fields = ['age', 'height_t', 'horos_t', 'scope_t'];
		foreach ($fields as $field) {
			if ($uInfo[$field]) {
				$brief[] = $uInfo[$field];
			}
			if (count($brief) >= 4) {
				break;
			}
		}
		$uInfo['brief'] = implode(' . ', $brief);
		if (!$uInfo['comment'] && $uInfo['mp_name']) {
			$uInfo['comment'] = '（媒婆很懒，什么也没说）';
		}

		$fields = ['phone', 'certdate', 'certimage', 'certnote', 'location', 'homeland'];
		foreach ($fields as $field) {
			unset($uInfo[$field]);
		}
		return $uInfo;
	}

	public static function resume($uId, $conn = '')
	{
		$uInfo = self::profile($uId, $conn);
		if (!$uInfo) {
			return [];
		}
		$items = [
			['content' => '基本资料', 'header' => 1],
			['caption' => '昵称', 'content' => 'name'],
			['caption' => '性别', 'content' => 'gender_t'],
			['caption' => '婚姻状况', 'content' => 'marital_t'],
			['caption' => '籍贯', 'content' => 'homeland_t'],
			['caption' => '所在城市', 'content' => 'location_t'],
			['caption' => '出生年份', 'content' => 'birthyear'],
			['caption' => '身高', 'content' => 'height_t'],
			['caption' => '体重', 'content' => 'weight_t'],
			['caption' => '年薪', 'content' => 'income_t'],
			['caption' => '学历', 'content' => 'education_t'],
			['caption' => '星座', 'content' => 'horos_t'],
			['content' => '个人小档案', 'header' => 1],
			['caption' => '购房情况', 'content' => 'estate_t'],
			['caption' => '购车情况', 'content' => 'car_t'],
			['caption' => '从事行业', 'content' => 'scope_t'],
			['caption' => '从事职业', 'content' => 'profession_t'],
			['caption' => '饮酒情况', 'content' => 'alcohol_t'],
			['caption' => '吸烟情况', 'content' => 'smoke_t'],
			['caption' => '宗教信仰', 'content' => 'belief_t'],
			['caption' => '健身习惯', 'content' => 'fitness_t'],
			['caption' => '饮食习惯', 'content' => 'diet_t'],
			['caption' => '作息习惯', 'content' => 'rest_t'],
			['caption' => '关于宠物', 'content' => 'pet_t'],
			['content' => '内心独白', 'header' => 1],
			['content' => 'intro'],
			['content' => '兴趣爱好', 'header' => 1],
			['content' => 'interest'],
		];
		foreach ($items as $k => $item) {
			$content = $item['content'];
			if (isset($uInfo[$content])) {
				$items[$k]['content'] = $uInfo[$content];
			}
			if ($k > 0 && isset($items[$k - 1]['header']) && $items[$k - 1]['header']) {
				$items[$k]['first'] = 1;
			}
		}
		return [
			'items' => $items,
			'avatar' => $uInfo['avatar'],
			'thumb' => $uInfo['thumb']
		];
	}

	public static function reg($data)
	{
		$fields = [
			"role" => "uRole",
			"name" => "uName",
			"nickname" => "uName",
			"intro" => "uIntro",
			"location" => "uLocation",
			"homeland" => "uHomeland",
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
			"profession" => "uProfession",
			"pet" => "uPet",
			"rest" => "uRest",
			"smoke" => "uSmoke",
			"weight" => "uWeight",
			"workout" => "uFitness",
			"year" => "uBirthYear",
			"horos" => "uHoros",
			"marital" => "uMarital",
			"coord" => "uCoord",
			"filter" => "uFilter",
			"album" => "uAlbum",
			"status" => "uStatus",
		];
		$avatar = isset($data["img"]) ? $data["img"] : '';
		unset($data['img']);
		if ($avatar) {
			list($thumb, $figure) = ImageUtil::save2Server($avatar, true);
			$data["thumb"] = $thumb;
			$data["img"] = $figure;
		}
		//Rain: 如果是在注册页面，则会上传两张相册图片，在编辑页面，则不涉及相册更改
		if (isset($data["album"])) {
			$album = $data["album"];
			$data['album'] = [];
			if ($album && is_array($album) && count($album)) {
				foreach ($album as $item) {
					list($thumb, $figure) = ImageUtil::save2Server($item);
					if ($figure) {
						$data['album'][] = $figure;
					}
				}
			}
		}
		$userData = [];
		foreach ($fields as $k => $field) {
			if (isset($data[$k])) {
				$userData[$field] = $data[$k];
			}
		}
		$uid = self::add($userData);

		//Rain: 添加媒婆关系
		$conn = AppUtil::db();

		$sql = 'select * from im_user WHERE uId=:id ';
		$uInfo = $conn->createCommand($sql)->bindValues([
			':id' => $uid,
		])->queryOne();
		$uInfo = self::fmtRow($uInfo);
		//$preStatus = $uInfo['status'];
		if ($uInfo['percent'] < 45) {
			$newStatus = self::STATUS_VISITOR;
		} else {
			$newStatus = self::STATUS_PENDING;
		}
		if ($newStatus > -1) {
			$sql = 'update im_user set uStatus=:st WHERE uId=:id';
			$conn->createCommand($sql)->bindValues([
				':id' => $uid,
				':st' => $newStatus
			])->execute();
		}

		$sql = 'INSERT INTO im_user_net(nUId,nSubUId,nRelation,nAddedOn,nUpdatedOn)
			 SELECT n.nUId,u.uId,:backer,u.uUpdatedOn ,u.uUpdatedOn 
			 from im_user_net as n 
			 join im_user as u on u.uId=n.nSubUId and u.uRole=:single 
			 WHERE nRelation=:rel and n.nDeletedFlag=0 AND u.uId=:uid
			 and not exists(select 1 from im_user_net as t where t.nSubUId=u.uId and t.nRelation=:backer and t.nDeletedFlag=0)';
		$cmd = $conn->createCommand($sql);
		$cmd->bindValues([
			':backer' => UserNet::REL_BACKER,
			':rel' => UserNet::REL_INVITE,
			':uid' => $uid,
			':single' => self::ROLE_SINGLE
		])->execute();
		$cmd->bindValues([
			':backer' => UserNet::REL_BACKER,
			':rel' => UserNet::REL_QR_SUBSCRIBE,
			':uid' => $uid,
			':single' => self::ROLE_SINGLE
		])->execute();
		$ret = UserTrans::addReward($uid, UserTrans::CAT_NEW, $conn);

		QueueUtil::loadJob('regeo', ['id' => $uid]);

		return $uid;
	}

	public static function setAvatar($uid, $thumb = '', $figure = '', $adminId = 1)
	{
		$Info = self::findOne(["uId" => $uid]);
		if (!$Info) {
			return [];
		}
		$uId = $Info->uId;
		$openId = $Info->uOpenId;
		$note = ['before' => [$Info->uThumb, $Info->uAvatar]];
		if ($thumb) {
			$Info->uThumb = $thumb;
		}
		if ($figure) {
			$Info->uAvatar = $figure;
		}
		$note['after'] = [$thumb, $figure];
		LogAction::add($uId, $openId, LogAction::ACTION_AVATAR,
			json_encode($note, JSON_UNESCAPED_UNICODE));
		$Info->uUpdatedOn = date('Y-m-d H:i:s');
		$Info->uUpdatedBy = $adminId;
		$Info->save();
		return true;
	}

	public static function album($mediaIds, $openId, $f = 'add')
	{
		$Info = self::findOne(["uOpenId" => $openId]);
		if (!$Info || !$mediaIds) {
			return [];
		}
		$uId = $Info->uId;
		$album = $Info->uAlbum;
		if ($album) {
			$album = json_decode($album, 1);
		} else {
			$album = [];
		}
		$imageItems = [];
		if ($f == 'add') {
			$mediaIds = json_decode($mediaIds, 1);
			$mediaIds = array_reverse($mediaIds);
			foreach ($mediaIds as $mediaId) {
				list($thumb, $url) = ImageUtil::save2Server($mediaId);
				$imageItems[] = [
					'thumb' => $thumb,
					'figure' => $url
				];
			}
			LogAction::add($uId, $openId, LogAction::ACTION_ALBUM_ADD, json_encode($imageItems, JSON_UNESCAPED_UNICODE));
			$album = array_merge($album, array_column($imageItems, 'figure'));
		} else {
			LogAction::add($uId, $openId, LogAction::ACTION_ALBUM_DEL, $mediaIds);
			foreach ($album as $k => $url) {
				if ($url == $mediaIds) {
					unset($album[$k]);
				}
			}
			$album = array_values($album);
		}
		$Info->uAlbum = json_encode(array_values($album), JSON_UNESCAPED_UNICODE);
		$Info->uUpdatedOn = date('Y-m-d H:i:s');
		$Info->save();
		if ($f == 'add') {
			return $imageItems;
		}
		return $album;

	}

	public static function cert($id, $openId)
	{
		list($thumb, $url) = ImageUtil::save2Server($id, false);
		$Info = self::findOne(["uOpenId" => $openId]);
		if ($url && $Info) {
			$uId = $Info->uId;
			$note = [
				'before' => $Info->uCertImage,
				'after' => $url
			];
			LogAction::add($uId, $openId, LogAction::ACTION_CERT,
				json_encode($note, JSON_UNESCAPED_UNICODE));
			return self::edit($Info->uId, [
				"uCertImage" => $url,
				"uCertStatus" => User::CERT_STATUS_PENDING,
				"uCertDate" => date("Y-m-d H:i:s")
			]);
		}
		return 0;
	}

	public static function toCertVerify($id, $flag)
	{
		$Info = self::findOne(["uId" => $id]);
		if ($flag && $Info) {
//			WechatUtil::regNotice($id, "cert" . $flag);
			WechatUtil::templateMsg($flag == "pass" ? WechatUtil::NOTICE_CERT_GRANT : WechatUtil::NOTICE_CERT_DENY, $id);
			return self::edit($id, [
				"uCertStatus" => ($flag == "pass") ? User::CERT_STATUS_PASS : User::CERT_STATUS_FAIL,
				"uCertDate" => date("Y-m-d H:i:s"),
				"uUpdatedOn" => date("Y-m-d H:i:s"),
				"uUpdatedBy" => Admin::getAdminId()
			]);
		}
		return 0;
	}

	public static function getItem($openId)
	{
		$sql = "select n.nUId as mpId,u.*  
				from im_user as u 
				left join im_user_net as n on n.nSubUId=u.uId and  nRelation=120
				where u.uOpenId=:openId";
		$Info = AppUtil::db()->createCommand($sql)->bindValues([
			":openId" => $openId
		])->queryOne();
		$items = self::fmtRow($Info);
		$items["img4"] = [];
		$items["imgList"] = [];
		$items["co"] = 0;
		$uAlbum = $Info["uAlbum"];
		if ($uAlbum) {
			$uAlbum = json_decode($uAlbum, 1);
			$items["imgList"] = $uAlbum;
			$gallery = self::gallery($uAlbum);
			$items["gallery"] = $gallery;
			$items["img4"] = [];
			foreach ($gallery as $k => $val) {
				if ($k >= 4) break;
				$items["img4"][] = $val['thumb'];
			}
			$items["co"] = count($uAlbum);
		}
		$items["hasMp"] = $Info["mpId"];
		return $items;
	}

	public static function gallery($album)
	{
		if (!$album || !is_array($album)) {
			return [];
		}
		$ret = [];
		foreach ($album as $val) {
			$name = pathinfo($val, PATHINFO_FILENAME);
			if (strpos($name, '_n') !== false) {
				$ret[] = [
					'thumb' => str_replace('_n.', '_t.', $val),
					'figure' => $val
				];
			} else {
				$ret[] = [
					'thumb' => $val,
					'figure' => $val
				];
			}
		}
		return $ret;
	}

	public static function criteria($userInfo)
	{
		$myFilter = [];
		$matchInfo = json_decode($userInfo['uFilter'], 1);
		if (!$matchInfo) {
			$matchInfo = [];
		}
		$uLocation = json_decode($userInfo['uLocation'], 1);
		$separator = '-';
		if ($uLocation && !isset($matchInfo['location'])) {
			$text = array_column($uLocation, 'text');
			$matchInfo["location"] = implode($separator, $text);
		}
		if (is_array($matchInfo) && $matchInfo) {
			if (isset($matchInfo["age"]) && $matchInfo["age"] > 0) {
				$ageArr = explode($separator, $matchInfo["age"]);
				if (count($ageArr) == 2) {
					$myFilter["age"] = $ageArr[0] . $separator . $ageArr[1] . '岁';
					$myFilter["ageVal"] = $ageArr[0] . $separator . $ageArr[1];
				}
			} else {
				$myFilter["age"] = self::$AgeFilter[0];
				$myFilter["ageVal"] = 0;
			}

			if (isset($matchInfo["height"]) && $matchInfo["height"] > 0) {
				$heightArr = explode($separator, $matchInfo["height"]);
				if (count($heightArr) == 2) {
					$myFilter["height"] = $heightArr[0] . $separator . $heightArr[1] . 'cm';
					$myFilter["heightVal"] = $heightArr[0] . $separator . $heightArr[1];
				}
			} else {
				$myFilter["height"] = self::$HeightFilter[0];
				$myFilter["heightVal"] = 0;
			}
			if ($uLocation && (!isset($matchInfo["location"]) || !$matchInfo["location"])) {
				$text = array_column($uLocation, 'text');
				$matchInfo["location"] = implode($separator, $text);
			}
			if (isset($matchInfo["location"]) && $matchInfo["location"]) {
				$locationArr = explode($separator, $matchInfo["location"]);
				if (count($locationArr) == 2) {
					$myFilter["location"] = $locationArr[0] . $separator . $locationArr[1];
					$myFilter["locationVal"] = $locationArr[0] . $separator . $locationArr[1];
				}
			} else {
				$myFilter["location"] = "";
				$myFilter["locationVal"] = "";
			}

			if (isset($matchInfo["edu"]) && $matchInfo["edu"] > 0) {
				$myFilter["edu"] = self::$EducationFilter[$matchInfo["edu"]];
				$myFilter["eduVal"] = $matchInfo["edu"];
			} else {
				$myFilter["edu"] = self::$EducationFilter[0];
				$myFilter["eduVal"] = 0;
			}

			if (isset($matchInfo["income"]) && $matchInfo["income"] > 0) {
				if ($matchInfo["income"] == 0) {
					$incomeT = "收入不限";
				} elseif ($matchInfo["income"] == 3) {
					$incomeT = $matchInfo["income"] . "W以下";
				} else {
					$incomeT = $matchInfo["income"] . "W以上";
				}
				$myFilter["income"] = $incomeT;
				$myFilter["incomeVal"] = $matchInfo["income"];

			} else {
				$myFilter["income"] = self::$IncomeFilter[0];
				$myFilter["incomeVal"] = 0;
			}
		}
		$text = '';
		$fields = ['age', 'height', 'edu', 'income', "location"];
		foreach ($fields as $field) {
			if (isset($myFilter[$field]) && $myFilter[$field]
				&& isset($myFilter[$field . 'Val']) && $myFilter[$field . 'Val']) {
				$text .= $myFilter[$field] . ' ';
			}
		}
		$myFilter['text'] = trim($text);
		return $myFilter;
	}

	public static function getFilter($openId, $data, $page = 1, $pageSize = 20)
	{
		$myInfo = self::findOne(["uOpenId" => $openId]);
		if (!$myInfo) {
			return 0;
		}
		$isSingle = ($myInfo->uRole == 10) ? 1 : 0;
		$mId = $myInfo->uId;
		$uFilter = $myInfo->uFilter;
		$myFilter = self::criteria($myInfo);

		$gender = $myInfo->uGender;
		$birthYear = $myInfo->uBirthYear;
		$ageLimit = '';
		if ($gender && $gender == self::GENDER_MALE && $birthYear) {
			$ageLimit = ' and u.uBirthYear >' . ($birthYear - 6);
		}
		if ($gender && $gender == self::GENDER_FEMALE && $birthYear) {
			$ageLimit = ' and u.uBirthYear >' . ($birthYear - 10);
		}

		$gender = ($gender == self::GENDER_FEMALE) ? self::GENDER_MALE : self::GENDER_FEMALE;
		$uRole = User::ROLE_SINGLE;

		$condition = " u.uRole=$uRole AND u.uGender=$gender 
				AND u.uStatus in (" . implode(',', self::$StatusVisible) . ") " . $ageLimit;

		$prov = '江苏';
		$city = '盐城';
		if (isset($myFilter['location'])) {
			list($prov, $city) = explode('-', $myFilter['location']);
		}
		$rankField = "(CASE WHEN u.uLocation like '%$prov%' and u.uLocation like '%$city%' then 10
					WHEN u.uLocation like '%$prov%' then 8 else 0 end) as rank";

		// 去掉筛选条件啦~
		/*
		if (!$data) {
			$data = json_decode($uFilter, 1);
		}
		if (isset($data["age"]) && $data["age"] != 0) {
			$age = explode("-", $data["age"]);
			$year = date("Y");
			$ageStart = $year - $age[1];
			$ageEnd = $year - $age[0];
			$condition .= " and u.uBirthYear  between $ageStart and $ageEnd ";
		}

		if (isset($data["height"]) && $data["height"] != 0) {
			$height = explode("-", $data["height"]);
			$startheight = (is_array($height) && count($height) == 2) ? $height[0] : 0;
			$Endheight = (is_array($height) && count($height) == 2) ? $height[1] : 0;
			$condition .= " and u.uHeight between $startheight and $Endheight ";
		}

		if (isset($data["edu"]) && $data["edu"] > 0) {
			$edu = $data['edu'];
			$condition .= " and u.uEducation > $edu ";
		}

		if (isset($data["income"]) && $data["income"] > 0) {
			$income = $data['income'];
			$condition .= " and u.uIncome > $income ";
		}*/

		$limit = ($page - 1) * $pageSize . "," . $pageSize;

		$relation_mp = UserNet::REL_BACKER;
		$relation_favor = UserNet::REL_FAVOR;
		$pinCat = Pin::CAT_NOW;
		$conn = AppUtil::db();
		$sql = "SELECT * from im_pin as p WHERE p.pCategory=$pinCat AND pPId=" . $mId;
		$ret = $conn->createCommand($sql)->queryOne();
		$myLat = $myLng = '0';
		$distField = ' 9999 as dist';
		if ($ret) {
			$myLat = $ret['pLat'];
			$myLng = $ret['pLng'];
			$distField = 'ROUND(IFNULL(ST_Distance(POINT(' . $myLat . ', ' . $myLng . '), p.pPoint) * 111.195,9999),1) as dist';
		}

		$sql = "SELECT u.*, $distField , $rankField
				FROM im_user as u 
				JOIN im_user_wechat as w on u.uId=w.wUId AND w.wSubscribe=1
				LEFT JOIN im_pin as p on p.pPId=u.uId AND p.pCategory=$pinCat
				WHERE $condition order by dist, rank desc, u.uRank desc limit $limit";
		$ret = $conn->createCommand($sql)->queryAll();
		$rows = [];
		$IDs = [0];
		foreach ($ret as $row) {
			$uid = $row['uId'];
			$rows[$uid] = $row;
			$rows[$uid]['mId'] = '';
			$rows[$uid]['mpavatar'] = '';
			$rows[$uid]['mpname'] = '';
			$rows[$uid]['comment'] = '';
			$rows[$uid]['hid'] = '';
			if ($row['dist'] > 400) {
				$rows[$uid]['dist'] = '';
			}
			$IDs[] = $uid;
		}

		$sql = "SELECT u.*,n.nSubUId, n.nNote  
				FROM im_user as u 
				JOIN im_user_net as n on u.uId=n.nUId and n.nRelation=$relation_mp and n.nDeletedFlag=0 
				WHERE n.nSubUId in (" . implode(',', $IDs) . ")";
		$mpList = $conn->createCommand($sql)->queryAll();
		foreach ($mpList as $mp) {
			$subUid = $mp['nSubUId'];
			if (isset($rows[$subUid])) {
				$rows[$subUid]['mId'] = $mp['uId'];
				$rows[$subUid]['mpavatar'] = $mp['uThumb'];
				$rows[$subUid]['mpname'] = $mp['uName'];
				$rows[$subUid]['comment'] = $mp['nNote'];
			}
		}

		$sql = "SELECT n.nUId
				FROM im_user_net as n  
				WHERE n.nRelation=$relation_favor AND n.nDeletedFlag=0 AND n.nSubUId=$mId
				AND n.nUId in (" . implode(',', $IDs) . ")";
		$favorList = $conn->createCommand($sql)->queryAll();
		foreach ($favorList as $favor) {
			$uid = $favor['nUId'];
			if (isset($rows[$uid])) {
				$rows[$uid]['hid'] = $uid;
			}
		}

		$result = [];
		foreach ($rows as $row) {
			$data = [];
			//$data["id"] = $v["uOpenId"];
			//$data["ids"] = $v["uId"];
			$data["secretId"] = AppUtil::encrypt($row["uId"]);
			$data["avatar"] = $row["uAvatar"];
			$data["cert"] = (isset($row["uCertStatus"]) && $row["uCertStatus"] == User::CERT_STATUS_PASS ? 1 : 0);
			$data["mavatar"] = $row["mpavatar"];
			$data["mpname"] = $row["mpname"];
			$data["comment"] = $row["comment"];
			$advise = '';
			if (strlen($row["dist"])) {
				if (floatval($row["dist"]) <= 0.1) {
					$data["dist"] = '距<100m';
				} elseif (floatval($row["dist"]) <= .9) {
					$data["dist"] = '距' . intval(floatval($row["dist"]) * 1000.0) . 'm';
				} else {
					$data["dist"] = '距' . $row["dist"] . 'km';
				}
				$advise = $data["dist"] . ', ';
			}
			$mins = AppUtil::diffDate(date('Y-m-d H:i'), $row['uLogDate']);
			if ($mins < 1) {
				$advise .= '当前在线';
			} elseif ($mins < 60) {
				$advise .= intval($mins) . '分钟前活跃';
			} elseif ($mins <= 60 * 8) {
				$advise .= intval($mins / 60.0) . '小时前活跃';
			}
			$data["advise"] = trim($advise, ', ');
			$data["name"] = $row["uName"];
			$data["gender"] = $row["uGender"] == 10 ? "female" : "male";
			$data["age"] = date("Y") - $row["uBirthYear"];
			$data["height"] = isset(User::$Height[$row["uHeight"]]) ? User::$Height[$row["uHeight"]] : "无身高";
			$data["horos"] = isset(User::$Horos[$row["uHoros"]]) ? User::$Horos[$row["uHoros"]] : "无星座";
			if ($data["horos"] && mb_strlen($data["horos"]) > 3) {
				$data["horos"] = mb_substr($data["horos"], 0, 3);
			}
			$data["job"] = isset(User::$Scope[$row["uScope"]]) ? User::$Scope[$row["uScope"]] : "无行业";
			$data["intro"] = $row["uIntro"];
			$location = json_decode($row["uLocation"], 1);
			$location = $location ? implode(' ', array_column($location, 'text')) : '';
			$data["location"] = $location;
			$data["hintclass"] = $row["hid"] ? "icon-loved" : "icon-love";
			$data["favor"] = $row["hid"] ? 'favor' : '';
			$data["singleF"] = $isSingle;
			$result[] = $data;
		}

		if (count($ret) == $pageSize) {
			$nextpage = $page + 1;
		} else {
			$nextpage = 0;
		}
		//Rain: 不想展示太多页了
		if ($nextpage > 12) {
			$nextpage = 0;
		}
		return ["data" => $result, "nextpage" => $nextpage, "condition" => $myFilter, 'page' => $page];
	}

	public static function mymp($openId)
	{
		$relation = UserNet::REL_BACKER;
		$sql = "select u2.uId as id,u2.uName as name,u2.uAvatar as avatar,u2.uIntro as intro
				from im_user as u
				join im_user_net as n on u.uId=n.nSubUId and n.nRelation=$relation
				left join im_user as u2 on u2.uId=n.nUId
				where u.uOpenId=:openId";
		$ret = AppUtil::db()->createCommand($sql)->bindValues([
			":openId" => $openId,
		])->queryOne();

		if ($ret) {
			$ret["secretId"] = AppUtil::encrypt($ret["id"]);
			return $ret;
		}
		return "";
	}

	public static function topSingle($uid, $page, $pageSize)
	{

	}

	public static function topMatcher($uid, $page = 1, $pageSize = 20)
	{
//		$uInfo = self::user(['uId' => $uid]);
		$status = User::STATUS_DELETE;
		$conn = AppUtil::db();
		$offset = ($page - 1) * $pageSize;
		$sql = "select u.*, count(t.nId) as uCnt 
			 from im_user as u 
			 LEFT JOIN (SELECT n.nId,n.nUId FROM im_user_net as n 
			 JOIN im_user as u1 on u1.uId=n.nSubUId AND u1.uRole=:single AND u1.uGender>9 AND u1.uStatus < $status
			 WHERE n.nRelation=:rel AND n.nDeletedFlag=0) as t on t.nUId=u.uId
			 WHERE u.uRole=:role AND u.uStatus<9 GROUP BY u.uId HAVING uCnt>0 ORDER BY uUpdatedOn DESC, uCnt DESC
			 LIMIT $offset," . ($pageSize + 1);
		$ret = $conn->createCommand($sql)->bindValues([
			':rel' => UserNet::REL_BACKER,
			':role' => self::ROLE_MATCHER,
			':single' => self::ROLE_SINGLE
		])->queryAll();
		$nextPage = 0;
		if ($ret && count($ret) > $pageSize) {
			array_pop($ret);
			$nextPage = $page + 1;
		}
		$items = [];
		$fields = ['password', 'phone', 'percent', 'openid', 'addedon', 'updatedon', 'album', 'album_cnt', 'homeland', 'homeland_t',
			'cert', 'certdate', 'certimage', 'certnote', 'certstatus', 'certstatus_t', 'location', 'rankdate', 'ranktmp',
			'setting', 'rank', 'weight', 'weight_t', 'marital', 'marital_t', 'coord', 'diet', 'diet_t', 'pet', 'pet_t',
			'birthyear', 'birthyear_t', 'alcohol', 'alcohol_t', 'rest', 'rest_t', 'fitness', 'fitness_t', 'hint',
			'horos', 'horos_t', 'estate', 'estate_t', 'belief', 'belief_t', 'car', 'car_t', 'height', 'height_t',
			'income', 'income_t', 'smoke', 'smoke_t', 'province', 'note', 'note_t', 'city', 'invitedby', 'status_t', 'status',
			'logdate', 'filter', 'filter_t', 'scope', 'interest', 'email', 'education', 'education_t', 'mpuid', 'profession'];
		foreach ($ret as $row) {
			$item = self::fmtRow($row);
			$item['stat'] = UserNet::getStat($item['id']);
			foreach ($fields as $field) {
				unset($item[$field]);
			}
			$items[] = $item;
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
		if (AppUtil::isDev()) {
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
		if ($code == self::$SMS_SUPER_PASS) {
			return true;
		}
		$smsCode = RedisUtil::getCache(RedisUtil::KEY_SMS_CODE, $phone);
		return ($smsCode && $code == $smsCode);
	}

	public static function Filter($filter)
	{
		if (!$filter) {
			return [
				[
					"age" => [["key" => 0, "name" => "年龄不限"]],
					"height" => [["key" => 0, "name" => "身高不限"]],
					"income" => ["key" => 0, "name" => "收入不限"],
					"edu" => ["key" => 0, "name" => "学历不限"],
				],
				[]
			];
		}
		$filterArr = json_decode($filter, 1);
		$ret = [];
		$titles = [];
		if (isset($filterArr["age"]) && $ageArr = explode("-", $filterArr["age"])) {
			$arr = [];
			foreach ($ageArr as $k => $v) {
				$val = isset(User::$AgeFilter[$v]) ? User::$AgeFilter[$v] : "";
				$ret["age"][] = ["key" => $v, "name" => $val];
				if ($v) {
					$arr[] = $val;
				}
			}
			$titles[] = implode('-', $arr);
		} else {
			$ret["age"][] = ["key" => 0, "name" => "年龄不限"];
		}

		if (isset($filterArr["height"]) && $heightArr = explode("-", $filterArr["height"])) {
			$arr = [];
			foreach ($heightArr as $k => $v) {
				$val = isset(User::$HeightFilter[$v]) ? User::$HeightFilter[$v] : "";
				$ret["height"][] = ["key" => $v, "name" => $val];
				if ($v) {
					$arr[] = $val;
				}
			}
			$titles[] = implode('-', $arr);
		} else {
			$ret["height"][] = ["key" => 0, "name" => "身高不限"];
		}

		if (isset($filterArr["location"]) && $locationArr = explode("-", $filterArr["location"])) {
			$arr = [];
			foreach ($locationArr as $k => $v) {
				$ret["location"][] = ["key" => $k, "name" => $v];
				if ($v) {
					$arr[] = $v;
				}
			}
			$titles[] = implode('-', $arr);
		} else {
			$ret["location"][] = ["key" => 0, "name" => ""];
		}

		if (isset($filterArr["income"]) && isset(User::$IncomeFilter[$filterArr["income"]])) {
			$ret["income"] = ["key" => $filterArr["income"], "name" => User::$IncomeFilter[$filterArr["income"]]];
			if ($filterArr["income"]) {
				$titles[] = $ret["income"]['name'];
			}
		} else {
			$ret["income"] = ["key" => 0, "name" => "收入不限"];
		}

		if (isset($filterArr["edu"]) && isset(User::$EducationFilter[$filterArr["edu"]])) {
			$ret["edu"] = ["key" => $filterArr["edu"], "name" => User::$EducationFilter[$filterArr["edu"]]];
			if ($filterArr["edu"]) {
				$titles[] = $ret["edu"]['name'];
			}
		} else {
			$ret["edu"] = ["key" => 0, "name" => "学历不限"];
		}

		return [$ret, $titles];
	}

	public static function searchNet($kw)
	{
		if (!$kw) {
			return [];
		}
		$sql = "select uId as id,uAvatar as avatar,uName as uname,uPhone as phone 
			FROM im_user where uName like '%$kw%' ";
		$res = AppUtil::db()->createCommand($sql)->queryAll();
		return $res;
	}

	public static function trendStat($step, $beginDate, $endDate)
	{
		$conn = AppUtil::db();
		$trends['titles'] = date('n.j', strtotime($endDate));
		$trends['dates'] = date('n.j', strtotime($endDate));
		if ($step == 'week') {
			$trends['dates'] = date('n.j', strtotime($beginDate)) . '~' . date('n.j', strtotime($endDate));
		} elseif ($step == 'month') {
			$trends['dates'] = date('n月', strtotime($endDate));
		}
		$trends["focus"] = 0;
		$trends["newvisitor"] = 0;
		$trends["newmember"] = 0;
		$trends["reg"] = 0;
		$trends["focusRate"] = 0;
		$trends["todayblur"] = 0;
		$trends["male"] = 0;
		$trends["female"] = 0;
		$trends["mps"] = 0;
		$trends["amt"] = 0;
		$trends["visitor"] = 0;
		$trends["member"] = 0;
		$trends["active"] = 0;
		$trends["activemale"] = 0;
		$trends["activefemale"] = 0;
		$trends["activemp"] = 0;
		$trends["activeRate"] = 0;
		$trends["favor"] = 0;
		$trends["getwxno"] = 0;
		$trends["pass"] = 0;
		$trends["chat"] = 0;
		$trends["trans"] = 0;
		$trends["recharge"] = 0;

		$sql = "SELECT 
				count(*) as reg,
				SUM(IFNULL(w.wSubscribe,0)) as focus,
				SUM(CASE WHEN uStatus=0 THEN 1 END) as nvisitor,
				SUM(CASE WHEN uStatus in (1,2,3) THEN 1 END) as newmember,
				SUM(CASE WHEN w.wAddedOn BETWEEN :beginDT AND :endDT AND wSubscribe =0 THEN 1 END ) as todayblur,
				SUM(CASE WHEN u.uRole=10 AND u.uGender=11 THEN  1 END ) as male,
				SUM(CASE WHEN u.uRole=10 AND u.uGender=10 THEN  1 END ) as female,
				SUM(CASE WHEN u.uRole=20 THEN  1 END) as mps
				FROM im_user as u 
				JOIN im_user_wechat as w on w.wUId=u.uId
				where u.uStatus<8 and u.uAddedOn BETWEEN :beginDT and :endDT ";
		$res = $conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryOne();
		if ($res) {
			$trends['focus'] = intval($res["focus"]); // 新增关注
			$trends['nvisitor'] = intval($res["nvisitor"]); // 新增游客
			$trends['newmember'] = intval($res["newmember"]); // 新增会员
			$trends['reg'] = intval($res["reg"]);     // 新增注册
			$trends['focusRate'] = ($res["reg"] > 0) ? intval(round($res["focus"] / $res["reg"], 2) * 100) : 0;   // 转化率
			$trends['todayblur'] = intval($res["todayblur"]);   //  新增取消关注
			$trends['male'] = intval($res["male"]);   //  新增男
			$trends['female'] = intval($res["female"]);// 新增女
			$trends['mps'] = intval($res["mps"]);     //  新增媒婆
		}

		$sql = "SELECT 
				COUNT(1) as amt,
				COUNT(CASE WHEN u.uStatus=0 THEN 1 END) as visitor,
				COUNT(CASE WHEN uRole>9 AND uStatus in (1,2,3) THEN 1 END) as member,
				SUM(IFNULL(w.wSubscribe,0)) as follows,
				SUM(CASE WHEN u.uRole=20 AND uStatus in (1,2,3) THEN 1 END) as meipos,
				SUM(CASE WHEN u.uRole=10 AND u.uGender=10 AND uStatus in (1,2,3) THEN 1 END) as girls,
				SUM(CASE WHEN u.uRole=10 AND u.uGender=11 AND uStatus in (1,2,3) THEN 1 END) as boys
				FROM im_user as u
				JOIN im_user_wechat as w on w.wUId=u.uId
				WHERE uStatus<8 AND uAddedOn < :endDT ";
		$res2 = $conn->createCommand($sql)->bindValues([
			':endDT' => $endDate,
		])->queryOne();
		if ($res2) {
			$trends['amt'] = intval($res2["amt"]); //累计用户
			$trends['member'] = intval($res2["member"]); //累计会员
			$trends['follows'] = intval($res2["follows"]); //累计关注用户
			$trends['meipos'] = intval($res2["meipos"]);
			$trends['girls'] = intval($res2["girls"]);
			$trends['boys'] = intval($res2["boys"]);
			$trends['visitor'] = intval($res2["visitor"]);
//			$trends['visitor'] = $trends['amt'] - $trends['member'];
		}

//		$sql = "select
//				COUNT(DISTINCT a.aUId) as active,
//				COUNT(DISTINCT a.aUId and u.uGender=11) as activemale,
//				COUNT(DISTINCT a.aUId and u.uGender=10) as activefemale,
//				COUNT(DISTINCT a.aUId and u.uRole=20) as activemp
//				from im_user as u
//				join im_log_action as a on u.uId=a.aUId
//				where  uStatus<8 AND a.aDate BETWEEN :beginDT and :endDT
//				AND a.aCategory in (1000,1002,1004) ";

		$sql = "select 
				COUNT(1) as active, 
				SUM(case when uRole=10 AND uGender=11 then 1 end ) as activemale, 
				SUM(case when uRole=10 AND uGender=10 then 1 end ) as activefemale, 
				SUM(case when uRole=20 then 1 end ) as activemp
				FROM (SELECT DISTINCT uId,uName,uGender,uRole
					FROM im_user as u 
					JOIN im_log_action as a on u.uId=a.aUId 
					WHERE uStatus<8 AND a.aCategory in (1000,1002,1004)
						AND a.aDate BETWEEN :beginDT AND :endDT 
					GROUP BY aUId) as temp";
		$res3 = $conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryOne();
		if ($res3) {
			$trends['active'] = intval($res3["active"]); //活跃人数
			$trends['activemale'] = intval($res3["activemale"]); //活跃男
			$trends['activefemale'] = intval($res3["activefemale"]); //活跃女
			$trends['activemp'] = intval($res3["activemp"]); //活跃媒婆
			$trends['activeRate'] = ($res2["member"] > 0) ? intval(round($res3["active"] / $res2["member"], 2) * 100) : 0; // 活跃度
		}

		$sql = "select 
				SUM(CASE WHEN  nRelation=150 THEN  1 END ) as favor,
				SUM(CASE WHEN  nRelation=140 THEN  1 END ) as getwxno,
				SUM(CASE WHEN  nRelation=140 AND nStatus=2 THEN  1 END) as pass,
				SUM(CASE WHEN  nRelation=180 THEN  1 END) as gift
				FROM im_user_net
				WHERE nAddedOn BETWEEN :beginDT and :endDT AND nDeletedFlag=0 ";
		$res4 = $conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryOne();
		if ($res4) {
			$trends['favor'] = intval($res4["favor"]); // 新增心动
			$trends['getwxno'] = intval($res4["getwxno"]); // 新增牵线
			$trends['pass'] = intval($res4["pass"]); // 新增牵线成功
			$trends['gift'] = intval($res4["gift"]); // 赠送礼物/媒桂花
		}

		$sql = "select SUM(tAmt/10.0) as amt
 				from im_user_trans 
 				WHERE tCategory=100 and tUnit='flower' 
 					and tAddedOn BETWEEN :beginDT and :endDT ";
		$ret = $conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryScalar();
		if ($ret) {
			$trends['recharge'] = intval($ret); // 新增心动
		}

		$sql = "SELECT SUM(pTransAmt/100) as trans
				FROM im_pay 
				where pStatus=100 and pTransDate BETWEEN :beginDT AND :endDT  ";
		$res5 = $conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryScalar();
		$trends['trans'] = intval($res5);

		$sql = "SELECT count(1) as chat  
				FROM im_chat_group
				WHERE gFirstCId>0 AND gAddedOn BETWEEN :beginDT AND :endDT ";
		$res6 = $conn->createCommand($sql)->bindValues([
			':beginDT' => $beginDate,
			':endDT' => $endDate,
		])->queryScalar();
		$trends['chat'] = intval($res6);
		return $trends;
	}

	public static function updateRank($ids = [], $goliveFlag = false, $debug = false)
	{
		$conn = AppUtil::db();
		$role = self::ROLE_SINGLE;
		$strCriteria = ' AND uRole=' . $role;
		if ($ids) {
			$strCriteria = ' AND uId in (' . implode(',', $ids) . ')';
		}
		$sql = "select * from im_user where uStatus<9 " . $strCriteria;
		$allUsers = $conn->createCommand($sql)->queryAll();
		$count = 0;
		if ($debug) {
			var_dump(date('Y-m-d H:i:s - ') . $count);
		}
		foreach ($allUsers as $v) {
			$row = self::fmtRow($v);
			self::rankCal($row, $v["uAddedOn"], false, $conn);
			$count++;
			if ($debug && $count % 50 == 0) {
				var_dump(date('Y-m-d H:i:s - ') . $count);
			}
		}
		if ($debug) {
			var_dump(date('Y-m-d H:i:s - ') . $count);
		}
		if ($goliveFlag) {
			$sql = 'Update im_user set uRankDate=now(),uRank=uRankTmp';
			$conn->createCommand($sql)->execute();
		}
	}

	public static function rankCal($row, $addedOn, $updRankFlag = false, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$date = [date('Y-m-d', time() - 86400), date('Y-m-d 23:59')];
		// 主动行为系数（B) B=B1*10+B2+B3+B4 牵手成功B1:次数*10 发出心动B2:次数*1 索要微信B3:次数*1 待定B4:次数*1
		// Rain: 主动行为系数（B) B=B1*10+B2+B3+B4 密聊B1:次数*10 发出心动B2:次数*1 赠送媒桂花B3:次数*2 待定B4:次数*1
		$sql = "SELECT 
				SUM(CASE WHEN nRelation=140 and nStatus=2 THEN 1 ELSE 0 END ) as b1,
				COUNT(CASE WHEN nRelation in (150)  THEN 1 END) as b2 
				from im_user_net where nSubUId=:uid and nAddedOn  BETWEEN :sTime  AND :eTime";
		$bResult = $conn->createCommand($sql)->bindValues([
			":uid" => $row["id"],
			":sTime" => $date[0],
			":eTime" => $date[1],
		])->queryOne();
		$B = $bResult["b2"];
		$sql = "select count(1) as cnt
 				from im_chat_msg WHERE cAddedBy=:uid and cAddedOn  BETWEEN :sTime  AND :eTime ";
		$cnt = $conn->createCommand($sql)->bindValues([
			":uid" => $row["id"],
			":sTime" => $date[0],
			":eTime" => $date[1],
		])->queryScalar();
		$B += intval($cnt * 2.0);


		// "购买系数(V）V=V1*100+V2+V3/5+V4"	充值行为V1:金额*5 每日签到V2:次数*1 账户余额V3:媒瑰花数/5 待定V4,赠送媒桂花B3:次数*2
		$sql = "select
					SUM(CASE WHEN tCategory in (127,128) and tAddedOn  BETWEEN :sTime  AND :eTime THEN 5 END ) as present,
					SUM(CASE WHEN tCategory=100 and tAddedOn  BETWEEN :sTime  AND :eTime THEN 100 END ) as recharge,
					SUM(CASE WHEN tCategory=105 and tAddedOn  BETWEEN :sTime  AND :eTime THEN 1 END ) as sign,
					SUM(CASE WHEN tCategory=100 or tCategory=130  THEN tAmt 
							 WHEN tCategory=105 AND  tUnit='flower' THEN tAmt  
							 WHEN tCategory=120 or tCategory=125 then -tAmt END ) as v3
					from im_user_trans 
					where tUId=:uid ";
		$vResult = $conn->createCommand($sql)->bindValues([
			":uid" => $row["id"],
			":sTime" => $date[0],
			":eTime" => $date[1],
		])->queryOne();
		$V = $vResult["present"] + $vResult["recharge"] * 5 + $vResult["sign"] + $vResult["v3"] / 5;

		// "新鲜度（Activity）A=A1*0.003+365+A2*365+A3*36.5"	注册时间差A1:天数*.003 昨日是否访问A2 过去7天是否访问A3
		$a1 = ceil((time() - strtotime($addedOn)) / 86400);
		$sCat = LogAction::ACTION_SINGLE;
		$yDate = AppUtil::getEndStartTime(time(), 'yesterday', true);
		$wDate = AppUtil::getEndStartTime(time(), 'curweek', true);
		$sql = "SELECT 
					SUM(case when aUId=:uid and aCategory=:cat and aDate BETWEEN :sTime  AND :eTime then 1 end ) as a2,
					SUM(case when aUId=:uid and aCategory=:cat and aDate BETWEEN :wSTime  AND :wETime then 1 end ) as a3
					from im_log_action ";
		$aResult = $conn->createCommand($sql)->bindValues([
			":uid" => $row["id"],
			":cat" => $sCat,
			":sTime" => $yDate[0],
			":eTime" => $yDate[1],
			":wSTime" => $wDate[0],
			":wETime" => $wDate[1],
		])->queryOne();
		$A = $a1 * 0.003 + ($aResult["a2"] > 0 ? 1 : 0) * 365 + ($aResult["a3"] > 0 ? 1 : 0) * 36.5;

		// "认证系数（Qualification）Q=1+Q1+Q2+Q3+Q4"	照片为人像Q1 身份证认证Q2 回访确认单身Q3 回访确认Q4 资料完整度Q5
		// 人工审核
		$Q = 1;

		// "身份表述（Identity）I=1-I1-I2-I3-I4"
		// 取消关注I1 (关注时值为0，未关注为缺省值0.6)
		// 有无媒婆I2 (有媒婆时值为0，无媒婆0.1)
		// 待定I3
		// 测试用户I4 (为测试人员值为0.9) 按以下规则(By zhoup)
		// 工作人员I5 (为工作人员值为0.9) 按以下规则 (By zhoup)
		$I5 = 0;
		switch ($row["status"]) {
			case self::STATUS_PRISON:
				$I4 = 1;
				break;
			case self::STATUS_DUMMY:
			case self::STATUS_VISITOR:
				$I4 = 0;
				break;
			case self::STATUS_PENDING:
				$I4 = 2;
				break;
			case self::STATUS_ACTIVE:
				$I4 = 4;
				break;
			default:
				$I4 = 1;
		}
		switch ($row["substatus"]) {
			case self::SUB_ST_STAFF:
				$I4 *= 0.1;
				break;
			case self::SUB_ST_FISH:
				$I4 *= 1.3;
				break;
			default:
				break;
		}
		$point = 0;
		for ($k = 1; $k < 9; $k++) {
			if ($row["logdate"] > date('Y-m-d H:i', time() - $k * 86400)) {
				$point = 2 * (10 - $k);
				break;
			}
		}
		$I4 += $point;

		$relBacker = UserNet::REL_BACKER;
		$sql = "SELECT u.uName, IFNULL(w.wSubscribe,0) as wSubscribe,n.* 
					FROM im_user as u 
					JOIN im_user_wechat as w on u.uId=w.wUId
					LEFT JOIN im_user_net as n on u.uId=n.nSubUId and n.nRelation=:mp
					WHERE uId=:uid ";
		$iResult = $conn->createCommand($sql)->bindValues([
			":uid" => $row["id"],
			":mp" => $relBacker,
		])->queryOne();
		$W = .01;
		if (isset($iResult["wSubscribe"]) && $iResult["wSubscribe"]) {
			$W = 5;
		}
		$I = ($iResult["nUId"] > 0 ? 0 : 0.1) + $I4 + $I5 + 1;

		// "区分系数（Distinguish) D=-D1/10+D2*10+D3"	 D1:注册年龄 D2:资料完整度指标(资料完成度大于90%取值。小于90%视为缺省) D3:待定
		$D = intval(120 - intval($row["age"])) / 10.0 + ($row["percent"] > 90 ? $row["percent"] / 100 : 0) * 10;

		//计分公式: (B+V+A)*Q*I+D
		$ranktemp = round(($A + $V + $B) * $Q * $I * $W + $D);
		$ranktemp = $ranktemp > 0 ? $ranktemp : 0;
		$updRank = $updRankFlag ? ",uRank=:ranktmp" : "";
		$sql = "update im_user set uRankTmp=:ranktmp" . $updRank . " where uId=:uid";
		$upd = $conn->createCommand($sql);
		$upd->bindValues([
			":uid" => $row["id"],
			":ranktmp" => $ranktemp
		])->execute();

		// echo "uid:" . $row["id"] . ' rank: ' . $ranktemp;
		// AppUtil::logFile("uid:" . $row["id"] . ' rank: ' . $ranktemp, 5);
	}

	protected static function fmtStat($items)
	{
		if ($items && count($items) > 7) {
			$amt = array_sum(array_column($items, 'y'));
			$limit = round($amt * 0.011);
			$others = 0;
			foreach ($items as $k => $item) {
				if ($item['y'] <= $limit) {
					$others += $item['y'];
					unset($items[$k]);
				}
			}
			if ($others) {
				$items = array_values($items);
				$items[] = [
					'name' => '其他',
					'y' => $others,
				];
			}
		}
		foreach ($items as $k => $item) {
			$name = $items[$k]['name'];
			$items[$k]['name'] = str_replace('万元', '', $name);
			$name = $items[$k]['name'];
			$items[$k]['name'] = str_replace('万', '', $name);
			$name = $items[$k]['name'];
			$items[$k]['name'] = str_replace('岁~', '~', $name);
			$name = $items[$k]['name'];
			$items[$k]['name'] = str_replace('厘米', '', $name);
		}
		$items = array_values($items);
		/*usort($items, function ($a, $b) {
			return $a['name'] < $b['name'];
		});*/
		return $items;
	}

	static $AgeScope = [
		[
			"name" => "20岁及以下",
			"range" => [0, 20],
			"y" => 0
		],
		[
			"name" => "21岁~25岁",
			"range" => [21, 25],
			"y" => 0
		],
		[
			"name" => "26岁~30岁",
			"range" => [26, 30],
			"y" => 0
		],
		[
			"name" => "31岁~35岁",
			"range" => [31, 35],
			"y" => 0
		],
		[
			"name" => "36岁~40岁",
			"range" => [36, 40],
			"y" => 0
		],
		[
			"name" => "41岁~45岁",
			"range" => [41, 45],
			"y" => 0
		],
		[
			"name" => "46岁~55岁",
			"range" => [46, 55],
			"y" => 0
		],
		[
			"name" => "56岁及以上",
			"range" => [56, 200],
			"y" => 0
		]
	];

	public static function propStat($beginDate, $endDate, $gender = '')
	{

		$fmtRet = function ($ret, $dict) {
			$itemAll = $itemFemale = $itemMale = [];
			foreach ($ret as $row) {
				$val = $row['val'];
				$cnt = intval($row['co']);
				$gid = intval($row['gender']);
				$name = isset($dict[$val]) ? $dict[$val] : '无数据';
				$item = [
					"name" => $name,
					"y" => $cnt
				];
				if (isset($itemAll[$val])) {
					$itemAll[$val]['y'] += $cnt;
				} else {
					$itemAll[$val] = $item;
				}
				if ($gid == User::GENDER_MALE) {
					$itemMale[] = $item;
				} else {
					$itemFemale[] = $item;
				}
			}
			return [
				'all' => self::fmtStat($itemAll),
				'female' => self::fmtStat($itemFemale),
				'male' => self::fmtStat($itemMale)
			];
		};

		$strCriteria = '';
		if ($gender) {
			$strCriteria = ' AND uGender=' . $gender;
		}
		$role = self::ROLE_SINGLE;
		$sql = "select COUNT(1) as co ,uIncome as val, uGender as gender
				from im_user 
				where uStatus <8 and uRole=:role AND uGender>9 
					and uAddedOn between :sDate and :eDate $strCriteria
				GROUP by uIncome,uGender ";
		$ret = AppUtil::db()->createCommand($sql)->bindValues([
			":role" => $role,
			":sDate" => $beginDate . ' 00:00:00',
			":eDate" => $endDate . ' 23:59:00',
		])->queryAll();
		$incomeData = $fmtRet($ret, self::$Income);

		$sql = "select COUNT(1) as co ,uEducation as val, uGender as gender
				from im_user 
				where uStatus <8 and uRole=:role AND uGender>9 
					and uAddedOn between :sDate and :eDate $strCriteria
				GROUP by uEducation,uGender ";
		$ret = AppUtil::db()->createCommand($sql)->bindValues([
			":role" => $role,
			":sDate" => $beginDate . ' 00:00:00',
			":eDate" => $endDate . ' 23:59:00',
		])->queryAll();
		$eduData = $fmtRet($ret, self::$Education);


		$sql = "select COUNT(1) as co ,uGender as gender 
				from im_user 
				where uStatus < 8 and uRole=:role AND uGender>9
					and uAddedOn between :sDate and :eDate $strCriteria
				GROUP by uGender";
		$gender = AppUtil::db()->createCommand($sql)->bindValues([
			":role" => $role,
			":sDate" => $beginDate . ' 00:00:00',
			":eDate" => $endDate . ' 23:59:00',
		])->queryAll();
		$genderData = [];
		foreach ($gender as $v) {
			$item = [
				"name" => isset(self::$Gender[$v["gender"]]) ? self::$Gender[$v["gender"]] : '无数据',
				"y" => intval($v["co"]),
			];
			$genderData[] = $item;
		}


		$sql = "select COUNT(1) as co ,uHeight as val, uGender as gender
				from im_user 
				where uStatus < 8 and uRole=:role AND uGender>9 
					and uAddedOn between :sDate and :eDate $strCriteria
				GROUP by uHeight,uGender";
		$ret = AppUtil::db()->createCommand($sql)->bindValues([
			":role" => $role,
			":sDate" => $beginDate . ' 00:00:00',
			":eDate" => $endDate . ' 23:59:00',
		])->queryAll();
		$heightData = $fmtRet($ret, self::$Height);

		$sql = 'select count(1) as co,
				(case when age<=20 THEN 0
				 when age BETWEEN 21 AND 25 THEN 1
				 when age BETWEEN 26 AND 30 THEN 2
				 when age BETWEEN 31 AND 35 THEN 3
				 when age BETWEEN 36 AND 40 THEN 4
				 when age BETWEEN 41 AND 45 THEN 6
				 when age BETWEEN 46 AND 55 THEN 7
				 else 8 end) as val,
				 uGender as gender 
				FROM (select (year(now())- uBirthYear) as age, uGender,uId from im_user 
						WHERE uStatus < 8 and uRole=:role AND uGender>9 AND uBirthYear>0
							and uAddedOn between :sDate and :eDate) as t
				 group by val,gender';
		$ret = AppUtil::db()->createCommand($sql)->bindValues([
			":role" => $role,
			":sDate" => $beginDate . ' 00:00:00',
			":eDate" => $endDate . ' 23:59:00',
		])->queryAll();
		$ageNames = ['20及以下', '21~25', '26~30', '31~35', '36~40', '41~45', '46~55', '56及以上'];
		$ageData = $fmtRet($ret, $ageNames);

		$sql = 'select count(DISTINCT aUId) as cnt, DATE_FORMAT(aDate,\'%H\') as hr, uGender as gender
			 from im_log_action as a  
			 JOIN im_user as u on u.uId=a.aUId
			 WHERE u.uGender>9 AND uRole=:role AND aDate BETWEEN :sDate AND :eDate
			 GROUP BY hr,gender';
		$ret = AppUtil::db()->createCommand($sql)->bindValues([
			":role" => $role,
			":sDate" => $beginDate . ' 00:00:00',
			":eDate" => $endDate . ' 23:59:00',
		])->queryAll();
		$times = [];
		for ($k = 0; $k < 24; $k++) {
			$times[$k] = [
				'date' => $k . '点',
				'男生' => 0,
				'女生' => 0,
			];
		}

		foreach ($ret as $row) {
			$hr = intval($row['hr']);
			$gid = $row['gender'] == self::GENDER_MALE ? '男生' : '女生';
			$times[$hr][$gid] = intval($row['cnt']);
		}
		$times = array_values($times);

		return [
			'age' => $ageData,
			'income' => $incomeData,
			'height' => $heightData,
			'gender' => $genderData,
			'edu' => $eduData,
			'times' => $times
		];
	}

	public static function setting($uid, $flag, $setfield)
	{
		// $fields = ["favor" => 1, "fans" => 1, "chat" => 1];
		$uInfo = self::findOne(["uId" => $uid]);
		if (!$uInfo || !$setfield) {
			return 0;
		}
		$set = $uInfo->uSetting;
		if ($set) {
			$set = json_decode($set, 1);
		} else {
			$set = [];
		}
		$set[$setfield] = $flag == "true" ? 1 : 0;
		$uInfo->uSetting = json_encode($set);
		$uInfo->save();
		return true;
	}

	public static function muteAlert($uid, $field, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = 'select uSetting from im_user WHERE uId=:id';
		$setting = $conn->createCommand($sql)->bindValues([
			':id' => $uid
		])->queryScalar();
		$setting = json_decode($setting, 1);
		if (isset($setting[$field]) && $setting[$field] == 0) {
			return true;
		}
		return false;
		/*{"fans":1,"chat":1,"favor":1}*/
	}

	public static function greetUsers($uid, $conn = '')
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$ret = [];
		$sql = 'select * from im_user WHERE uId=:id';
		$uInfo = $conn->createCommand($sql)->bindValues([
			':id' => $uid
		])->queryOne();
		if (!$uInfo) return $ret;
		$gender = $uInfo['uGender'];
		// if ($gender != self::GENDER_FEMALE) return $ret;
		$location = json_decode($uInfo['uLocation'], 1);
		if (!$location) return $ret;
		list($prov, $city) = array_column($location, 'text');
		$birthYear = $uInfo['uBirthYear'];

		if ($gender == self::GENDER_MALE) {
			$gender = self::GENDER_FEMALE;
			list($y0, $y1) = [$birthYear - 3, $birthYear + 8];
		} elseif ($gender == self::GENDER_FEMALE) {
			$gender = self::GENDER_MALE;
			list($y0, $y1) = [$birthYear - 8, $birthYear + 3];
		} else {
			return $ret;
		}

		$params = [
			':prov' => $prov . '%',
			':city' => $city . '%',
			':cat' => Pin::CAT_NOW,
			':y0' => $y0,
			':y1' => $y1,
			':gender' => $gender,
			':st' => self::STATUS_ACTIVE,
			':subst' => self::SUB_ST_NORMAL,
		];

		$sql = 'select uId as id,uName as name,uThumb as thumb,uLogDate,uBirthYear,uHeight,uHoros,
			 (case WHEN p.pProvince like :prov and p.pCity like :city then 10 WHEN p.pProvince like :prov then 8 else 0 end) as rank 
			 from im_user as u
			 JOIN im_user_wechat as w on w.wUId=u.uId AND w.wSubscribe=1
			 JOIN im_pin as p on p.pPId=u.uId and p.pCategory=:cat 
			 WHERE uStatus=:st AND uSubStatus=:subst AND uBirthYear BETWEEN :y0 AND :y1 AND uGender=:gender
			 order by rank desc, uLogDate desc limit 20';
		$active = $conn->createCommand($sql)->bindValues($params)->queryAll();

		$sql = 'select uId as id,uName as name,uThumb as thumb,uLogDate,uBirthYear,uHeight,uHoros,
			 (case WHEN p.pProvince like :prov and p.pCity like :city then 10 WHEN p.pProvince like :prov then 8 else 0 end) as rank 
			 from im_user as u
			 JOIN im_user_wechat as w on w.wUId=u.uId AND w.wSubscribe=1
			 JOIN im_pin as p on p.pPId=u.uId and p.pCategory=:cat 
			 WHERE uStatus=:st AND uSubStatus=:subst AND uBirthYear BETWEEN :y0 AND :y1 AND uGender=:gender
			 order by rank desc, uLogDate limit 20';
		$inactive = $conn->createCommand($sql)->bindValues($params)->queryAll();
		$items = [];
		$flag = true;
		for ($k = 0; $k < 20; $k++) {
			if ($flag) {
				$item = array_shift($active);
			} else {
				$item = array_shift($inactive);
			}
			if ($item) {
				$item['age'] = $item['uBirthYear'] ? date('Y') - $item['uBirthYear'] : '';
				$item['horos'] = isset(self::$Horos[$item['uHoros']]) ? mb_substr(self::$Horos[$item['uHoros']], 0, 3) : '';
				$items[$item['id']] = $item;
				$flag = !$flag;
			}
			if (count($items) == 8) {
				break;
			}
		}
		return array_values($items);
	}

	// 后台聊天稻草人
	public static function dummyForChat()
	{
		$sql = "select uName,uThumb,uId,uGender from im_user where uSubStatus=:sst and uStatus=:st ORDER by uId desc";
		$ret = AppUtil::db()->createCommand($sql)->bindValues([
			":sst" => self::SUB_ST_FISH,
			":st" => self::STATUS_DUMMY,
		])->queryAll();
		$res = [];
		foreach ($ret as $v) {
			if ($v["uGender"] == self::GENDER_MALE) {
				$res[self::GENDER_FEMALE][] = $v;
			} elseif ($v["uGender"] == self::GENDER_FEMALE) {
				$res[self::GENDER_MALE][] = $v;
			}
		}
		return $res;
	}

}