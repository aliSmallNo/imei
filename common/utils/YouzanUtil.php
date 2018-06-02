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


	public static function init($array)
	{
		$util = new self();
		$util->api = $array['api'] ?? '';
		$util->params = $array['params'] ?? [];
		return $util;
	}

	/**
	 * @return mixed
	 */
	private static function getToken()
	{
		$token = new YZGetTokenClient(self::CLIENT_ID, self::CLIENT_SECRET);
		$keys = [
			"kdt_id" => self::APPId,
		];
		$ret = $token->get_token(self::TYPE, $keys);
		return $ret['access_token'] ?? '';
		$ret = [
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

	}

	public static function getAccessToken($reset = false)
	{
		$accessToken = RedisUtil::init(RedisUtil::KEY_YOUZAN_TOKEN)->getCache();
		if ($accessToken || !$reset) {
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

	// SignDemo
	private function sign()
	{
		$method = 'youzan.item.get';//要调用的api名称
		$my_params = [
			'item_id' => '888888',
		];
		$client = new YZSignClient(self::APPID, self::APPSECRET);
		$ret = $client->post($method, self::API_VERSION, $my_params);
		return $ret;
	}

	public function token()
	{
		$token = self::getToken(self::TYPE);//请填入商家授权后获取的access_token
		$client = new YZTokenClient($token);

		$method = 'youzan.item.get';//要调用的api名称

		$my_params = [
			'item_id' => '888888',
		];

		$client->post($method, self::API_VERSION, $my_params);
	}


}