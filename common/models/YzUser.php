<?php
/**
 * Created by PhpStorm.
 * Time: 10:34 AM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\RedisUtil;
use common\utils\YouzanUtil;
use yii\db\ActiveRecord;

class YzUser extends ActiveRecord
{
	const TYPE_DEFAULT = 1;
	const TYPE_YXS = 3;
	static $typeDict = [
		self::TYPE_DEFAULT => '普通用户',
		self::TYPE_YXS => '严选师',
	];

	static $fieldMap = [
		'country' => 'uCountry',
		'province' => 'uProvince',
		'city' => 'uCity',
		'is_follow' => 'uFollow',
		'sex' => 'uSex',
		'avatar' => 'uAvatar',
		'nick' => 'uName',
		'follow_time' => 'uFollowTime',
		'user_id' => 'uYZUId',
		'weixin_open_id' => 'uOpenId',
		'union_id' => 'uUnionId',
		'points' => 'uPoint',
		'level_info' => 'uLevel',
		'traded_num' => 'uTradeNum',
		'trade_money' => 'uTradeMoney',

		'weixin_openid' => 'uOpenId',
		'tags' => 'uTags',
	];

	// 一个手机号有多个账户（小程序用户，公众号用户）
	// select count(1) as co,GROUP_CONCAT(uYZUId),GROUP_CONCAT(uName),GROUP_CONCAT(`uOpenId`),GROUP_CONCAT(uType) from im_yz_user where uPhone  group by uPhone order by co desc;

	public static function tableName()
	{
		return '{{%yz_user}}';
	}

	public static function edit($yzuid, $data)
	{
		if (!$yzuid || !$data) {
			return 0;
		}
		$entity = self::findOne(['uYZUId' => $yzuid]);
		if (!$entity) {
			$entity = new self();
		} else {
			$data['uUpdatedOn'] = date('Y-m-d H:i:s');
		}
		foreach ($data as $k => $v) {
			if ($k == 'uFromPhone') {
				// 不修改uFromPhone: 我们的合伙人会修改
				$entity->$k = $entity->uFromPhone ? $entity->uFromPhone : $v;
			} else {
				$entity->$k = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
			}
		}
		$entity->save();
		return $entity->uYZUId;
	}

	public static function process($v)
	{
		$uid = $v['user_id'];
		$insert = [];
		foreach (YzUser::$fieldMap as $key => $val) {
			if (isset($v[$key]) && $v[$key]) {
				if ($key == "nick") {
					$v[$key] = self::filterEmoji($v[$key]);
				}
				$insert[$val] = $v[$key];
			}
		}

		$insert['uRawData'] = json_encode($v, JSON_UNESCAPED_UNICODE);
		// echo $uid;print_r($insert);exit;
		return YzUser::edit($uid, $insert);
	}

	public static function filterEmoji($str)
	{
		$str = preg_replace_callback(
			'/./u',
			function (array $match) {
				return strlen($match[0]) >= 4 ? '' : $match[0];
			},
			$str);

		return $str;
	}

	/**
	 * 定时任务入口
	 */
	public static function UpdateUser($st = '', $et = '')
	{
		$st = $st ? $st : date('Y-m-d 00:00:00');
		$et = $et ? $et : date('Y-m-d 00:00:00', time() + 86400);
		self::getUserBySETime($st, $et, 1);

		if (in_array(date('H'), [8])) {
			echo '~~~~~~~~~~~~~~~~~~~~~~~' . PHP_EOL;
			self::getUserBySETime("2018-04-01 00:00:00", $et, 1);
		}
	}

	public static function getUserBySETime($st, $et, $isDebugger = false)
	{
		$dates = YouzanUtil::cal_se_date($st, $et);
		$page_size = 50;
		$total = 0;
		foreach ($dates as $date) {
			$stime = $date['stime'];
			$etime = $date['etime'];
			$stimeFmt = $date['stimeFmt'];
			$etimeFmt = $date['etimeFmt'];
			$page = 1;
			do {
				$results = self::getTZUser($stimeFmt, $etimeFmt, $page, $page_size, $isDebugger);
				$total_results = $results['total_results'] ?? 0;
				$users = $results['users'] ?? [];
				if (1) {
					if ($page == 1) {
						$total = $total + $total_results;
					}
					$msg = "stime:" . $stime . ':' . $stimeFmt . ' == etime:' . $etime . ':' . $etimeFmt . ' currentNum:' . $total_results . 'countRes:' . count($users) . ' Total:' . $total;
					if ($isDebugger) {
						echo $msg . PHP_EOL;
						AppUtil::logByFile($msg, YouzanUtil::LOG_YOUZAN_USER, __FUNCTION__, __LINE__);
					}
				}
				foreach ($users as $v) {
					self::process($v);
				}
				$page++;
			} while (count($users) == $page_size && $page < 20);
		}

		// 更新分销员信息
		self::getSalesManList($isDebugger);
	}

	/**
	 * 根据关注时间段批量查询微信粉丝用户信息（支持粉丝基础信息、积分、交易等数据查询，详见入参fields字段描述）。
	 * https://www.youzanyun.com/apilist/detail/group_scrm/user/youzan.users.weixin.followers.info.search
	 */
	public static function getTZUser($stime, $etime, $page, $page_size, $isDebugger = false)
	{
		$method = 'youzan.users.weixin.followers.info.search';
		$params = [
			'page_no' => $page,
			'page_size' => $page_size,
			'start_follow' => $stime,
			'end_follow' => $etime,
			'fields' => 'points,trade,level',
		];
		$ret = YouzanUtil::getData($method, $params);
		$retStyle = [
			'response' => [
				'users' => [
					[
						"nick" => "美好时光",
						"country" => "中国",
						"follow_time" => 1529892658,
						"is_follow" => true,
						"province" => "山东",
						"city" => "枣庄",
						"user_id" => 5843399220,
						"weixin_open_id" => "oj3YZwM-DA7_FQlGW5SnMPxAeNUA",
						"sex" => "f",
						"avatar" => "http=>//thirdwx.qlogo.cn/mmopen/AEyr0pyxIAyiaeTbR9CK5k55cpfjPnuGzxJyboNOmeOWa1p7P25t2orp2u1LuLj0PAiafFRiaW2DibnibfwRyicibz2YEtlF7BM5Y9H/132"
					],
					// ...
				],
				'total_results' => 6,
			]
		];
		$results = $ret['response'] ?? 0;

		$msg = "stime:" . $stime . ' == etime:' . $etime . ' == ' . 'page:' . $page . ' == ' . 'pagesize:' . $page_size;
		if ($isDebugger) {
			echo $msg . PHP_EOL;
			AppUtil::logByFile($msg, YouzanUtil::LOG_YOUZAN_USER, __FUNCTION__, __LINE__);
		}

		$total_results = $results['total_results'] ?? 0;
		$users = $results['users'] ?? [];

		return [$users, $total_results];

	}

	/**
	 * https://www.youzanyun.com/apilist/detail/group_ump/salesman/youzan.salesman.accounts.get
	 */
	public static function getSalesManList($isDebugger = false)
	{
		$getSales = function ($page, $isDebugger) {
			//获取当前店铺分销员列表，需申请高级权限方可调用。
			$method = 'youzan.salesman.accounts.get';
			$params = [
				'page_no' => $page,
				'page_size' => 20,
			];
			$res = YouzanUtil::getData($method, $params);
			$resStyle = [
				'response' => [
					'accounts' => [
						[
							"seller" => "3NFNEE",
							"from_buyer_mobile" => "15963761328",
							"money" => "0.00",
							"mobile" => "13176188080",
							"nickname" => "金刚瓢瓢娃",
							"created_at" => "2018-06-26 18:55:02",
							"order_num" => 0,
							"fans_id" => 5861354382
						],
						// ...
					],
					'total_results' => 979,
				]
			];
			if (isset($res['response'])) {
				$total_results = $res['response']['total_results'];
				if ($total_results) {
					return [$res['response']['accounts'], $total_results];
				}
			}
			return 0;
		};

		$res = $getSales(1, $isDebugger);
		$addCount = $editCount = 0;
		if ($res) {
			$total_results = $res[1];
			$pages = ceil($total_results / 20);
			$msg = '$total_results: ' . $total_results . ' $pages:' . $pages;
			if ($isDebugger) {
				echo $msg . PHP_EOL;
			}

			for ($p = 1; $p <= $pages; $p++) {
				$ret = $getSales($p, $isDebugger);
				if ($ret) {
					$ret = $ret[0];
					foreach ($ret as $k => $v) {
						$insert = [
							'uFromPhone' => $v['from_buyer_mobile'] ?? '',
							'uFromPhoneBak' => $v['from_buyer_mobile'] ?? '',
							'uPhone' => $v['mobile'] ?? '',
							'uCreateOn' => $v['created_at'] ?? '',
							'uSeller' => $v['seller'] ?? '',
							'uType' => self::TYPE_YXS,
						];
						$fansId = $v['fans_id'];
						// 注：$fansId 有等于0 的情况，实际严选师大于拉取的严选师
						if ($fansId && !self::findOne(['uYZUId' => $fansId])) {
							$addCount++;
							self::getUserInfoByTag($fansId);
						} elseif (!$fansId && $insert['uPhone']) {
							// 没卵用
							//$editCount++;
							//$fansId = self::use_phone_get_user_info($insert['uPhone']);
						}
						self::edit($fansId, $insert);
						if ($isDebugger) {
							echo '$fansId:' . $fansId . PHP_EOL;
						}
					}
				}
			}
		}

		$msg = '$addCount:' . $addCount . ' == $editCount:' . $editCount;
		if ($isDebugger) {
			echo $msg . PHP_EOL;
			AppUtil::logByFile($msg, YouzanUtil::LOG_YOUZAN_USER, __FUNCTION__, __LINE__);
		}
	}


	public static function getUserInfoByTag($id, $tag = 'fans_id', $isDebugger = false)
	{

		$method = 'youzan.users.weixin.follower.get';
		switch ($tag) {
			case "fans_id":
				$params = [
					'fans_id' => $id,
				];
				break;
			case "weixin_openid":
				$params = [
					'weixin_openid' => $id
				];
				break;
		}

		$res = YouzanUtil::getData($method, $params);

		$msg = is_array($res) ? json_encode($res) : $res;
		if ($isDebugger) {
			echo $msg . PHP_EOL;
			AppUtil::logByFile($msg, YouzanUtil::LOG_YOUZAN_USER, __FUNCTION__, __LINE__);
		}

		$resStyle = [
			"response" => [
				"user" => [
					"is_follow" => true,
					"city" => "盐城",
					"sex" => "m",
					"avatar" => "http://thirdwx.qlogo.cn/mmopen/pMNhp8zQy8vEKlbnX7hTxfZpZ3asyARUOQGXJoTWJtFUnVYXDmJhibFGDPaZicmiaWU99c18WvJf6RygicbGmavuHCkshuaARsNB/132",
					"traded_num" => 2,
					"points" => 220,
					"tags" => [
					],
					"nick" => "饭先生",
					"follow_time" => 1499842901,
					"province" => "江苏",
					"user_id" => 5305912017,
					"union_id" => "oWYqJwQEwMPBKQ_qIJDGfwQscoWM",
					"level_info" => [
					],
					"traded_money" => "11.49",
					"weixin_openid" => "oj3YZwFKcXhyhq1vOLPO3YpfSMLY"
				],
			]
		];

		if (isset($res['response']) && isset($res['response']['user'])) {
			$user = $res['response']['user'];
			return self::process($user);
		}

		return 0;

	}

	public static function use_phone_get_user_info($phone)
	{
		// 根据用户手机号获取用户openID
		// https://www.youzanyun.com/apilist/detail/group_scrm/user/youzan.user.weixin.openid.get

		$method = 'youzan.user.weixin.openid.get';
		$params = [
			'mobile' => $phone,
			'country_code' => '+86',
		];
		$res = YouzanUtil::getData($method, $params);
		$resStyle = [
			'response' => [
				'open_id' => 'oF-_pwIxCPA-4732HyLafI51810A',
			]
		];
		$open_id = isset($res['response']) ? $res['response']['open_id'] : '';

		$fansId = 0;
		if ($open_id) {
			$fansId = self::getUserInfoByTag($open_id, 'weixin_openid');
		}
		// echo '$phone:' . $phone . ' $open_id:' . $open_id . ' $fansId' . $fansId . PHP_EOL;
		return $fansId;
	}

	/**
	 * 根据微信粉丝Id正序批量查询微信粉丝用户信息（不受关注时间限制。支持粉丝基础信息、积分、交易等数据查询，详见入参fields字段描述）
	 */
	public static function getYZUserByFansIdAscCycle($isDebugger = false)
	{

		$last_fansId = RedisUtil::init(RedisUtil::KEY_YOUZAN_LAST_FANSID)->getCache();

		$return_lastFansId = $last_fansId ? $last_fansId : 0;

		$co = 0;
		while ($return_lastFansId > 0) {
			$co++;
			if ($isDebugger) {
				echo 'getYZUserByFansIdAscCycle:' . $co . PHP_EOL;
			}
			$return_lastFansId = self::getYZUserByFansIdAsc($return_lastFansId, $isDebugger);
			if ($co > 100) {
				break;
			}
		}

		// 更新分销员信息
		self::getSalesManList($isDebugger);
	}

	/**
	 * 根据微信粉丝Id正序批量查询微信粉丝用户信息（不受关注时间限制。支持粉丝基础信息、积分、交易等数据查询，详见入参fields字段描述）
	 * 注意：
	 * 1. 如果接口频繁抛异常，且入参无误，请减小page_size并重试。
	 * 2.请尽量按需自定义入参“fields”字段获取数据。“fields”字段传入枚举值越多，查询数据耗费时间越长。
	 */
	public static function getYZUserByFansIdAsc($last_fansId = 0, $isDebugger = false)
	{
		$method = 'youzan.users.weixin.followers.info.pull';
		$params = [
			'after_fans_id' => $last_fansId,
			'page_size' => 50,
			'fields' => 'points,trade,level',
		];
		$res = YouzanUtil::getData($method, $params);
		if (isset($res['response'])
			&& isset($res['response']['has_next'])
			&& $res['response']['has_next'] == true) {

			$users = $res['response']['users'];
			$last_fansId = $res['response']['last_fans_id'];

			foreach ($users as $v) {
				$fansId = $v['user_id'];
				if ($isDebugger) {
					echo 'edit fans_id:' . $fansId . PHP_EOL;
					AppUtil::logByFile('fans_id:' . $fansId, YouzanUtil::LOG_YOUZAN_USER, __FUNCTION__, __LINE__);
				}
				self::process($v);
			}
			if ($last_fansId > 0) {
				RedisUtil::init(RedisUtil::KEY_YOUZAN_LAST_FANSID)->setCache($last_fansId);
			}
			return $last_fansId;
		}

		$resSucessStyle = [
			'response' => [
				'has_next' => true,
				'users' => [
					[
						"nick" => "日暮途远丶",
						"country" => "中国",
						"follow_time" => 1503647237,
						"is_follow" => true,
						"province" => "北京",
						"city" => "",
						"user_id" => 5305907746,
						"weixin_open_id" => "oj3YZwN94DnNQ1K8KfsvlRnq9Wm4",
						"sex" => "m",
						"avatar" => "http://thirdwx.qlogo.cn/mmopen/PiajxSqBRaEKbQc8vO0yMapQLVxRMmgvaOFhQibPECyZy7G9IpkxwibnTNY2NYWakmgTYReaQKOPbib8JqFNvgaydA/132"
					],
					// ...
				],
				'last_fans_id' => 5305907747,
			]
		];
		$resFailStyle = [
			'response' => [
				'has_next' => false,
				'users' => [],
				'last_fans_id' => -1,
			]
		];
		return -1;

	}


	public static function items($criteria, $params, $page = 1, $pageSize = 20)
	{
		$conn = AppUtil::db();
		$limit = 'limit ' . ($page - 1) * $pageSize . "," . $pageSize;
		$criteriaStr = '';
		if ($criteria) {
			$criteriaStr = ' and ' . implode(" and ", $criteria);
		}

		$sql = "select 
				a.aId,a.aName,
				u1.uId,u1.uYZUId,u1.uAvatar,u1.uName,u1.uPhone,u1.uFollow,u1.uCreateOn,u1.uUpdatedOn,u1.uAdminId,u1.uTradeMoney,
				u1.uTradeNum,
				u2.uAvatar as favatar,u2.uName as fname,u2.uPhone as fphone,u2.uFollow as ffollow
				from im_yz_user as u1
				left join im_yz_user as u2 on u2.uPhone=u1.uFromPhone and u2.uPhone>0
				left join im_admin as a on a.aId=u1.uAdminId 
				where u1.uType=:type $criteriaStr
				group by u1.uId
				order by u1.`uCreateOn` desc $limit";

		$res = $conn->createCommand($sql)->bindValues(array_merge([
			':type' => self::TYPE_YXS,
		], $params))->queryAll();

		$admins = Admin::getAdmins();
		foreach ($res as $k => $v) {
			$res[$k]['admin_txt'] = $admins[$v['uAdminId']] ?? '';
		}

		$sql = "select 
				count(DISTINCT u1.uId)
				from im_yz_user as u1
				left join im_yz_user as u2 on u2.uPhone=u1.uFromPhone and u2.uPhone>0
				left join im_admin as a on a.aId=u1.uAdminId
				where u1.uType=:type $criteriaStr  ";
		$count = $conn->createCommand($sql)->bindValues(array_merge([
			':type' => self::TYPE_YXS,
		], $params))->queryScalar();

		return [$res, $count];

	}

	public static function users($criteria, $params, $page = 1, $pageSize = 20)
	{
		$conn = AppUtil::db();
		$limit = 'limit ' . ($page - 1) * $pageSize . "," . $pageSize;
		$criteriaStr = '';
		if ($criteria) {
			$criteriaStr = ' and ' . implode(" and ", $criteria);
		}

		$sql = "select 
				u1.*
				from im_yz_user as u1 
				where u1.uId>0 $criteriaStr
				order by u1.`uYZUId` desc $limit";

		$res = $conn->createCommand($sql)->bindValues($params)->queryAll();

		foreach ($res as $k => $v) {
			$res[$k]['type_txt'] = self::$typeDict[$v['uType']] ?? '';
		}

		$sql = "select 
				count(DISTINCT u1.uId)
				from im_yz_user as u1
				where u1.uId>0 $criteriaStr  ";
		$count = $conn->createCommand($sql)->bindValues($params)->queryScalar();

		return [$res, $count];

	}

	public static function chain_items($criteria, $params, $se_date = [])
	{

		$res = RedisUtil::init(RedisUtil::KEY_YOUZAN_USER_CHAIN, md5(json_encode($params) . json_encode($se_date)))->getCache();
		if ($res) {
			// return json_decode($res, 1);
		}

		$conn = AppUtil::db();
		$criteriaStr = '';
		if ($criteria) {
			$criteriaStr = ' and ' . implode(" and ", $criteria);
		}

		$orderby = ' order by amt desc ';
		$criteria_o = $criteria_o2 = '';
		if ($se_date['sdate'] && $se_date['edate']) {
			$sdate = $se_date['sdate'] . ' 00:00:00';
			$edate = $se_date['edate'] . ' 23:59:59';
			$criteria_o = " and o.o_created between '$sdate' and '$edate' ";
			$criteria_o2 = " and o2.o_created between '$sdate' and '$edate' ";
			$orderby = " order by next_order_amt desc,self_order_amt desc";
		}

		$sql = "select u1.uName,u1.uPhone,u1.uYZUId,
				COUNT(DISTINCT u2.uPhone) as amt, 
				count(DISTINCT o.o_id) as self_order_amt ,
				count(DISTINCT o2.o_id) as next_order_amt 
				from im_yz_user as u1
				left join im_yz_user as u2 on u2.uFromPhone=u1.uPhone
				left join im_yz_orders as o on o.o_fans_id=u1.uYZUId $criteria_o
				left join im_yz_orders as o2 on o2.o_fans_id=u2.uYZUId  $criteria_o2
				where u1.uType=:ty  $criteriaStr  
				group by u1.uYZUId $orderby";
		$res = $conn->createCommand($sql)->bindValues(array_merge([
			':ty' => self::TYPE_YXS,
		], $params))->queryAll();

		$sql = "select sum(o.o_payment)
				from im_yz_user as u1 
				left join im_yz_orders as o on o.o_fans_id=u1.uYZUId 
				where u1.uType=:ty and (u1.uFromPhone=:phone or u1.uPhone=:phone) and o.o_id>0 $criteria_o ";
		$CMD = $conn->createCommand($sql);
		$sql3 = "select count(1) as co ,sum(o_payment) as payment
				from im_yz_user as u1 
				left join im_yz_orders as o on o.o_fans_id=u1.uYZUId
				where o.o_id>0 and o.o_saleman_mobile=:phone $criteria_o";
		$stat3CMD = $conn->createCommand($sql3);
		foreach ($res as $k => $v) {
			$res[$k]['cls'] = $v['amt'] > 0 ? 'parent_li' : '';
			$res[$k]['cls_ico'] = $v['amt'] > 0 ? 'icon-plus-sign' : '';
			$res[$k]['uname'] = mb_strlen($v['uName']) > 5 ? mb_substr($v['uName'], 0, 5) . '...' : $v['uName'];
			//$res[$k]['sum_payment'] = $CMD->bindValues([':phone' => $v['uPhone'], ':ty' => self::TYPE_YXS])->queryScalar() ?: 0;
			$res[$k]['sum_payment'] = 0;
			$res[$k]['all_order_amt'] = $stat3CMD->bindValues(array_merge([
				":phone" => $v['uPhone']
			]))->queryScalar();
		}

		// RedisUtil::init(RedisUtil::KEY_YOUZAN_USER_CHAIN, md5(json_encode($params) . json_encode($se_date)))->setCache(json_encode($res));

		return $res;
	}


	public static function get_user_chain_by_fans_id($fans_id)
	{
		$ret = [];
		$co = 0;
		$sql = 'select u2.uYZUId,u2.uName,u2.uPhone from im_yz_user as u1
				left join  im_yz_user as u2 on u2.uPhone=u1.uFromPhone 
				where u1.uYZUId=:fans_id and u1.uFromPhone>1 ';
		$cmd = AppUtil::db()->createCommand($sql);

		do {
			$res = $cmd->bindValues([':fans_id' => $fans_id])->queryOne();
			if ($res) {
				$fans_id = $res['uYZUId'];
				$uPhone = $res['uPhone'];
				$ret[] = [
					'fans_id' => $fans_id,
					'name' => $res['uName'],
					'phone' => $uPhone,
				];
			} else {
				break;
			}
			$co++;
		} while ($fans_id && $co < 10);

		return $ret;
	}

	public static function peak_yxs()
	{
		$sql = 'select uYZUId,uName,uPhone from im_yz_user where uType=:ty and uFromPhone<100 order by uPhone desc';
		$res = AppUtil::db()->createCommand($sql)->bindValues([
			':ty' => self::TYPE_YXS,
		])->queryAll();
		return $res;
	}

	/**
	 * 计算所有下级数
	 */
	public static function cal_all_next($phone, $conn)
	{
		static $count;
		if (!$conn) {
			$conn = AppUtil::db();
		}
		$sql = 'select count(*) from where uFromPhone=:phone ';
		$count = $count + $conn->createCommand($sql)->bindValues([':phone' => $phone])->queryScalar();

		$sql = 'select uPhone from where uFromPhone=:phone ';
		$res = $conn->createCommand($sql)->bindValues([':phone' => $phone])->queryAll();
		if ($res) {
			foreach ($res as $next_from_phone) {
				self::cal_all_next($next_from_phone, $conn);
			}
		}

		return $count;

	}


}