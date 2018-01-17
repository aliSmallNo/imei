<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 24/5/2017
 * Time: 11:15 AM
 */

namespace common\models;

use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\ImageUtil;
use common\utils\RedisUtil;
use common\utils\WechatUtil;
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
		1964 => 1964, 1965 => 1965, 1966 => 1966, 1967 => 1967,
		1968 => 1968, 1969 => 1969, 1970 => 1970, 1971 => 1971,
		1972 => 1972, 1973 => 1973, 1974 => 1974, 1975 => 1975,
		1976 => 1976, 1977 => 1977, 1978 => 1978, 1979 => 1979,
		1980 => 1980, 1981 => 1981, 1982 => 1982, 1983 => 1983,
		1984 => 1984, 1985 => 1985, 1986 => 1986, 1987 => 1987,
		1988 => 1988, 1989 => 1989, 1990 => 1990, 1991 => 1991,
		1992 => 1992, 1993 => 1993, 1994 => 1994, 1995 => 1995,
		1996 => 1996, 1997 => 1997, 1998 => 1998, 1999 => 1999
	];
	static $AgeFilter = [
		0 => "年龄不限",
		16 => "16岁", 18 => "18岁", 20 => "20岁", 22 => "22岁", 24 => "24岁", 26 => "26岁", 28 => "28岁", 30 => "30岁",
		32 => "32岁", 34 => "34岁", 36 => "36岁", 38 => "38岁", 40 => "40岁", 42 => "42岁", 44 => "44岁", 46 => "46岁",
		48 => "48岁", 50 => "50岁", 52 => "52岁", 54 => "54岁", 56 => "56岁", 58 => "58岁", 60 => "60岁",
	];
//	static $Height = [
//		140 => '不到140厘米', 145 => '141~145厘米', 150 => '146~150厘米',
//		155 => '151~155厘米', 160 => '156~160厘米', 165 => '161~165厘米',
//		170 => '166~170厘米', 175 => '171~175厘米', 180 => '176~180厘米',
//		185 => '181~185厘米', 190 => '185~190厘米', 195 => '191~195厘米',
//		200 => '196~200厘米', 205 => '201厘米以上',
//	];

	static $Height = [
		134 => 134, 135 => 135, 136 => 136, 137 => 137, 138 => 138, 139 => 139,
		140 => 140, 141 => 141, 142 => 142, 143 => 143, 144 => 144, 145 => 135,
		146 => 136, 147 => 137, 148 => 138, 149 => 139, 150 => 150, 151 => 151,
		152 => 152, 153 => 153, 154 => 154, 155 => 155, 156 => 156, 157 => 157,
		158 => 158, 159 => 159, 160 => 160, 161 => 161, 162 => 162, 163 => 163,
		164 => 164, 165 => 165, 166 => 166, 167 => 167, 168 => 168, 169 => 169,
		170 => 170, 171 => 171, 172 => 172, 173 => 173, 174 => 174, 175 => 175,
		176 => 176, 177 => 177, 178 => 178, 179 => 179, 180 => 180, 181 => 181,
		182 => 182, 183 => 183, 184 => 184, 185 => 185, 186 => 186, 187 => 187,
		188 => 188, 189 => 189, 190 => 190, 191 => 191, 192 => 192, 193 => 193,
		194 => 194, 195 => 195, 196 => 196, 197 => 197, 198 => 198, 199 => 199,
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

	const MARITAL_UNMARRIED = 100;
	const MARITAL_DIVORCE_KID = 110;
	const MARITAL_DIVORCE_NO_KID = 120;
	const MARITAL_MARRIED = 130;

	static $Marital = [
		self::MARITAL_UNMARRIED => "未婚（无婚史）",
		self::MARITAL_DIVORCE_KID => "离异不带孩",
		self::MARITAL_DIVORCE_NO_KID => "离异带孩",
		self::MARITAL_MARRIED => "已婚（可帮朋友脱单）",
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
		201 => "有房无贷", 203 => "有房有贷", 205 => "计划购房", 207 => "暂无购房计划",
		209 => "市区有房", 211 => "有门面房", 213 => "老家有房", 215 => "乡下有房",
	];
	static $Worktype = [
		210 => "公务员编制", 220 => "事业编制", 230 => "私营企业", 240 => "个体创业", 250 => "家庭自由"
	];
	static $Parent = [
		210 => "单亲", 220 => "都健在"
	];
	static $Sibling = [
		210 => "独生子女", 220 => "排行老大", 230 => "排行最小", 240 => "排行中间"
	];
	static $Dwelling = [
		210 => "与父母同住", 220 => "租房住", 230 => "住自购房"
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
		305 => "双子座(5.21~6.21)", 307 => "巨蟹座(6.22~7.22)",
		309 => "狮子座(7.23~8.22)", 311 => "处女座(8.23~9.22)",
		313 => "天秤座(9.23~10.23)", 315 => "天蝎座(10.24~11.22)",
		317 => "射手座(11.23~12.21)", 319 => "摩羯座(12.22~1.19)",
		321 => "水瓶座(1.20~2.18)", 323 => "双鱼座(2.19~3.20)"
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

	const STATUS_XCX = 100;

	static $StatusVisible = [
		self::STATUS_ACTIVE
	];

	const SUB_ST_NORMAL = 1;
	const SUB_ST_STAFF = 2;
	const SUB_ST_FISH = 3;
	const SUB_ST_STICK = 4;
	static $Substatus = [
		self::SUB_ST_NORMAL => "普通用户",
		self::SUB_ST_STAFF => "员工用户",
		self::SUB_ST_FISH => "鲶鱼用户",
		self::SUB_ST_STICK => "置顶用户",
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
		self::CERT_STATUS_PENDING => "待审核",
		self::CERT_STATUS_PASS => "实名",
		self::CERT_STATUS_FAIL => "未通过",
	];

	const ALERT_FAVOR = 'favor';
	const ALERT_PRESENT = 'fans';
	const ALERT_CHAT = 'chat';

	const OPENID_PREFIX = 'oYDJew';

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
		$preInfo = $entity->toArray();
		$entity->uUpdatedOn = date('Y-m-d H:i:s');
		$entity->uUpdatedBy = $adminId;
		foreach ($data as $key => $val) {
			if ($key == 'uAlbum' && is_array($val) && $val) {
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
		$afterInfo = $entity->toArray();
		Log::add([
			"oCategory" => Log::CAT_USER_MODIFY,
			"oUId" => $entity->uId,
			"oOpenId" => $openId,
			"oAfter" => $afterInfo,
			"oBefore" => $preInfo,
		]);
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
		RedisUtil::init(RedisUtil::KEY_WX_USER, $openid)->delCache();
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
			RedisUtil::init(RedisUtil::KEY_WX_USER, $openId)->delCache();
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
			RedisUtil::init(RedisUtil::KEY_WX_USER, $openId)->delCache();
			return true;
		}
		return false;
	}

	public static function addWX($wxInfo, $editBy = 1)
	{
		$openid = $wxInfo['openid'];
		$entity = self::findOne(['uOpenId' => $openid]);
		if ($entity) {
			return $entity->uId;
		}
		$entity = new self();
		$entity->uAddedBy = $editBy;
		$entity->uUpdatedBy = $editBy;
		$entity->uOpenId = $openid;
		$entity->uUnionId = isset($wxInfo['unionid']) ? $wxInfo['unionid'] : '';
		$entity->uUniqid = uniqid();
		$entity->uName = $wxInfo['nickname'];
		list($thumb, $figure) = ImageUtil::save2Server($wxInfo['headimgurl'], false);
		$entity->uThumb = $thumb;
		$entity->uAvatar = $figure;
		$entity->save();
		return $entity->uId;
	}

	public static function addWXByUnionId($wxInfo, $editBy = 1)
	{
		$openId = $wxInfo['unionid'];
		$entity = self::findOne(['uUnionId' => $openId]);
		if ($entity) {
			return $entity->uId;
		}
		$entity = new self();
		$entity->uAddedBy = $editBy;
		$entity->uUpdatedBy = $editBy;
		$entity->uOpenId = "";
		$entity->uStatus = User::STATUS_XCX;
		$entity->uUnionId = $openId;
		$entity->uUniqid = uniqid();
		$entity->uName = $wxInfo['nickname'];
		list($thumb, $figure) = ImageUtil::save2Server($wxInfo['headimgurl'], false);
		$entity->uThumb = $thumb;
		$entity->uAvatar = $figure;
		$entity->save();
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
			} else if (in_array($newKey, ["estate"])) {
				$newKey = ucfirst($newKey);
				$item[strtolower($newKey) . '_t'] = [];
				$item[strtolower($newKey) . '_txt'] = "";
				if (isset(self::$$newKey) && is_array(self::$$newKey) && $val) {
					$val = explode(",", $val);
					if ($val && is_array($val)) {
						foreach ($val as $v) {
							$item[strtolower($newKey) . '_t'][$v] = isset(self::$$newKey[$v]) ? self::$$newKey[$v] : '';
							$item[strtolower($newKey) . '_txt'] .= isset(self::$$newKey[$v]) ? "," . self::$$newKey[$v] : '';
						}
						$item[strtolower($newKey) . '_txt'] = trim($item[strtolower($newKey) . '_txt'], ",");
					}
				}
				continue;
			}
			$item["straw"] = (isset($item['openid']) && strpos($item['openid'], 'oYDJew') !== 0 ? 1 : 0);

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
		$item["dummy"] = 0;
		if (strpos($item['openid'], 'oYDJew') !== 0) {
			$item["dummy"] = 1;
		}
		if (!$item['thumb']) {
			$item['thumb'] = $item['avatar'];
		}
		if ($item['horos_t'] && mb_strlen($item['horos_t']) > 3) {
			$item['horos_t'] = mb_substr($item['horos_t'], 0, 3);
		}
		if ($item['height_t'] && strpos($item['height_t'], 'cm') === false) {
			$item['height_t'] .= 'cm';
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
		// ["parent","sibling","dwelling","worktype","employer"，"music","book","movie","highschool","university",]
		$fields = ["role", "name", "phone", "avatar", "location", 'homeland', "scope", "gender", "birthyear", "horos",
			'marital', "height", "weight", "income", "education", "estate", "car", "smoke", "alcohol", "belief", "fitness",
			"diet", "rest", "pet", "interest", "intro",
			"parent", "sibling", "dwelling", "worktype", "employer", "music", "book", "movie", "highschool", "university"];
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


	public static function insertPercent()
	{
		// User::propStat();
		$role = self::ROLE_SINGLE;
		$sql = "select * from im_user 
				where uStatus <8 and uRole=:role AND uGender>9 order by uId desc ";
		$conn = AppUtil::db();
		$res = $conn->createCommand($sql)->bindValues([
			":role" => $role,
		])->queryAll();
		$sql2 = "update im_user set uPercent=:percent where uId=:uid ";
		$modCmd = $conn->createCommand($sql2);
		foreach ($res as $v) {
			$fmt = self::fmtRow($v);
			$uid = $v["uId"];
			$percent = $fmt["percent"];
			$modCmd->bindValues([
				':percent' => $percent,
				':uid' => $uid
			])->execute();
		}
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
			//$inactive2 = " and a.aUId is null ";
			$inactive2 = ($inactive == 1) ? " and a.aUId is null " : " and a.aUId ";
		}

		$conn = AppUtil::db();
		$sql = "SELECT u.*, IFNULL(w.wSubscribe,0) as wSubscribe,w.wWechatId, count(t.tPId) as uco,m.aName as aOpname
 				  FROM im_user as u 
				  JOIN im_user_wechat as w on w.wUId=u.uId
				  LEFT JOIN im_trace as t on u.uId=t.tPId
				  LEFT JOIN im_admin as m on m.aId=u.uUpdatedBy
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
		$sql = "SELECT count(1) 
				FROM im_user as u
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
			// $inactive2 = " and a.aUId is null ";
			$inactive2 = ($inactive == 1) ? " and a.aUId is null " : " and a.aUId ";
		}
		/*$sql = "select $sqlPart
				from im_user as u
				JOIN im_user_wechat as w on w.wUId=u.uId
				$inactive1
				WHERE uId>0 $strCriteria $inactive2";*/
		$sql = "select $sqlPart
				from 
				(
				select u.* from im_user as u
				JOIN im_user_wechat as w on w.wUId=u.uId
				$inactive1
				WHERE uId>0 $strCriteria $inactive2 group by uId
				) as a";
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
				COUNT(CASE WHEN uPhone!='' AND uRole in (10) AND uGender=11 THEN  1 END ) as male,
				COUNT(CASE WHEN uPhone!='' AND uRole in (10) AND uGender=11 AND wSubscribe=1 THEN  1 END ) as male0,
				COUNT(CASE WHEN uPhone!='' AND uRole in (10) AND uGender=10 THEN  1 END ) as female,
				COUNT(CASE WHEN uPhone!='' AND uRole in (10) AND uGender=10 AND wSubscribe=1 THEN  1 END ) as female0,
				COUNT(CASE WHEN uPhone!='' AND uRole in (20) THEN  1 END ) as mp,
				COUNT(CASE WHEN uPhone!='' AND uRole in (20) AND wSubscribe=1 THEN  1 END ) as mp0,
				COUNT(CASE WHEN uPhone!='' AND (uRole=20 or (uRole=10 AND uGender>9)) THEN  1 END ) as reg,
				COUNT(CASE WHEN uPhone!='' AND (uRole=20 or (uRole=10 AND uGender>9)) AND wSubscribe=1 THEN  1 END ) as reg0,
				COUNT(CASE WHEN wSubscribe=1 THEN  1 END ) as follow
				FROM im_user as u
				JOIN im_user_wechat as w on w.wUId=u.uId AND w.wOpenId LIKE :openid
				WHERE uStatus<8";
		$result = $conn->createCommand($sql)->bindValues([
			':openid' => self::OPENID_PREFIX . '%'
		])->queryOne();
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
			$userId = $user['id'];
			$tags = UserTag::tags($userId);
			$user["tags"] = isset($tags[$userId]) ? $tags[$userId] : [];
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
		$tags = UserTag::tags($uId);
		$uInfo["tags"] = isset($tags[$uId]) ? $tags[$uId] : [];
		$uInfo["albumJson"] = json_encode($uInfo["album"]);
		$uInfo["album_str"] = implode(',', $uInfo["album"]);
		$uInfo["gallery4"] = [];
		if ($uInfo["album"]) {
			$uInfo["gallery4"] = array_slice(self::gallery($uInfo["album"]), 0, 4);
		}
		$baseInfo = [];
		$fields = ['marital_t', 'height_t', 'weight_t', 'income_t', 'education_t', 'estate_txt'];
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

	public static function resume($uId, $wx_uid, $conn = '')
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
			['caption' => '购房情况', 'content' => 'estate_txt'],
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
		$index = 100;
		$normal = $vip = [];
		foreach ($items as $k => $item) {
			$content = $item['content'];
			if ($content == "个人小档案") {
				$index = $k;
			}
			if (isset($uInfo[$content]) && $k < $index) {
				$items[$k]['content'] = $uInfo[$content];
				$normal[] = $items[$k];
			} else if (isset($items[$k]["header"]) && $items[$k]["header"] == 1 && $k < $index) {
				$normal[] = $items[$k];
			}
			if (isset($uInfo[$content]) && $k >= $index) {
				$items[$k]['content'] = $uInfo[$content];
				$vip[] = $items[$k];
			} else if (isset($items[$k]["header"]) && $items[$k]["header"] == 1 && $k >= $index) {
				$vip[] = $items[$k];
			}

			if ($k > 0 && isset($items[$k - 1]['header']) && $items[$k - 1]['header']) {
				$items[$k]['first'] = 1;
			}
		}

		return [
			'normal' => $normal,
			'vip' => $vip,
			'avatar' => $uInfo['avatar'],
			'thumb' => $uInfo['thumb'],
			'name' => $uInfo['name'],
			"showOtherFields" => self::hideFields($wx_uid),
		];
	}

	public static function reg($data)
	{
		$fields = [
			"phone" => "uPhone",
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

			"parent" => "uParent",
			"sibling" => "uSibling",
			"dwelling" => "uDwelling",
			"worktype" => "uWorkType",
			"employer" => "uEmployer",
			"music" => "uMusic",
			"book" => "uBook",
			"movie" => "uMovie",
			"highschool" => "uHighSchool",
			"university" => "uUniversity",
		];
		// ["parent","sibling","dwelling","worktype","employer"，"music","book","movie","highschool","university",]
		$avatar = isset($data["img"]) ? $data["img"] : '';
		unset($data['img']);
		if ($avatar) {
			list($thumb, $figure) = ImageUtil::save2Server($avatar, true);
			$data["thumb"] = $thumb;
			$data["img"] = $figure;
			$data["status"] = self::STATUS_PENDING;
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
				$userData[$field] = trim($data[$k], ",");
			}
		}

		//Rain: 新注册的用户，假如没有添加album，直接把头像放入album中
		if (!isset($userData['uAlbum']) && isset($userData['uAvatar']) && $userData['uAvatar']) {
			$album0 = $userData['uAvatar'];
			$album0 = str_replace('_n.', '.', $album0);
			$userData['uAlbum'] = [$album0];
		}

		$uid = self::add($userData);

		//Rain: 添加媒婆关系
		$conn = AppUtil::db();

		$sql = 'select * from im_user WHERE uId=:id ';
		$uInfo = $conn->createCommand($sql)->bindValues([
			':id' => $uid,
		])->queryOne();
		$uInfo = self::fmtRow($uInfo);
		if ($uInfo['status'] < self::STATUS_PRISON) {
			$newStatus = ($uInfo['percent'] < 31 ? self::STATUS_VISITOR : self::STATUS_PENDING);
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
		//Rain: 奖励新人66媒桂花
		UserTrans::addReward($uid, UserTrans::CAT_NEW, $conn);
		//Rain: 奖励媒婆99媒桂花
		UserTrans::addReward($uid, UserTrans::CAT_MOMENT_RECRUIT, $conn);
		QueueUtil::loadJob('regeo', ['id' => $uid]);

		return $uid;
	}

	public
	static function setAvatar($uid, $thumb = '', $figure = '', $adminId = 1)
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
			$Info->uStatus = self::STATUS_PENDING;
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

	public static function certInfo($id)
	{

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

	public static function certnew($ids, $openId)
	{
		$ids = json_decode($ids, 1);
		$urls = [];
		foreach ($ids as $v) {
			list($thumb, $url) = ImageUtil::save2Server($v["id"], false);
			$urls[] = [
				"tag" => $v["tag"],
				"url" => $url,
			];
		}
		// list($thumb, $url) = ImageUtil::save2Server($id, false);
		$Info = self::findOne(["uOpenId" => $openId]);
		if ($urls && $Info) {
			$uId = $Info->uId;
			$note = [
				'before' => $Info->uCertImage,
				'after' => $urls
			];
			LogAction::add($uId, $openId, LogAction::ACTION_CERT,
				json_encode($note, JSON_UNESCAPED_UNICODE));
			return self::edit($Info->uId, [
				"uCertImage" => json_encode($urls),
				"uCertStatus" => User::CERT_STATUS_PENDING,
				"uCertDate" => date("Y-m-d H:i:s")
			]);
		}
		return 0;
	}

	public static function editCert($uid, $certs)
	{
		$urls = [];
		foreach ($certs as $cert) {
			list($thumb, $url) = ImageUtil::save2Server($cert["id"], false);
			$urls[] = [
				"tag" => $cert["tag"],
				"url" => $url,
			];
		}
		// list($thumb, $url) = ImageUtil::save2Server($id, false);
		$Info = self::findOne(["uId" => $uid]);
		if ($urls && $Info) {
			$note = [
				'before' => $Info->uCertImage,
				'after' => $urls
			];
			LogAction::add($uid, $Info->uOpenId, LogAction::ACTION_CERT,
				json_encode($note, JSON_UNESCAPED_UNICODE));
			return self::edit($uid,
				[
					"uCertImage" => json_encode($urls),
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
			WechatUtil::templateMsg($flag == "pass" ? WechatUtil::NOTICE_CERT_GRANT : WechatUtil::NOTICE_CERT_DENY, $id);
			return self::edit($id, [
				"uCertStatus" => ($flag == "pass") ? User::CERT_STATUS_PASS : User::CERT_STATUS_FAIL,
				"uCertDate" => date("Y-m-d H:i:s"),
			], Admin::getAdminId());
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
		$myId = $myInfo->uId;
		// $uFilter = $myInfo->uFilter;
		$myFilter = self::criteria($myInfo);

		$gender = $myInfo->uGender;
		$birthYear = $myInfo->uBirthYear;
		$marital = $myInfo->uMarital;
		$ageLimit = $ageRank = '';
		$ageFrom = $ageTo = 0;
		if ($gender && $gender == self::GENDER_MALE && $birthYear) {
			$ageLimit = ' AND u.uBirthYear BETWEEN ' . ($birthYear - 3) . ' AND ' . ($birthYear + 9);
			$ageFrom = $birthYear - 3;
			$ageTo = $birthYear + 8;
		}
		if ($gender && $gender == self::GENDER_FEMALE && $birthYear) {
			$ageLimit = ' AND u.uBirthYear BETWEEN ' . ($birthYear - 9) . ' AND ' . ($birthYear + 2);
			$ageFrom = $birthYear - 8;
			$ageTo = $birthYear + 2;
		}

		$gender = ($gender == self::GENDER_FEMALE) ? self::GENDER_MALE : self::GENDER_FEMALE;
		$uRole = User::ROLE_SINGLE;

		$marry = $myInfo->uMarital == self::MARITAL_MARRIED ? self::MARITAL_MARRIED : implode(',', [self::MARITAL_UNMARRIED, self::MARITAL_DIVORCE_KID, self::MARITAL_DIVORCE_NO_KID]);
		if ($data && isset($data["loc"]) && isset($data["mar"]) && !($data["loc"] == "all" && $data["mar"] == "all")) {
			$condition = " u.uRole=$uRole AND u.uGender=$gender  AND u.uStatus in (" . implode(',', self::$StatusVisible) . ") ";
		} else {
			$condition = " u.uRole=$uRole AND u.uGender=$gender and u.uMarital in ($marry) AND u.uStatus in (" . implode(',', self::$StatusVisible) . ") " . $ageLimit;
		}

		$prov = '江苏';
		$city = '盐城';
		$country = '东台';
		if (isset($myFilter['location'])) {
			list($prov, $city) = explode('-', $myFilter['location']);
		}
		$rankField = "(CASE WHEN u.uLocation like '%$prov%' and u.uLocation like '%$city%' then 10
					WHEN u.uLocation like '%$prov%' then 8 else 0 end) as rank";

		$ulocation = json_decode($myInfo->uLocation, 1);
		if (is_array($ulocation) && count($ulocation) >= 2) {
			list($prov, $city) = array_column($ulocation, 'text');
			$country = isset($ulocation[2]) ? $ulocation[2]["text"] : $country;
		}
		// 去掉筛选条件啦~
		/*
		 if (isset($data["age"]) && $data["age"] != 0) {
			$age = explode("-", $data["age"]);
			$year = date("Y");
			$ageStart = $year - $age[1];
			$ageEnd = $year - $age[0];
			$condition .= " and u.uBirthYear BETWEEN $ageStart AND $ageEnd ";
		}
		if (isset($data['location']) && $data['location']) {
			list($fp, $fc) = explode('-', $data['location']);
			//$condition .= $fc ? " and u.uLocation  like '%$fp%' && u.uLocation like '%$fc%' " : " and u.uLocation  like '%$fp%' ";
			$condition .= " AND u.uLocation like '%$fp%' ";
		}
		*/
		/*
		if (!$data) {
			$data = json_decode($uFilter, 1);
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
		$sql = "SELECT * from im_pin as p WHERE p.pCategory=$pinCat AND pPId=" . $myId;
		$ret = $conn->createCommand($sql)->queryOne();
		$myLat = $myLng = '0';
		$distField = ' 9999 as dist';
		$distRank = '0 as mRank';
		if ($ret) {
			$myLat = $ret['pLat'];
			$myLng = $ret['pLng'];
			$tmpDist = 'ROUND(IFNULL(ST_Distance(POINT(' . $myLat . ', ' . $myLng . '), p.pPoint) * 111.195,9999),1)';
			$distRank = "UNIX_TIMESTAMP(u.uLogDate)/1000 
							+ UNIX_TIMESTAMP(u.uAddedOn)/1000 
							+ (CASE WHEN u.uBirthYear BETWEEN " . $ageFrom . " AND " . $ageTo . " then 10000 else 0 END)
			                + (case when $tmpDist <= 30 then 40 
							when $tmpDist <= 60 AND $tmpDist > 30 then 32
							when $tmpDist <= 90 AND $tmpDist > 60 then 24
							when $tmpDist <= 120 AND $tmpDist > 90 then 16
							when $tmpDist <= 150 AND $tmpDist > 120 then 8
							else 0 end) as mRank";
			$distField = 'ROUND(IFNULL(ST_Distance(POINT(' . $myLat . ', ' . $myLng . '), p.pPoint) * 111.195,9999),1) as dist';
		}

		$loc = "江苏";

		switch ($marital) {
			case self::MARITAL_UNMARRIED:
				$strMarital = implode(',', [self::MARITAL_UNMARRIED]);
				$fmRank = "(CASE WHEN uMarital in ($strMarital) then 10 else 0 end) as fmRank";
				break;
			case self::MARITAL_DIVORCE_KID:
				$strMarital = implode(',', [self::MARITAL_DIVORCE_KID, self::MARITAL_DIVORCE_NO_KID]);
				$fmRank = "(CASE WHEN uMarital in ($strMarital) then 10 else 0 end) as fmRank";
				break;
			case self::MARITAL_DIVORCE_NO_KID:
				$fmRank = "(CASE WHEN uMarital=" . self::MARITAL_DIVORCE_NO_KID . " then 10
				 WHEN uMarital=" . self::MARITAL_DIVORCE_KID . " then 8
				 WHEN uMarital=" . self::MARITAL_UNMARRIED . " then 5
				 ELSE 0 END) as fmRank";
				break;
			default:
				$fmRank = "1 as fmRank";
				break;
		}
		$ageRank = "";
		if ($data) {
			$homeland = json_decode($myInfo->uHomeland, 1);
			$sheng = $prov;
			$shi = $city;
			$xian = $country;
			if (is_array($homeland) && count($homeland) >= 2) {
				$sheng = isset($homeland[0]) ? $homeland[0]["text"] : $prov;
				$shi = isset($homeland[1]) ? $homeland[1]["text"] : $city;
				$xian = isset($homeland[2]) ? $homeland[2]["text"] : $country;
			}
			if (isset($data['loc']) && $data['loc']) {
				$l = $data['loc'];
				switch ($l) {
					case "all":
						$loc = $shi;
						break;
					case "province":
						$loc = $prov;
						break;
					case "city":
						$loc = $city;
						break;
					case "county":
					case "30km":
						$loc = $country;
						break;
					case "fellow":
						$loc = $shi;
						break;
				}
			}
			if (isset($data['mar']) && $mar = $data['mar']) {
				if ($mar != "all") {
					$fmRank = "(CASE WHEN uMarital =$mar then 10 else 0 end) as fmRank";
				}
			}
			if (isset($data['age']) && $age = $data['age']) {
				if ($age == 1) {
					$ageRank = "u.uBirthYear asc,";
				} elseif ($age == 2) {
					$ageRank = "u.uBirthYear desc,";
				} elseif ($age == 3) {
					$condition .= $ageLimit;
				}
			}
		}
		$flRank = "(CASE WHEN uLocation like '%$prov%' then 10 else 0 end) as flRank";
		if ($city) {
			$flRank = "(CASE WHEN uLocation like '%$prov%' then 10 else 0 end) + (CASE WHEN uLocation like '%$city%' then 5 else 0 end) as flRank";
		}

		$sql = "SELECT u.*,
 				(CASE WHEN u.uOpenId LIKE 'oYDJew%' THEN IFNULL(h.hCount, 0) ELSE 9999 END) + (CASE WHEN uSubStatus=4 THEN 1 ELSE 99 END) as stickRank,
 				$distRank ,$distField ,$flRank,$fmRank
				FROM im_user as u 
				JOIN im_user_wechat as w on u.uId=w.wUId AND w.wSubscribe=1
				LEFT JOIN im_pin as p on p.pPId=u.uId AND p.pCategory=$pinCat
				LEFT JOIN im_hit as h on h.hUId=$myId AND h.hSubUId=u.uId
				WHERE $condition 
				ORDER BY flRank desc, stickRank,fmRank desc,$ageRank mRank desc limit $limit";

		//AppUtil::logFile($conn->createCommand($sql)->getRawSql(), 5, __FUNCTION__, __LINE__);
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
				WHERE n.nRelation=$relation_favor AND n.nDeletedFlag=0 AND n.nSubUId=$myId
				AND n.nUId in (" . implode(',', $IDs) . ")";
		$favorList = $conn->createCommand($sql)->queryAll();
		foreach ($favorList as $favor) {
			$uid = $favor['nUId'];
			if (isset($rows[$uid])) {
				$rows[$uid]['hid'] = $uid;
			}
		}

		$result = [];
		$tags = UserTag::tags($IDs);
		foreach ($rows as $row) {
			$data = [];
			//$data["id"] = $v["uOpenId"];
			//$data["ids"] = $v["uId"];
			$user_id = $row["uId"];
			Hit::add($myId, $row["uId"]);
			$data["tags"] = isset($tags[$user_id]) ? $tags[$user_id] : [];
			$data["secretId"] = AppUtil::encrypt($row["uId"]);
			$data["uni"] = $row["uUniqid"];
			$data["avatar"] = $row["uAvatar"];
			$data["thumb"] = $row["uThumb"];
			$data["cert"] = (isset($row["uCertStatus"]) && $row["uCertStatus"] == User::CERT_STATUS_PASS ? 1 : 0);
			$data["mavatar"] = $row["mpavatar"];
			$data["mpname"] = $row["mpname"];
			$data["comment"] = $row["comment"];
			$advise = '';
			if (strlen($row["dist"])) {
				if (floatval($row["dist"]) <= 1.0) {
					$data["dist"] = '距<1000m';
//				} elseif (floatval($row["dist"]) <= .9) {
//					$data["dist"] = '距' . intval(floatval($row["dist"]) * 1000.0) . 'm';
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
			//$data["height"] = isset(User::$Height[$row["uHeight"]]) ? User::$Height[$row["uHeight"]] : "无身高";
			$data["height"] = $row["uHeight"] . "cm";
			$data["horos"] = isset(User::$Horos[$row["uHoros"]]) ? User::$Horos[$row["uHoros"]] : "无星座";
			if ($data["horos"] && mb_strlen($data["horos"]) > 3) {
				$data["horos"] = mb_substr($data["horos"], 0, 3);
			}
			$data["job"] = isset(User::$Scope[$row["uScope"]]) ? User::$Scope[$row["uScope"]] : "无行业";
			$data["intro"] = $row["uIntro"];
			$location = json_decode($row["uLocation"], 1);
			$data["location_s"] = isset($location[0]) ? $location[0]["text"] : '';
			$location = $location ? implode(' ', array_column($location, 'text')) : '';
			$data["location"] = $location;
			$data["hintclass"] = $row["hid"] ? "icon-loved" : "icon-love";
			$data["favor"] = $row["hid"] ? 'favor' : '';
			$data["singleF"] = $isSingle;
			$result[] = $data;
		}
		$next_page = 0;
		if (count($result) >= $pageSize) {
			$next_page = $page + 1;
		}
		//Rain: 不想展示太多页了
		if ($next_page > 16) {
			$next_page = 0;
		}
		return [
			"data" => $result,
			"nextpage" => $next_page,
			//"condition" => $myFilter,
			"condition" => '',
			'page' => $page
		];
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

	public
	static function topSingle($uid, $page, $pageSize)
	{

	}

	public
	static function topMatcher($uid, $page = 1, $pageSize = 20)
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
	public
	static function sendSMSCode($phone)
	{
		if (!AppUtil::checkPhone($phone)) {
			return ['code' => 159, 'msg' => '手机格式不正确'];
		}
		if (AppUtil::isDev()) {
			return ['code' => 159, 'msg' => '悲催啊~ 只能发布到服务器端才能测试这个功能~'];
		}
		$redis = RedisUtil::init(RedisUtil::KEY_SMS_CODE_CNT, date('ymd'), $phone);
		$smsLimit = $redis->getCache();
		if (!$smsLimit) {
			$redis->setCache(1);
		} elseif ($smsLimit > self::$SmsCodeLimitPerDay) {
			return ['code' => 159, 'msg' => '每天获取验证码的次数不能超过' . self::$SmsCodeLimitPerDay . '次'];
		} else {
			$redis->setCache($smsLimit + 1);
		}
		$code = rand(100000, 999999);
		$minutes = 10;

		AppUtil::sendTXSMS($phone, AppUtil::SMS_NORMAL, ["params" => [strval($code), strval($minutes)]]);
		RedisUtil::init(RedisUtil::KEY_SMS_CODE, $phone)->setCache($code);
		return ['code' => 0, 'msg' => '验证码已发送到手机【' . $phone . '】<br>请注意查收手机短信'];
	}

	public
	static function verifySMSCode($phone, $code)
	{
		if ($code == self::$SMS_SUPER_PASS) {
			return true;
		}
		$smsCode = RedisUtil::init(RedisUtil::KEY_SMS_CODE, $phone)->getCache();
		return ($smsCode && $code == $smsCode);
	}

	public
	static function Filter($filter)
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

	public static function searchNet($kw, $subtag = "all")
	{
		if (!$kw) {
			return [];
		}
		$conStr = "";
		if ($subtag == "dummy") {
			$conStr = " and uNote='dummy' ";
		}
		$sql = "select uId as id,uAvatar as avatar,uName as uname,uPhone as phone 
			FROM im_user where uName like '%$kw%' $conStr ";
		$res = AppUtil::db()->createCommand($sql)->queryAll();
		return $res;
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
				'all' => array_values($itemAll),
				'female' => array_values($itemFemale),
				'male' => array_values($itemMale)
			];
		};

		$strCriteria = '';
		if ($gender) {
			$strCriteria = ' AND uGender=' . $gender;
		}
		$role = self::ROLE_SINGLE;
		$conn = AppUtil::db();
		$sql = "select COUNT(1) as co , IFNULL(uMarital,0) as val, uGender as gender
				from im_user 
				WHERE uStatus <8 and uRole=:role AND uGender>9 AND uOpenId LIKE 'oYDJew%'
					and uAddedOn between :sDate and :eDate $strCriteria
				GROUP by val, uGender ";

		$ret = $conn->createCommand($sql)->bindValues([
			":role" => $role,
			":sDate" => $beginDate . ' 00:00:00',
			":eDate" => $endDate . ' 23:59:00',
		])->queryAll();
		$marrayData = $fmtRet($ret, self::$Marital);
//		var_dump($marrayData);

		$sql = "select COUNT(1) as co ,uIncome as val, uGender as gender
				from im_user 
				where uStatus <8 and uRole=:role AND uGender>9 AND uOpenId LIKE 'oYDJew%'
					and uAddedOn between :sDate and :eDate $strCriteria
				GROUP by uIncome,uGender ";
		$ret = $conn->createCommand($sql)->bindValues([
			":role" => $role,
			":sDate" => $beginDate . ' 00:00:00',
			":eDate" => $endDate . ' 23:59:00',
		])->queryAll();
		$incomeData = $fmtRet($ret, self::$Income);

		$sql = "select COUNT(1) as co ,uEducation as val, uGender as gender
				from im_user 
				where uStatus <8 and uRole=:role AND uGender>9 AND uOpenId LIKE 'oYDJew%'
					and uAddedOn between :sDate and :eDate $strCriteria
				GROUP by uEducation,uGender ";
		$ret = $conn->createCommand($sql)->bindValues([
			":role" => $role,
			":sDate" => $beginDate . ' 00:00:00',
			":eDate" => $endDate . ' 23:59:00',
		])->queryAll();
		$eduData = $fmtRet($ret, self::$Education);


		$sql = "select COUNT(1) as co ,uGender as gender 
				from im_user 
				where uStatus < 8 and uRole=:role AND uGender>9 AND uOpenId LIKE 'oYDJew%'
					and uAddedOn between :sDate and :eDate $strCriteria
				GROUP by uGender";
		$gender = $conn->createCommand($sql)->bindValues([
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

		$sql = "SELECT COUNT(1) as co , 
				(case 
				 when uHeight BETWEEN 100 AND 150 THEN 1
				 when uHeight BETWEEN 151 AND 155 THEN 2
				 when uHeight BETWEEN 156 AND 160 THEN 3
				 when uHeight BETWEEN 161 AND 165 THEN 4
				 when uHeight BETWEEN 166 AND 170 THEN 5
				 when uHeight BETWEEN 171 AND 175 THEN 6
				 when uHeight BETWEEN 176 AND 180 THEN 7
				 when uHeight BETWEEN 181 AND 185 THEN 8
				 when uHeight > 185 THEN 9
				 else 0 end) as val, uGender as gender
				FROM im_user 
				WHERE uStatus < 8 AND uRole=:role AND uGender>9 AND uOpenId LIKE 'oYDJew%'
					AND uAddedOn BETWEEN :sDate AND :eDate $strCriteria
				GROUP by val,uGender";
		$ret = $conn->createCommand($sql)->bindValues([
			":role" => $role,
			":sDate" => $beginDate . ' 00:00:00',
			":eDate" => $endDate . ' 23:59:00',
		])->queryAll();
		$heightNames = ['其他', '150及以下', '151~155', '156~160', '161~165', '166~170', '171~175', '176~180', '181~185', '186及以上'];
		$heightData = $fmtRet($ret, $heightNames);

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
						WHERE uStatus < 8 and uRole=:role AND uGender>9 AND uBirthYear>0 AND uOpenId LIKE \'oYDJew%\'
							and uAddedOn between :sDate and :eDate) as t
				 group by val,gender';
		$ret = $conn->createCommand($sql)->bindValues([
			":role" => $role,
			":sDate" => $beginDate . ' 00:00:00',
			":eDate" => $endDate . ' 23:59:00',
		])->queryAll();
		$ageNames = ['20及以下', '21~25', '26~30', '31~35', '36~40', '41~45', '46~55', '56及以上'];
		$ageData = $fmtRet($ret, $ageNames);

		$sql = 'select count(DISTINCT aUId) as cnt, DATE_FORMAT(aDate,\'%H\') as hr, uGender as gender
			 from im_log_action as a  
			 JOIN im_user as u on u.uId=a.aUId
			 WHERE u.uGender>9 AND uRole=:role AND aDate BETWEEN :sDate AND :eDate AND uOpenId LIKE \'oYDJew%\'
			 GROUP BY hr,gender';
		$ret = $conn->createCommand($sql)->bindValues([
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
			'mar' => $marrayData,
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

	public static function hiDummies($page = 1, $resetFlag = false)
	{
		$dummies = self::topDummies($resetFlag);
		//var_dump($dummies);
		if ($page > 9) {
			$page = 1;
		}
		$items = array_merge(array_slice($dummies[self::GENDER_FEMALE], $page * 6, 6),
			array_slice($dummies[self::GENDER_MALE], $page * 6, 6));
		shuffle($items);
		foreach ($items as $k => $item) {
			$items[$k]['sid'] = AppUtil::encrypt($item['uId']);
		}
		return [$items, $page + 1];
	}

	// 后台聊天稻草人
	public static function topDummies($resetFlag = false)
	{
		$redis = RedisUtil::init(RedisUtil::KEY_DUMMY_TOP);
		$ret = json_decode($redis->getCache(), 1);
		if (!$ret || $resetFlag) {
			$conn = AppUtil::db();
			$sql = "SELECT * FROM (SELECT uName,uThumb,uId,uGender,uLocation,uHomeLand,uBirthYear FROM im_user 
				 WHERE uOpenId not LIKE :openid AND uHomeLand!='' AND uStatus=:st AND uGender=:female LIMIT 60) as f
				 UNION
				 SELECT * FROM (SELECT uName,uThumb,uId,uGender,uLocation,uHomeLand,uBirthYear FROM im_user 
				 WHERE uOpenId not LIKE :openid AND uHomeLand!='' AND uStatus=:st AND uGender=:male LIMIT 60) as m";
			$ret = $conn->createCommand($sql)->bindValues([
				":st" => self::STATUS_ACTIVE,
				":male" => self::GENDER_MALE,
				":female" => self::GENDER_FEMALE,
				":openid" => 'oYDJew%'
			])->queryAll();
			$redis->setCache($ret);
		}
		$res = [];
		foreach ($ret as $row) {
			$row["location"] = '';
			if (isset($row['uLocation']) && $row['uLocation']) {
				$text = array_column(json_decode($row['uLocation'], 1), 'text');
				$row["location"] = implode(' ', $text);
			}
			$row["homeland"] = '';
			if (isset($row['uHomeLand']) && $row['uHomeLand']) {
				$text = array_column(json_decode($row['uHomeLand'], 1), 'text');
				$row["homeland"] = implode(' ', $text);
			}
			$row["age"] = date('Y') - $row['uBirthYear'];
			unset($row['uBirthYear'], $row['uHomeLand'], $row['uLocation']);
			if ($row["uGender"] == self::GENDER_MALE) {
				$res[self::GENDER_FEMALE][] = $row;
			} elseif ($row["uGender"] == self::GENDER_FEMALE) {
				$res[self::GENDER_MALE][] = $row;
			}
		}
		return $res;
	}

	public static function getCerts($certData = '')
	{
		if ($certData && strpos($certData, 'http') === 0) {
			return [
				[
					'tag' => 'zm',
					'url' => $certData
				]
			];
		}
		$certs = json_decode($certData, 1);
		return $certs ? $certs : [];
	}

	public static function recommendUsers($uid, $conn = null)
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$key = 101;
		$sql = "SELECT count(a.aId) as cnt
			  FROM im_log_action as a 
			  WHERE a.aKey=:key and a.aUId=:uid ";
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':key' => $key,
		])->queryScalar();
		if ($ret) {
			return [];
		}

		$sql = 'select uId, uGender, uBirthYear, uHoros, uLocation, uOpenId from im_user where uId=:uid';
		$userInfo = $conn->createCommand($sql)->bindValues([
			':uid' => $uid
		])->queryOne();
		if (!$userInfo['uGender'] || !$userInfo['uBirthYear'] || !$userInfo['uHoros']) {
			return [];
		}
		LogAction::add($uid, $userInfo['uOpenId'], LogAction::ACTION_GREETING, '', $key);

		$province = $city = '';
		$location = json_decode($userInfo['uLocation'], 1);
		if ($location && count($location) > 1) {
			list($province, $city) = array_column($location, 'text');
		}
		$findGender = $userInfo['uGender'] == self::GENDER_MALE ? self::GENDER_FEMALE : self::GENDER_MALE;
		if ($findGender == self::GENDER_FEMALE) {
			//return [];
		}
		$y0 = $userInfo['uBirthYear'];
		$y1 = $userInfo['uBirthYear'] + 4;
		$criteriaYear = ' AND uStatus=1 AND uBirthYear between ' . $userInfo['uBirthYear'] . ' AND ' . ($userInfo['uBirthYear'] + 6);
		if ($findGender == self::GENDER_MALE) {
			$y0 = $userInfo['uBirthYear'] - 4;
			$y1 = $userInfo['uBirthYear'];
			$criteriaYear = ' AND uStatus=1 AND uBirthYear between ' . ($userInfo['uBirthYear'] - 6) . ' AND ' . $userInfo['uBirthYear'];
		}
		$sql = "select uId as id,uName,uThumb as thumb,'age' as cat  
			from im_user where uGender=:gender and uOpenId like 'oYDJew%' and uPhone!='' 
			and uBirthYear between :y0 and :y1 $criteriaYear 
			limit 20";
		$users = $conn->createCommand($sql)->bindValues([
			':gender' => $findGender,
			':y0' => $y0,
			':y1' => $y1,
		])->queryAll();

		$sql = "select uId as id,uName,uThumb as thumb,'horos' as cat  
			from im_user where uGender=:gender and uOpenId like 'oYDJew%' and uPhone!='' 
			and uHoros =:horo $criteriaYear
			limit 20";
		$ret = $conn->createCommand($sql)->bindValues([
			':gender' => $findGender,
			':horo' => $userInfo['uHoros']
		])->queryAll();
		$users = array_merge($users, $ret);

		$sql = "select uId as id,uName,uThumb as thumb,'location' as cat  
			from im_user where uGender=:gender and uOpenId like 'oYDJew%' and uPhone!='' 
			and uLocation like :prov and uLocation like :city $criteriaYear
			limit 20";
		$ret = $conn->createCommand($sql)->bindValues([
			':gender' => $findGender,
			':prov' => '%' . $province . '%',
			':city' => '%' . $city . '%',
		])->queryAll();
		$users = array_merge($users, $ret);

		$ta = $findGender == self::GENDER_MALE ? '他' : '她';
		$captions = [
			'location' => $ta . '跟你一个地区的哦',
			'horos' => $ta . '的星座跟你很配哦',
			'age' => $ta . '的年龄跟你匹配哦',
		];
		foreach ($users as $k => $user) {
			$users[$k]['title'] = $captions[$user['cat']];
		}
		shuffle($users);
		$items = $ids = [];
		foreach ($users as $user) {
			if (in_array($user['id'], $ids)) {
				continue;
			}
			$items[] = $user;
			$ids[] = $user['id'];
			if (count($items) == 6) {
				break;
			}
		}
//		$users = array_slice($users, 0, 6);
		return $items;
	}

	public static function coinReward($uid, $open_id, $conn = null)
	{
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$key = 103;
		$sql = "SELECT count(a.aId) as cnt
			  FROM im_log_action as a 
			  WHERE a.aKey=:key and a.aUId=:uid ";
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':key' => $key,
		])->queryScalar();
		if ($ret) {
			return false;
		}
		LogAction::add($uid, $open_id, LogAction::ACTION_GREETING, '', $key);
		return true;
	}

	/**
	 * 单身详细页面 对等显示字段(如果是VIP 全部显示)
	 * @param $uid
	 * @return int
	 */
	public static function hideFields($uid)
	{
		$flag = 1;

		// 如果是会员VIP 直接返回 true
		if (UserTag::hasCard($uid, UserTag::CAT_MEMBER_VIP)) {
			return 1;
		}

		//对等显示字段
		$showFields = ["profession_t", "weight_t", "scope_t", "income_t", "estate_txt", "car_t", "belief_t", "pet_t", "diet_t", "fitness_t", "book", "music", "movie"];
		$uInfo = User::fmtRow(User::findOne(["uId" => $uid])->toArray());
		foreach ($showFields as $v) {
			if (!$uInfo[$v]) {
				$flag = 0;
				break;
			}
		}

		return $flag;

	}
}
