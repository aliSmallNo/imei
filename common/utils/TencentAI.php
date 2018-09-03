<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 8/9/2017
 * Time: 2:59 PM
 */

namespace common\utils;


class TencentAI
{

	const APPKEY = 'tosQ0vMuyCYOtHn3';
	const APPID = '2108179267';

	static $error_map = [
		9 => 'qps超过限制	,用户认证升级或者降低调用频率',
		4096 => '参数非法	请检查请求参数是否符合要求',
		12289 => '应用不存在、请检查app_id是否有效的应用标识（AppId）',
		12801 => '素材不存在、请检查app_id对应的素材模版id',
		12802 => '素材ID与应用ID不匹配、请检查app_id对应的素材模版id',
		16385 => '缺少app_id参数、请检查请求中是否包含有效的app_id参数',
		16386 => '缺少time_stamp参数、请检查请求中是否包含有效的time_stamp参数',
		16387 => '缺少nonce_str参数、请检查请求中是否包含有效的nonce_str参数',
		16388 => '请求签名无效、请检查请求中的签名信息（sign）是否有效',
		16389 => '缺失API权限	、请检查应用是否勾选当前API所属接口的权限',
		16390 => 'time_stamp参数无效、请检查time_stamp距离当前时间是否超过5分钟',
		16391 => '同义词识别结果为空、请尝试更换文案',
		16392 => '专有名词识别结果为空、请尝试更换文案',
		16393 => '意图识别结果为空、请尝试更换文案',
		16394 => '闲聊返回结果为空	',
		16396 => '图片格式非法',
		16397 => '图片体积过大',
		16402 => '图片没有人脸',
		16403 => '相似度错误',
		16404 => '人脸检测失败',
		16405 => '图片解码失败',
		16406 => '特征处理失败',
		16407 => '提取轮廓错误',
		16408 => '提取性别错误',
		16409 => '提取表情错误',
		16410 => '提取年龄错误',
		16411 => '提取姿态错误',
		16412 => '提取眼镜错误',
		16413 => '提取魅力值错误',
		16414 => '语音合成失败',
		16415 => '图片为空',
		16416 => '个体已存在',
		16417 => '个体不存在',
		16418 => '人脸不存在',
		16419 => '分组不存在',
		16420 => '分组列表不存在',
		16421 => '人脸个数超过限制',
		16422 => '个体个数超过限制',
		16423 => '组个数超过限制',
		16424 => '对个体添加了几乎相同的人脸',
		16425 => '无效的图片格式',
		16426 => '图片模糊度检测失败',
		16427 => '美食图片检测失败',
		16428 => '提取图像指纹失败',
		16429 => '图像特征比对失败',
		16430 => 'OCR照片为空',
		16431 => 'OCR识别失败',
		16432 => '输入图片不是身份证',
		16433 => '名片无足够文本',
		16434 => '名片文本行倾斜角度太大',
		16435 => '名片模糊',
		16436 => '名片姓名识别失败',
		16437 => '名片电话识别失败',
		16438 => '图像为非名片图像',
		16439 => '检测或者识别失败',
		16440 => '未检测到身份证',
		16441 => '请使用第二代身份证件进行扫描',
		16442 => '不是身份证正面照片',
		16443 => '不是身份证反面照片',
		16444 => '证件图片模糊',
		16445 => '请避开灯光直射在证件表面',
		16446 => '行驾驶证OCR识别失败',
		16447 => '通用OCR识别失败',
		16448 => '银行卡OCR预处理错误',
		16449 => '银行卡OCR识别失败',
		16450 => '营业执照OCR预处理失败',
		16451 => '营业执照OCR识别失败',
		16452 => '意图识别超时',
		16453 => '闲聊处理超时',
		16454 => '语音识别解码失败',
		16455 => '语音过长或空',
		16456 => '翻译引擎失败',
		16457 => '不支持的翻译类型',
		16460 => '输入图片与识别场景不匹配	请检查场景参数是否正确，所传图片与场景是否匹配',
		16461 => '识别结果为空	当前图片无法匹配已收录的标签，请尝试更换图片',
		16462 => '多人脸检测识别结果为空	图片中识别不出人脸，请尝试更换图片',
		16467 => '跨年龄人脸识别结果为空	源图片与目标图片中识别不出匹配的人脸，请尝试更换图片',
	];

	const ERR_TYPE_OK = 0;
	const ERR_TYPE_SYS = 1;
	const ERR_TYPE_BUSINESS = 2;

	static $error_type = [
		0 => '表示处理成功',
		1 => '表示系统出错，例如网络超时；一般情况下需要发出告警，共同定位问题原因',
		2 => '表示业务出错，例如调用者传递非法参数；不同业务错误有单独的返回码定义',
	];

	public static function ckeck_error_type($error_code)
	{
		$error_code = intval($error_code);
		switch ($error_code) {
			case $error_code > 0:
				return self::$error_type[self::ERR_TYPE_BUSINESS];
				break;
			case $error_code < 0:
				return self::$error_type[self::ERR_TYPE_SYS];
				break;
			default:
				return self::$error_type[self::ERR_TYPE_OK];
		}
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

	/**
	 * @param $params 完整接口请求参数（特别注意：不同的接口，参数对一般不一样，请以具体接口要求为准）
	 * @return bool|mixed 返回false表示失败，否则表示API成功返回的HTTP BODY部分
	 */
	public static function doHttpPost($params, $url)
	{
		$curl = curl_init();

		$response = false;
		do {
			// 1. 设置HTTP URL (API地址)
			curl_setopt($curl, CURLOPT_URL, $url);

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
		return self::_respone($response);
	}

	public static function _respone($response)
	{
		echo 'response:' . $response . PHP_EOL;
		if (!$response) {
			return '系统错误';
		}
		$response = AppUtil::json_decode($response);
		/*
		 $responseStyle = [
			"ret" => 0,
			"msg" => 'msg',
			'data' => []
		];
		*/

		if (is_array($response)
			&& array_key_exists('ret', $response)
			&& $response['ret'] == 0) {
			return $response['data'];
		} elseif (is_array($response)
			&& array_key_exists('ret', $response)
			&& $response['ret'] > 0) {
			return $response['msg'];
		} else {
			return '系统错误~';
		}
	}

	/**
	 * 自动回复
	 * @param string $msg
	 * @return bool|mixed
	 */
	public static function auto_reply($msg = '你叫啥')
	{
		// 文档: https://ai.qq.com/doc/nlpchat.shtml
		// 设置请求数据
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
		$url = 'https://api.ai.qq.com/fcgi-bin/nlp/nlp_textchat';
		$response = self::doHttpPost($params, $url);

		if (is_string($response)) {
			return $response;
		}

		return $response['answer'];
		// error   ['ret'=>16389,'msg'=>'no auto','data'=>['session'=>'','answer'=>'']]
		// sucess  ['ret'=>0,'msg'=>'ok','data'=>['session'=>'1104821','answer'=>'我叫千寻小妹，你觉得这个名字怎么样？']]

	}

	/**
	 * 身份证正面信息识别
	 * @param string $path 路片路径
	 * @return bool|mixed
	 */
	public static function ID_identify($path = '')
	{
		// 文档: https://ai.qq.com/doc/ocridcardocr.shtml
		// 设置请求数据
		// 图片base64编码
		$data = file_get_contents('/Users/b_tt/Downloads/ID_card/lzp_z.jpg');
		$base64 = base64_encode($data);

		// 设置请求数据
		$params = array(
			'app_id' => self::APPID,
			'image' => $base64,
			'card_type' => '0',
			'time_stamp' => strval(time()),
			'nonce_str' => strval(rand()),
			'sign' => '',
		);
		$params['sign'] = self::getReqSign($params);

		$url = 'https://api.ai.qq.com/fcgi-bin/ocr/ocr_idcardocr';
		// 执行API调用
		$response = self::doHttpPost($params, $url);

		if (is_string($response)) {
			return $response;
		}
		unset($response['frontimage']);
		return $response;
//{
//"ret": 16415,
//"msg": "image empty",
//"data": {
//	"name": "",
//	"sex": "",
//	"nation": "",
//	"birth": "",
//	"address": "",
//	"id": "",
//	"frontimage": "",
//	"authority": "",
//	"backimage": ""
//	"valid_date": "",
//}
	}

	public static function face_analysis($path = '')
	{
		// 文档 https://ai.qq.com/doc/detectface.shtml
		// 图片base64编码
		$path = '/Users/b_tt/Downloads/ID_card/ID_z.jpg';

		$data = file_get_contents($path);
		$base64 = base64_encode($data);

		// 设置请求数据
		$params = array(
			'app_id' => self::APPID,
			'image' => $base64,
			'mode' => '0',
			'time_stamp' => strval(time()),
			'nonce_str' => strval(rand()),
			'sign' => '',
		);
		$params['sign'] = self::getReqSign($params);

		// 执行API调用
		$url = 'https://api.ai.qq.com/fcgi-bin/face/face_detectface';
		$response = self::doHttpPost($params, $url);

		echo AppUtil::json_encode($response);
	}


	// 语音压缩格式编码
	const VOILD_FORMAT_PCM = 1;
	const VOILD_FORMAT_WAV = 2;
	const VOILD_FORMAT_AMR = 3;
	const VOILD_FORMAT_SILK = 4;
	// 格式编码 格式名称
	static $voild_format_dict = [
		self::VOILD_FORMAT_PCM => 'PCM',
		self::VOILD_FORMAT_WAV => 'WAV',
		self::VOILD_FORMAT_AMR => 'AMR',
		self::VOILD_FORMAT_SILK => 'SILK',
	];

	// 语音采样率编码
	const VOILD_RATE_8 = 8000;
	const VOILD_RATE_16 = 16000;
	// 采样率 编码
	static $voild_rate_dict = [
		self::VOILD_RATE_8 => '8KHz',
		self::VOILD_RATE_16 => '16KHz',
	];

	public static function voild_to_word()
	{
		// 语音base64编码
		$path = "/data/res/imei/voice/2017/99/131040.slk";
		$fomat = self::get_voild_format($path);

		$data = file_get_contents($path);
		$base64 = base64_encode($data);

		// 设置请求数据
		$params = array(
			'app_id' => self::APPID,
			'format' => $fomat,
			'rate' => self::VOILD_RATE_16,
			'speech' => $base64,
			'time_stamp' => strval(time()),
			'nonce_str' => strval(rand()),
			'sign' => '',
		);
		$params['sign'] = self::getReqSign($params);

		// 执行API调用
		$url = 'https://api.ai.qq.com/fcgi-bin/aai/aai_asr';
		$response = self::doHttpPost($params, $url);
		//var_dump($response);
	}

	public static function get_voild_format($path)
	{
		$format = self::VOILD_FORMAT_AMR;

		$ext = pathinfo($path, PATHINFO_EXTENSION);
		$ext = strtolower($ext);

		switch ($ext) {
			case "pcm":
				$format = self::VOILD_FORMAT_PCM;
				break;
			case "amr":
				$format = self::VOILD_FORMAT_AMR;
				break;
			case "wav":
				$format = self::VOILD_FORMAT_WAV;
				break;
			case "slk":
				$format = self::VOILD_FORMAT_SILK;
				break;
		}

		return $format;
	}

}