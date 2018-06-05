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
		$st = '2018-03-27 13:36:58';
		$et = '2018-06-05 23:59:59';
		$page = 1;
		$page_size = 20;
		$days = floor((strtotime($et) - strtotime($st)) / 86400);

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


}