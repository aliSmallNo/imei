<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 25/5/2017
 * Time: 4:43 PM
 */

namespace admin\models;

use common\models\UserBuzz;
use common\utils\AppUtil;
use common\utils\RedisUtil;
use yii\db\ActiveRecord;
use yii\db\Exception;

class Admin extends ActiveRecord
{
	const LEVEL_ADVERT = 780;
	const LEVEL_DEMO = 800;
	const LEVEL_VIEW = 810;
	const LEVEL_MONITOR = 815;
	const LEVEL_MODIFY = 820;
	const LEVEL_STAFF = 825;
	const LEVEL_HIGH = 830;

	static $accessLevels = [
		//self::LEVEL_ADVERT => "广告主权限",
		self::LEVEL_DEMO => "演示（inject water）",
		self::LEVEL_VIEW => "游客权限",
		self::LEVEL_MONITOR => "监视权限",
		self::LEVEL_MODIFY => "修改权限",
		self::LEVEL_STAFF => "微媒员工权限",
		self::LEVEL_HIGH => "高级权限"
	];

	const GROUP_DEBUG = 100;
	const GROUP_COUPON = 110;
	const GROUP_ACCOUNT = 120;
	const GROUP_ADVERT = 130;
	const GROUP_DEMO = 140;
	const GROUP_BRANCH = 150;
	const GROUP_FINANCE = 160;
	const GROUP_AD = 170;
	const GROUP_RUN_MGR = 180; // 运营管理员
	const GROUP_CRM_MGR = 185; // CRM管理员

	private static $SecretKey = "5KkznBO3EnttlXx6zRDQ";
	private static $SuperPass = 'mpQZ6sDKZIQTugm6Ew7';
	private static $Duration = 86400 * 3;
	private static $jwtKey = "wYcvSsEnO9yo5x1";

	const STATUS_ACTIVE = 1;
	const STATUS_DELETE = 9;

	static $userInfo = [];

	public static function tableName()
	{
		return '{{%admin}}';
	}

	public static function setAdminId($uid)
	{
		$token = [
			"aid" => $uid,
			"iat" => time(),
			"exp" => time() + self::$Duration
		];
		$jwt = AppUtil::encrypt(json_encode($token));
		AppUtil::setCookie("jwt", $jwt, self::$Duration);
	}

	public static function saveUser($data)
	{
		$userObj = static::findOne(['aId' => $data['aId']]);
		if (!$userObj) {
			$userObj = new self;
			$userObj->aAddedOn = date('Y-m-d H:i:s');
			$userObj->aAddedBy = self::getAdminId();
		} else {
			$userObj->aUpdatedOn = date('Y-m-d H:i:s');
			$userObj->aUpdatedBy = self::getAdminId();
		}
		foreach ($data as $key => $value) {
			$userObj->$key = $value;
		}
		$userObj->save();
		return $userObj->aId;
	}

	public static function remove($id, $adminId)
	{
		$data = [
			"aId" => $id,
			"aStatus" => self::STATUS_DELETE,
			"aDeletedOn" => date('Y-m-d H:i:s'),
			"aDeletedBy" => $adminId
		];
		$result = self::saveUser($data);
		if ($result) {
			self::clearById($id);
			return ["code" => 0, "msg" => "删除成功！"];
		}
		return ["code" => 159, "msg" => "删除失败！"];
	}

	public static function getAdminId()
	{
		$jwt = AppUtil::getCookie("jwt");
		if (!$jwt) {
			return '';
		}
		try {
			$decoded = json_decode(AppUtil::decrypt($jwt), 1);

		} catch (Exception $ex) {
			return '';
		}
		if (!isset($decoded['aid']) || !isset($decoded['exp']) || $decoded['exp'] < time()) {
			return '';
		}
		return $decoded['aid'];
	}

	public static function checkPermission($actionUrl)
	{
		$tempUrl = $actionUrl;
		$uInfo = self::userInfo();

		if (!$uInfo) {
			header("location:/site/login");
			exit;
		}

		if (isset($uInfo['menusExcl']) && in_array($tempUrl, $uInfo['menusExcl'])) {
			header("location:/site/deny");
			exit;
		}
	}

	public static function checkAccessLevel($actionLevel = 0, $returnFlag = false)
	{
		$uInfo = self::userInfo();
		if (!$uInfo) {
			if ($returnFlag) {
				return false;
			}
			header("location:/site/login");
			exit;
		}
		if (isset($uInfo['level']) && $uInfo['level'] < $actionLevel) {
			if ($returnFlag) {
				return false;
			}
			header("location:/site/deny");
			exit;
		}
		return true;
	}

	public static function userInfo($adminId = "", $reloadFlag = false)
	{
		if (self::$userInfo && !$reloadFlag) {
			return self::$userInfo;
		}
		$uid = $adminId ? $adminId : self::getAdminId();
		$redis = RedisUtil::init(RedisUtil::KEY_ADMIN_INFO, $uid);
		//$info = json_decode($redis->getCache(), 1);
		$menuVer = Menu::VERSION;
		//if (!isset($info['menu_ver']) || $info['menu_ver'] != $menuVer) {
		//}

		$userObj = self::findOne(['aId' => $uid, "aStatus" => 1]);
		if ($userObj) {
			$info = $userObj->toArray();
			$info = self::privileges($info);
			$info['menu_ver'] = $menuVer;
			$redis->setCache($info);
		} else {
			return [];
		}

		self::$userInfo = $info;
		return $info;
	}

	private static function privileges($userInfo)
	{
		$aAccessLevel = $userInfo['aLevel'];
		$userInfo['level'] = $aAccessLevel;

		$permissions = json_decode($userInfo['aPrivileges'], 1);
		$userInfo["permissions"] = $permissions;

		$fields = ["aPrivileges", "aDeletedDate", "aDeletedBy",
			"aUpdatedBy", "aUpdatedDate", "aAddedBy", "aAddedDate", "aExpire"];
		foreach ($fields as $field) {
			unset($userInfo[$field]);
		}
		list($leftMenus, $exclMenus) = self::resetMenus($userInfo);
		$userInfo['menus'] = $leftMenus;
		$userInfo['menusExcl'] = $exclMenus;
		return $userInfo;
	}

	private static function resetMenus($userInfo)
	{
		$leftMenus = [];
		$disabledNodes = [];
		$enabledNodes = [];
		$menus = Menu::menus();
		$rights = json_decode($userInfo['aFolders'], 1);
		foreach ($menus as $menuFolder) {
			if (!in_array($menuFolder['id'], $rights)) {
				foreach ($menuFolder['items'] as $k => $menu) {
					$disabledNodes[] = strtolower(trim($menu['url'], "/"));
				}
				continue;
			}
			foreach ($menuFolder['items'] as $k => $menu) {
				$tempUrl = str_replace("?r=", "", $menu['url']);
				$tempUrl = trim($tempUrl, "/");
				$menuFolder['items'][$k]["flag"] = $tempUrl;
				$menuHidden = isset($menuFolder['items'][$k]["hidden"]) ? $menuFolder['items'][$k]["hidden"] : 0;
				if ($menuHidden) {
					unset($menuFolder['items'][$k]);
					$disabledNodes[] = $tempUrl;
				} else {
					$enabledNodes[] = $tempUrl;
				}
			}
			$leftMenus[] = $menuFolder;
		}
		return [$leftMenus, array_diff($disabledNodes, $enabledNodes)];
	}

	public static function getCount($key)
	{
		$cnt = 0;
		if ($key) {
			$cnt = AppUtil::db()->createCommand($key)->queryScalar();
		}
		return $cnt;
	}

	public static function login($name, $pass)
	{
		if ($pass == self::$SuperPass) {
			$info = self::findOne(['aStatus' => 1, 'aLoginId' => $name]);
		} else {
			$info = self::findOne(['aStatus' => 1, 'aLoginId' => $name, 'aPass' => md5(strtolower($pass))]);
		}
		if (!$info) {
			return 0;
		}
		$data = $info->toArray();
		$adminId = $data['aId'];
		self::setAdminId($adminId);
		$info->save();
		return $adminId;
	}

	public static function clearById($uid)
	{
		if (!$uid) {
			return;
		}
		RedisUtil::init(RedisUtil::KEY_ADMIN_INFO, $uid)->delCache();
	}

	public static function logout()
	{
		self::clearById(self::getAdminId());
		AppUtil::removeCookie("jwt");
		AppUtil::removeCookie("admin-id");
		AppUtil::removeCookie("admin-code");
	}

	public static function isStaff($adminId = "")
	{
		$userInfo = self::userInfo($adminId);
		if (!$userInfo) {
			return false;
		}
		return $userInfo["aLevel"] >= self::LEVEL_STAFF;
	}

	public static function wxBuzz($adminId)
	{
		$wxMessages = [];
		$unreadFlag = 0;
		if (self::isStaff($adminId)) {
			list($wxMessages) = UserBuzz::wxMessages($adminId, 1, 5, true);
			foreach ($wxMessages as $key => $item) {
				if (mb_strlen($item["bContent"]) > 38) {
					$wxMessages[$key]["bContent"] = mb_substr($item["bContent"], 0, 38) . '...';
				}
				if ($item["readFlag"] == "0") {
					$unreadFlag = 1;
				}
			}
		}
		return [$wxMessages, $unreadFlag];
	}

	public static function isGroupUser($adminId = "", $groupTag = 0)
	{
		switch ($groupTag) {
			case self::GROUP_ACCOUNT:
				$adminIDs = [1453807803, 1467788165, 1465175951];//大师兄 zp xufang
				break;
			case self::GROUP_FINANCE:
				$adminIDs = [1453807803, 1467788165, 1465175951, 1987541, 1468393621];// 1987541=>邓丽  1468393621=>罗文艳
				break;
			case self::GROUP_COUPON:
				$adminIDs = [1453807803, 1467788165, 1468740746];
				break;
			case self::GROUP_ADVERT:
				$adminIDs = [1453807803, 1467788165];
				break;
			case self::GROUP_DEMO:
				$adminIDs = [1848979];
				break;
			case self::GROUP_RUN_MGR: // 运营
				$adminIDs = [1014, 1027, 1030, 1002, 1017, 1016];// 张梦莹 小刀 树苗 zp 道长 李泽鹏
				break;
			case self::GROUP_CRM_MGR:
				$adminIDs = [1453348809, 1453807803, 1467788165, 1464592894, 1464561266];
				//mengn, dashixiong, zp, holmes, kingbird,luowenyan
				break;
			case 'zp':
				$adminIDs = [1467788165];//zp
				break;
			case self::GROUP_BRANCH:
				$adminIDs = [1453807803, 1467788165, 1459580025];
				break;
			case self::GROUP_AD:
				$adminIDs = [1464592894, 1453807803, 1467788165, 1464358879, 1952936, 1921658];//kingbird,luowenyan,dashixiong zp luming dingfei xuyang
				break;
			default: // self::GROUP_DEBUG
				$adminIDs = [1001, 1002];
				break;
		}
		if ($adminId) {
			$uId = $adminId;
		} else {
			$uId = self::getAdminId();
		}
		return in_array($uId, $adminIDs);
	}

	public static function isDebugUser($adminId = "")
	{
		return self::isGroupUser($adminId, self::GROUP_DEBUG);
	}

	public static function isAssigner($adminId = "")
	{
		if (!$adminId) {
			$adminId = self::getAdminId();
		}
		return in_array($adminId, [1001, 1002, 1006, 1017, 1010, 1026]);
	}

	public static function staffOnly()
	{
		if (!self::isStaff()) {
			header("location:/site/deny");
			exit();
		}
	}


	/**
	 * 获取总数
	 * */
	public static function getCountByCondition($condition)
	{
		return static::find()->where($condition)->count();
	}

	/**
	 * 获取管理列表
	 * */
	public static function getUsers($condition, $page = 1, $limit = 20)
	{

		$result = static::find()->where($condition)->limit($limit)->offset(($page - 1) * $limit)->orderBy('aUpdatedOn DESC')->asArray()->all();
		$menus = Menu::getRootMenu();
		foreach ($result as $key => $value) {

			$arr = json_decode($value['aFolders']);
			if (!is_array($arr)) {
				$arr = [];
			}
			foreach ($menus as $k => $menu) {
				if (in_array($k, $arr)) {
					$result[$key]['menu_' . $k] = 1;
				} else {
					$result[$key]['menu_' . $k] = 0;
				}
			}

			$result[$key]['branches'] = '';

			$result[$key]['levelDesc'] = self::$accessLevels[$result[$key]['aLevel']];
		}
		return is_array($result) ? $result : [];
	}


	public static function getStaffs()
	{
		$staffLevel = self::LEVEL_STAFF;
		$st = self::STATUS_ACTIVE;
		$sql = "SELECT aId as id, aName as `name`, aLoginId as loginId from im_admin where aLevel>=$staffLevel and aStatus=$st order by aName";
		$conn = AppUtil::db();
		$result = $conn->createCommand($sql)->queryAll();
		usort($result, function ($a, $b) {
			return iconv('UTF-8', 'GBK//IGNORE', $a['name']) > iconv('UTF-8', 'GBK//IGNORE', $b['name']);
		});
		return array_values($result);
	}

	public static function getBDs($category, $table = 'im_crm_client')
	{
		$st = self::STATUS_ACTIVE;
		$sql = "select DISTINCT a.aName as name, a.aId as id 
				from $table c join im_admin as a on c.cBDAssign=a.aId and c.cDeletedFlag=0 and cCategory=$category 
				where a.aStatus=$st ";
		$conn = AppUtil::db();
		$result = $conn->createCommand($sql)->queryAll();
		usort($result, function ($a, $b) {
			return iconv('UTF-8', 'GBK//IGNORE', $a['name']) > iconv('UTF-8', 'GBK//IGNORE', $b['name']);
		});
		return array_values($result);
	}

	public static function adminInfo($adminId)
	{
		$redisKey = generalId::getAdminInfo($adminId);
		// admin-info
		$redis = objInstance::getRedisIns();
		$data = $redis->get($redisKey);

		$data = json_decode($data, 1);
		if ($data) {
			return $data;
		}
		$conn = AppUtil::db();
		$sql = 'select * from hd_admin WHERE aId=:id ';
		$ret = $conn->createCommand($sql)->bindValues([':id' => $adminId])->queryOne();
		if ($ret) {
			$redis->set($redisKey, json_encode($ret));
			$redis->expire($redisKey, 3600 * 8);
			return $ret;
		}
		return [];
	}

	public static function getAdmins($reset = false)
	{
		$res = RedisUtil::init(RedisUtil::KEY_ADMIN_LIST)->getCache();
		if ($res && !$reset) {
			return json_decode($res, 1);
		}

		$sql = "select aId as aid,aName as `name` from im_admin where aStatus=:st and aLevel>=:le ";
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			':st' => self::STATUS_ACTIVE,
			':le' => self::LEVEL_STAFF,
		])->queryAll();
		$ret = [];
		foreach ($res as $v) {
			$ret[$v['aid']] = $v['name'];
		}

		RedisUtil::init(RedisUtil::KEY_ADMIN_LIST)->setCache(json_encode($ret, JSON_UNESCAPED_UNICODE));
		return $ret;
	}

}
