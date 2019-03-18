<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 24/5/2017
 * Time: 10:58 AM
 */

namespace common\utils;

class ResponseUtil
{
	const VERSION = '1.0.1';

	const CODE_SUCCESS = 0;
	const CODE_CONFIRM = 2;
	static $MsgDict = [
		self::CODE_SUCCESS => 'success',
		self::CODE_CONFIRM => '是否确定要继续？',
		108 => '系统错误',
		109 => '参数不足',
		110 => '参数错误',
		113 => '登录信息验证失败，请重新登录',
		129 => '操作无效',
		159 => '',
	];

	public static function renderAPI($code, $msg = "", $data = [])
	{
		$result = ['code' => $code, 'msg' => $msg ? $msg : self::$MsgDict[$code]];
		if ($data) {
			$result["data"] = $data;
		}
		$result["time"] = time();
		$result["ver"] = self::VERSION;
		return $result;
	}


}