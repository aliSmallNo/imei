<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 27/9/2017
 * Time: 5:58 PM
 */

namespace common\utils;

class BaiduUtil
{
	const APP_ID = '10194489';
	const APP_KEY = 'tbWpoGGvu0wF3Hk3jOkCsw3W';
	const APP_SECRET = 'e674e27f2493c76ee391e1fb000cea1f';

	public static function token($resetFlag = false)
	{
		$token = RedisUtil::getCache(RedisUtil::KEY_BAIDU_TOKEN);
		if ($token && !$resetFlag) {
			return $token;
		}
		$url = 'https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id=%s&client_secret=%s';
		$url = sprintf($url, self::APP_KEY, self::APP_SECRET);
		$ret = AppUtil::httpGet($url);
		$ret = json_decode($ret, 1);
		if (isset($ret['access_token'])) {
			$token = $ret['access_token'];
			RedisUtil::setCache($token, RedisUtil::KEY_BAIDU_TOKEN);
			return $token;
		}
		return '';
	}

	/**
	 * @param string $voiceUrl 语音url地址
	 * @return string
	 */
	public static function postVoice($voiceUrl)
	{
		$url = 'http://vop.baidu.com/server_api';
		$token = self::token();
		$filePath = ImageUtil::getFilePath($voiceUrl);
		if (!$filePath || strpos($filePath, '.wav') === false) {
			return '';
		}
		$cuid = md5($token);
		$fileData = file_get_contents($filePath);
		$postData = [
			'format' => 'wav',
			'rate' => 8000,
			'channel' => 1,
			'cuid' => $cuid,
			'token' => $token,
			'lan' => 'zh',
			'speech' => base64_encode($fileData),
			'len' => strlen($fileData)
		];
		$ret = AppUtil::postJSON($url, json_encode($postData));
		$ret = json_decode($ret, 1);
		if (isset($ret['result']) && $ret['result']) {
			$ret = $ret['result'];
			if (is_array($ret) && $ret) {
				return trim($ret[0]);
			}
		}
		return '';
	}
}