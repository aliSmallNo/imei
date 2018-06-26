<?php
/**
 * Created by PhpStorm.
 * User: b_tt
 * Date: 18/6/2
 * Time: 10:55
 */

namespace common\utils;

require_once __DIR__ . "/../lib/youzan/lib/YZSignClient.php";
require_once __DIR__ . "/../lib/youzan/lib/YZGetTokenClient.php";
require_once __DIR__ . '/../lib/youzan/lib/YZTokenClient.php';

use YZSignClient;
use YZGetTokenClient;
use YZTokenClient;

class YouzanUtil
{
	//请填入你有赞店铺ID
	const APPId = 40552639;
	//要调用的api版本号
	const API_VERSION = '3.0.0';
	// 自用型应用
	const TYPE = 'self';
	//请填入有赞云控制台的应用client_id
	const CLIENT_ID = "5df54ce4e9d639f106";
	//请填入有赞云控制台的应用client_secret
	const  CLIENT_SECRET = "f7611f5fb8b49ed45338b39f07266fe6";

	//请填入开发者后台所填写的回调地址，本示例中回调地址应指向本文件。
	//public $redirect_url = "https://admin.meipo100.com/";

	const LOG_YOUZAN_USER = 'youzan_user';
	const LOG_YOUZAN_GOODS = 'youzan_goods';
	const LOG_YOUZAN_ORDERS = 'youzan_orders';
	const LOG_YOUZAN_ORDERS_UP_PHONE = 'youzan_orders_up_phone';
	const LOG_YOUZAN_REFUND = 'youzan_refund';

	/**
	 * @return mixed
	 * https://www.youzanyun.com/docs/guide/3399/3414
	 */
	private static function getToken()
	{
		$token = new YZGetTokenClient(self::CLIENT_ID, self::CLIENT_SECRET);
		$keys = [
			"kdt_id" => self::APPId,
		];
		$ret = $token->get_token(self::TYPE, $keys);

		$retStyle = [
			// 成功返回
			'success' => [
				'access_token' => '91e22b82a76e390ca66474a46002d729',
				'expires_in' => '604800',
				'scope' => 'storage points reviews multi_store salesman pay_qrcode item user trade_advanced trade item_category logistics coupon_advanced shop coupon crm_advanced trade_virtual retail_goods',
			],
			// 失败返回
			'error' => [
				'error_description' => '缺少参数kdt_id',
				'error' => 41000,
			]
		];

		return $ret['access_token'] ?? '';
	}

	public static function getAccessToken($reset = false)
	{
		$accessToken = RedisUtil::init(RedisUtil::KEY_YOUZAN_TOKEN)->getCache();
		if ($accessToken && !$reset) {
			return $accessToken;
		}
		$accessToken = "";
		for ($i = 0; $i < 3; $i++) {
			$accessToken = self::getToken();
			if ($accessToken) {
				break;
			}
		}
		RedisUtil::init(RedisUtil::KEY_YOUZAN_TOKEN)->setCache($accessToken);
		return $accessToken;
	}

	public static function getData($method, $params, $api_version = self::API_VERSION)
	{
		$client = new YZTokenClient(self::getAccessToken());
		$my_files = [];
		return $client->post($method, $api_version, $params, $my_files);
	}

	public static function cal_se_date($st = '', $et = '')
	{
		$st = $st ? $st : date('Y-m-d 00:00:00');
		$et = $et ? $et : date('Y-m-d 23:23:59');
		$days = ceil((strtotime($et) - strtotime($st)) / 86400);

		for ($d = 0; $d < $days; $d++) {
			$res[] = [
				'stimeFmt' => date('Y-m-d H:i:s', strtotime($st) + $d * 86400),
				'etimeFmt' => date('Y-m-d H:i:s', strtotime($st) + ($d + 1) * 86400 - 1),
				'stime' => (strtotime($st) + $d * 86400) * 1000,
				'etime' => (strtotime($st) + ($d + 1) * 86400 - 1) * 1000,
			];
		}
		return $res;
	}


}