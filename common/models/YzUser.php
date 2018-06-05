<?php
/**
 * Created by PhpStorm.
 * Time: 10:34 AM
 */

namespace common\models;


use admin\models\Admin;
use common\utils\AppUtil;
use common\utils\ImageUtil;
use common\utils\YouzanUtil;
use yii\db\ActiveRecord;

class YzUser extends ActiveRecord
{
	const ST_PENDING = 0;
	const ST_PASS = 1;
	const ST_REMOVED = 9;
	static $StatusDict = [
		self::ST_PENDING => '待审核',
		self::ST_PASS => '审核通过',
		self::ST_REMOVED => '已删除',
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
		'points' => 'uPoint',
		'level_info' => 'uLevel',
		'traded_num' => 'uTradeNum',
		'trade_money' => 'uTradeMoney',
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
		}
		foreach ($data as $k => $v) {
			$entity->$k = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
		}
		$entity->save();
		return true;
	}


	/**
	 * 获取指定时间段用户信息
	 */
	public static function getUserBySETime()
	{

		// 根据关注时间段批量查询微信粉丝用户信息
		//$st = '2018-03-27 13:36:58';
		$st = '2018-06-04 13:36:58';
		$et = '2018-06-05 23:59:59';
		$page = 1;
		$page_size = 20;
		$days = ceil((strtotime($et) - strtotime($st)) / 86400);

		for ($d = 0; $d < $days; $d++) {
			$stime = date('Y-m-d', strtotime($st) + $d * 86400);
			$etime = date('Y-m-d', strtotime($st) + ($d + 1) * 86400);

			$results = self::getTZUser($stime, $etime, $page, $page_size);
			if ($results && $results['total_results'] > 0) {
				$total_results = $results['total_results'];
				$page_count = ceil($total_results / $page_size);

				for ($i = 0; $i < $page_count; $i++) {
					$users = self::getTZUser($stime, $etime, ($i + 1), $page_size)['users'];
					foreach ($users as $v) {
						$uid = $v['user_id'];
						$insert = [];
						foreach (YzUser::$fieldMap as $key => $val) {
							if (isset($v[$key])) {
								$insert[$val] = $v[$key];
							}
						}
						$insert['uRawData'] = json_encode($v, JSON_UNESCAPED_UNICODE);
						// echo $uid;print_r($insert);exit;
						YzUser::edit($uid, $insert);
					}
				}
			}
		}
	}

	public function getTZUser($stime, $etime, $page, $page_size)
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

		AppUtil::logFile($results, 5, __FUNCTION__, __LINE__);
		echo "stime:" . $stime . ' == etime:' . $etime . ' == ' . 'page:' . $page . ' == ' . 'pagesize:' . $page_size . PHP_EOL;
		return $results;

	}

	public static function getSalesManList()
	{

		$getSales = function ($page) {
			//获取当前店铺分销员列表，需申请高级权限方可调用。
			$method = 'youzan.salesman.accounts.get';
			$params = [
				'page_no' => $page,
				'page_size' => 20,
			];
			echo 'page:' . $page . PHP_EOL;

			$res = YouzanUtil::getData($method, $params);
			if (isset($res['response'])) {
				$total_results = $res['response']['total_results'];
				if ($total_results) {
					return [$res['response']['accounts'], $total_results];
				}
			}
			return 0;
		};

		$res = $getSales(1);
		if ($res) {
			$total_results = $res[1];
			$pages = ceil($total_results / 20);
			for ($p = 1; $p <= $pages; $p++) {
				$ret = $getSales($p);
				if ($ret) {
					$ret = $ret[0];
					foreach ($ret as $k => $v) {
						$insert = [
							'uFromPhone' => $v['from_buyer_mobile'] ?? '',
							'uPhone' => $v['mobile'] ?? '',
							'uCreateOn' => $v['created_at'] ?? '',
							'uSeller' => $v['seller'] ?? '',
						];
						$fansId = $v['fans_id'];
						if (self::findOne(['uYZUId' => $fansId])) {
							self::edit($fansId, $insert);
						}
					}
				}
			}
		}

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


}