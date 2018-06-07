<?php
/**
 * Created by PhpStorm.
 * Time: 10:34 AM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\ImageUtil;
use common\utils\RedisUtil;
use common\utils\YouzanUtil;
use yii\db\ActiveRecord;

class YzUser extends ActiveRecord
{
	const TYPE_DEFAULT = 1;
	const TYPE_YXS = 3;
	static $StatusDict = [
		self::TYPE_DEFAULT => '普通',
		self::TYPE_YXS => '严选师',
	];

	const LOG_YOUZAN_TAG = 'youzan_user';

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
			if (isset($v[$key])) {
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
			AppUtil::logByFile($msg, self::LOG_YOUZAN_TAG, __FUNCTION__, __LINE__);

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

		AppUtil::logByFile($results, self::LOG_YOUZAN_TAG, __FUNCTION__, __LINE__);
		AppUtil::logByFile($msg, self::LOG_YOUZAN_TAG, __FUNCTION__, __LINE__);

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
			AppUtil::logByFile($msg, self::LOG_YOUZAN_TAG, __FUNCTION__, __LINE__);

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
							AppUtil::logByFile('$fansId:' . $fansId, self::LOG_YOUZAN_TAG, __FUNCTION__, __LINE__);
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

		AppUtil::logByFile($msg, self::LOG_YOUZAN_TAG, __FUNCTION__, __LINE__);

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
		AppUtil::logByFile($msg, self::LOG_YOUZAN_TAG, __FUNCTION__, __LINE__);

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
	public static function getYZUserByFansIdCycle($isDebugger = false)
	{

		$last_fansId = RedisUtil::init(RedisUtil::KEY_YOUZAN_LAST_FANSID)->getCache();

		$return_lastFansId = $last_fansId ? $last_fansId : 0;

		$co = 0;
		while ($return_lastFansId > 0) {
			$co++;
			if ($isDebugger) {
				echo 'getYZUserByFansIdCycle:' . $co . PHP_EOL;
			}
			$return_lastFansId = self::getYZUserByFansId($return_lastFansId, $isDebugger);
			if ($co > 100) {
				break;
			}
		}

		// 更新分销员信息
		//self::getSalesManList($isDebugger);
	}

	/**
	 * 根据微信粉丝Id正序批量查询微信粉丝用户信息（不受关注时间限制。支持粉丝基础信息、积分、交易等数据查询，详见入参fields字段描述）
	 * 注意：
	 * 1. 如果接口频繁抛异常，且入参无误，请减小page_size并重试。
	 * 2.请尽量按需自定义入参“fields”字段获取数据。“fields”字段传入枚举值越多，查询数据耗费时间越长。
	 */
	public static function getYZUserByFansId($last_fansId = 0, $isDebugger = false)
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
					echo 'add fans_id:' . $fansId . PHP_EOL;
				}
				AppUtil::logByFile('fans_id:' . $fansId, self::LOG_YOUZAN_TAG, __FUNCTION__, __LINE__);
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


}