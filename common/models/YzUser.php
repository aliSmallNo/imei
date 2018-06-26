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
		if (!$data) {
			return 0;
		}
		$entity = self::findOne(['uYZUId' => $yzuid]);
		if (!$entity) {
			$entity = new self();
		} else {
			$data['uUpdatedOn'] = date('Y-m-d H:i:s');
		}
		foreach ($data as $k => $v) {
			$entity->$k = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
		}
		$entity->save();
		return true;
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

		if (isset($insert['uPhone']) && !$insert['uPhone']) {
			unset($insert['uPhone']);
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


	public static function UpdateUser($st = '', $et = '')
	{
		$st = $st ? $st : date('Y-m-d 00:00:00');
		$et = $et ? $et : date('Y-m-d 00:00:00', time() + 86400);
		self::getUserBySETime($st, $et);
	}

	/**
	 * 根据关注时间段批量查询微信粉丝用户信息（支持粉丝基础信息、积分、交易等数据查询，详见入参fields字段描述）。
	 * 注意：循环拉取
	 */
	public static function getUserBySETime($st, $et, $isDebugger = false)
	{

		// 根据关注时间段批量查询微信粉丝用户信息
		//$st = '2018-03-26 00:00:00';
		//$et = '2018-03-29 00:00:00';

		//$st = '2018-06-05 00:00:00';
		//$et = '2018-06-06 00:00:00';
		$page = 1;
		$page_size = 20;
		$days = ceil((strtotime($et) - strtotime($st)) / 86400);

		$total = 0;
		for ($d = 0; $d < $days; $d++) {
			$stime = date('Y-m-d', strtotime($st) + $d * 86400);
			$etime = date('Y-m-d', strtotime($st) + ($d + 1) * 86400);

			$results = self::getTZUser($stime, $etime, $page, $page_size, $isDebugger);

			/* 计算总共用户数 */
			$total_results = $results['total_results'] ?? 0;
			$total = $total + $total_results;
			$msg = "stime:" . $stime . ' == etime:' . $etime . ' currentNum:' . $total_results . ' Total:' . $total;
			if ($isDebugger) {
				echo $msg . PHP_EOL;
			}
			AppUtil::logByFile($msg, YouzanUtil::LOG_YOUZAN_USER, __FUNCTION__, __LINE__);

			if ($results && $results['total_results'] > 0) {
				$total_results = $results['total_results'];
				$page_count = ceil($total_results / $page_size);

				for ($i = 0; $i < $page_count; $i++) {
					$users = self::getTZUser($stime, $etime, ($i + 1), $page_size, $isDebugger)['users'];
					foreach ($users as $v) {
						self::process($v);
					}
				}
			}
		}

		// 更新分销员信息
		self::getSalesManList($isDebugger);
	}

	/**
	 * 根据关注时间段批量查询微信粉丝用户信息（支持粉丝基础信息、积分、交易等数据查询，详见入参fields字段描述）。
	 * 注意：
	 * 1. 如果接口频繁抛异常，且入参无误，请减小page_size并重试。
	 * 2.请尽量按需自定义入参“fields”字段获取数据。“fields”字段传入枚举值越多，查询数据耗费时间越长。
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
		$results = $ret['response'] ?? 0;

		$msg = "stime:" . $stime . ' == etime:' . $etime . ' == ' . 'page:' . $page . ' == ' . 'pagesize:' . $page_size;
		if ($isDebugger) {
			echo $msg . PHP_EOL;
		}

		AppUtil::logByFile($results, YouzanUtil::LOG_YOUZAN_USER, __FUNCTION__, __LINE__);
		AppUtil::logByFile($msg, YouzanUtil::LOG_YOUZAN_USER, __FUNCTION__, __LINE__);

		return $results;

	}

	public static function getSalesManList($isDebugger = false)
	{
		$getSales = function ($page, $isDebugger) {
			//获取当前店铺分销员列表，需申请高级权限方可调用。
			$method = 'youzan.salesman.accounts.get';
			$params = [
				'page_no' => $page,
				'page_size' => 20,
			];
			$msg = 'page:' . $page;
			if ($isDebugger) {
				echo $msg . PHP_EOL;
			}
			AppUtil::logByFile($msg, YouzanUtil::LOG_YOUZAN_USER, __FUNCTION__, __LINE__);

			$res = YouzanUtil::getData($method, $params);
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
							'uPhone' => $v['mobile'] ?? '',
							'uCreateOn' => $v['created_at'] ?? '',
							'uSeller' => $v['seller'] ?? '',
							'uType' => self::TYPE_YXS,
						];
						$fansId = $v['fans_id'];

						if (self::findOne(['uYZUId' => $fansId])) {
							if (isset($insert['uPhone']) && !$insert['uPhone']) {
								unset($insert['uPhone']);
							}
							// 修改
							$editCount++;
							self::edit($fansId, $insert);
						} else {
							// 添加
							$addCount++;
							$msg = '$fansId:' . $fansId;

							if ($isDebugger) {
								echo $msg . PHP_EOL;
							}
							AppUtil::logByFile('$fansId:' . $fansId, YouzanUtil::LOG_YOUZAN_USER, __FUNCTION__, __LINE__);
							self::getUserInfoByTag($fansId);
						}
					}
				}
			}
		}
		$msg = '$addCount:' . $addCount . ' == $editCount:' . $editCount;
		if ($isDebugger) {
			echo $msg . PHP_EOL;
		}

		AppUtil::logByFile($msg, YouzanUtil::LOG_YOUZAN_USER, __FUNCTION__, __LINE__);

		$resStyle = [
			'response' => [
				'accounts' => [
					[
						'seller' => '3JS1xT',
						'from_buyer_mobile' => 15206373307,
						'money' => 1.90,
						'mobile' => 15153782763,
						'nickname' => '鸿运当头',
						'created_at' => '2018-06-04 18:00:35',
						'order_num' => 1,
						'fans_id' => 5650058353,
					],
					// .....
				],
				'total_results' => 730,
			]
		];


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
		}
		AppUtil::logByFile($msg, YouzanUtil::LOG_YOUZAN_USER, __FUNCTION__, __LINE__);

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

		return false;

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
				}
				AppUtil::logByFile('fans_id:' . $fansId, YouzanUtil::LOG_YOUZAN_USER, __FUNCTION__, __LINE__);
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
				u1.*,u2.uAvatar as favatar,u2.uName as fname,u2.uPhone as fphone,u2.uFollow as ffollow
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
			return json_decode($res, 1);
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

		$sql = "select u1.uName,u1.uPhone,
				COUNT(DISTINCT u2.uPhone) as amt, 
				count(DISTINCT o.o_id) as self_order_amt ,
				count(DISTINCT o2.o_id) as next_order_amt 
				from im_yz_user as u1
				left join  im_yz_user as u2 on u2.uFromPhone=u1.uPhone
				left join im_yz_orders as o on o.o_fans_id=u1.uYZUId $criteria_o
				left join im_yz_orders as o2 on o2.o_fans_id=u2.uYZUId  $criteria_o2
				where u1.uType=:ty  $criteriaStr  
				group by u1.uYZUId $orderby ";
		$res = $conn->createCommand($sql)->bindValues(array_merge([
			':ty' => self::TYPE_YXS,
		], $params))->queryAll();
		if ($criteriaStr) {
			/*echo $conn->createCommand($sql)->bindValues(array_merge([
				':ty' => self::TYPE_YXS,
			], $params))->getRawSql();
			exit;*/
		}
		$sql = "select sum(o.o_payment)
				from im_yz_user as u1 
				left join im_yz_orders as o on o.o_fans_id=u1.uYZUId 
				where u1.uType=:ty and (u1.uFromPhone=:phone or u1.uPhone=:phone) and o.o_id>0 $criteria_o ";
		$CMD = $conn->createCommand($sql);
		foreach ($res as $k => $v) {
			$res[$k]['cls'] = $v['amt'] > 0 ? 'parent_li' : '';
			$res[$k]['cls_ico'] = $v['amt'] > 0 ? 'icon-plus-sign' : '';
			$res[$k]['uname'] = mb_strlen($v['uName']) > 5 ? mb_substr($v['uName'], 0, 5) . '...' : $v['uName'];
			$res[$k]['sum_payment'] = $CMD->bindValues([':phone' => $v['uPhone'], ':ty' => self::TYPE_YXS])->queryScalar() ?: 0;
		}

		RedisUtil::init(RedisUtil::KEY_YOUZAN_USER_CHAIN, md5(json_encode($params) . json_encode($se_date)))->setCache(json_encode($res));
		return $res;
	}


	public static function orders_by_phone($params_in, $page, $pageize = 20)
	{
		$conn = AppUtil::db();
		$limit = "limit " . ($page - 1) * $pageize . ',' . ($pageize + 1);
		switch ($params_in['flag']) {
			case "self":
				$criteriaStr = " and u1.uPhone=:phone ";
				$params[':phone'] = $params_in['phone'];
				break;
			case "next":
				$criteriaStr = " and u1.uFromPhone=:phone ";
				$params[':phone'] = $params_in['phone'];
				break;
			default:
				$criteriaStr = '';
				$params = [];
		}

		if ($params_in['sdate'] && $params_in['edate']) {
			$criteriaStr .= " and o.o_created between :sdate and :edate ";
			$params[':sdate'] = $params_in['sdate'] . ' 00:00:00';
			$params[':edate'] = $params_in['edate'] . ' 23:59:59';
		}

		$sql = "select u1.uName,u1.uPhone,u1.uFromPhone,o.*
				from im_yz_user as u1 
				left join im_yz_orders as o on o.o_fans_id=u1.uYZUId
				where u1.uType=:ty $criteriaStr and o.o_id>0 order by o_created DESC $limit";
		$res = $conn->createCommand($sql)->bindValues(array_merge([
			':ty' => self::TYPE_YXS
		], $params))->queryAll();

		foreach ($res as $k => $v) {
			$res[$k]['status_str'] = YzOrders::$typeDict[$v['o_status']] ?? '';
			$res[$k]['orders'] = json_decode($v['o_orders'], 1)[0];
			$res[$k]['_pic_path'] = $res[$k]['orders']['pic_path'];
			$res[$k]['_title'] = $res[$k]['orders']['title'];
			$res[$k]['_sku_properties_name'] = json_decode($res[$k]['orders']['sku_properties_name'], 1);

		}

		$nextpage = count($res) > $pageize ? ($page + 1) : 0;

		return [$res, $nextpage];

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


}