<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 8/9/2017
 * Time: 2:59 PM
 */

namespace common\utils;


class AutoReplyUtil extends TencentAI
{

	static $url = 'https://api.ai.qq.com/fcgi-bin/nlp/nlp_textchat';

	public static function auto_reply($msg = '你叫啥')
	{
		// 文档: https://ai.qq.com/doc/nlpchat.shtml
		// 设置请求数据
		/*app_id	是	int	正整数	1000001	应用标识（AppId）
		time_stamp	是	int	正整数	1493468759	请求时间戳（秒级）
		nonce_str	是	string	非空且长度上限32字节	fa577ce340859f9fe	随机字符串
		sign	是	string	非空且长度固定32字节	B250148B284956EC5218D4B0503E7F8A	签名信息，详见接口鉴权
		session	是	string	UTF-8编码，非空且长度上限32字节	10000	会话标识（应用内唯一）
		question	是	string*/
		$params = array(
			'app_id' => self::APPID,
			'session' => RedisUtil::getIntSeq(),
			'question' => $msg,
			'time_stamp' => time(),
			'nonce_str' => strval(rand()),
			'sign' => '',
		);
		$params['sign'] = self::getReqSign($params);

		// 执行API调用
		$response = self::doHttpPost($params);
		return self::after_respone($response);

		/** response DATA
		 * ret    是    int    返回码； 0表示成功，非0表示出错
		 * msg    是    string    返回信息；ret非0时表示出错时错误原因
		 * data    是    object    返回数据；ret为0时有意义
		 * session    是    string    UTF-8编码，非空且长度上限32字节
		 * answer    是    string    UTF-8编码，非空
		 */
	}

	/**
	 * @param $response
	 * error   ['ret'=>16389,'msg'=>'no auto','data'=>['session'=>'','answer'=>'']]
	 * sucess  ['ret'=>0,'msg'=>'ok','data'=>['session'=>'1104821','answer'=>'我叫千寻小妹，你觉得这个名字怎么样？']]
	 * @return string
	 */
	public static function after_respone($response)
	{
		if (!$response) {
			return '系统错误';
		}
		$response = AppUtil::json_decode($response);
		// print_r($response);
		if (!is_array($response)
			|| !array_key_exists('ret', $response)) {
			return '系统错误~';
		}

		if ($response['ret'] == 0) {
			return $response['data']['answer'] ?? '~~';
		} else {
			return self::$error_map[$response['ret']] ?? '--';
		}

	}

	/**
	 * @param $params 完整接口请求参数（特别注意：不同的接口，参数对一般不一样，请以具体接口要求为准）
	 * @return bool|mixed 返回false表示失败，否则表示API成功返回的HTTP BODY部分
	 */
	public static function doHttpPost($params)
	{
		$curl = curl_init();

		$response = false;
		do {
			// 1. 设置HTTP URL (API地址)
			curl_setopt($curl, CURLOPT_URL, self::$url);

			// 2. 设置HTTP HEADER (表单POST)
			$head = array(
				'Content-Type: application/x-www-form-urlencoded'
			);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $head);

			// 3. 设置HTTP BODY (URL键值对)
			$body = http_build_query($params);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

			// 4. 调用API，获取响应结果
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_NOBODY, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($curl);
			if ($response === false) {
				$response = false;
				break;
			}

			$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if ($code != 200) {
				$response = false;
				break;
			}
		} while (0);

		curl_close($curl);
		return $response;
	}

	/**
	 * 根据 接口请求参数 和 应用密钥 计算 请求签名
	 * @param $params 接口请求参数（特别注意：不同的接口，参数对一般不一样，请以具体接口要求为准）
	 * @return string 返回签名结果
	 */
	public static function getReqSign($params)
	{
		// 1. 字典升序排序
		ksort($params);
		// 2. 拼按URL键值对
		$str = '';
		foreach ($params as $key => $value) {
			if ($value !== '') {
				$str .= $key . '=' . urlencode($value) . '&';
			}
		}
		// 3. 拼接app_key
		$str .= 'app_key=' . self::APPKEY;
		// 4. MD5运算+转换大写，得到请求签名
		$sign = strtoupper(md5($str));
		return $sign;
	}

}