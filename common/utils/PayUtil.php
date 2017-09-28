<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 28/9/2017
 * Time: 2:22 PM
 */

namespace common\utils;

use common\models\RedpacketTrans;

require_once __DIR__ . '/../lib/WxPay/WxPay.Config.php';
require_once __DIR__ . '/../lib/WxPay/WxPay.Api.php';

class PayUtil
{

	const DESC_WITHDRAW = '趣红包-现金提现';

	/**
	 * 现金提现，直接进入微信零钱
	 * @param string $openId 用户的公众号openid 或者 用户的小程序openid
	 * @param string $tradeNo 流水号，应该是 im_user_trans 里的唯一ID
	 * @param string $nickname 用户的昵称
	 * @param int $amt 金额，单位分
	 * @return bool
	 */
	public static function withdraw($openId, $tradeNo, $nickname, $amt)
	{
		$appId = \WxPayConfig::X_APPID;
		if (strpos($openId, 'oYDJe') === 0) {
			$appId = \WxPayConfig::APPID;
		}
		$postData = [
			'mch_appid' => $appId,
			'mchid' => \WxPayConfig::MCHID,
			'nonce_str' => self::nonceStr(),
			'partner_trade_no' => $tradeNo,
			'openid' => $openId,
			'check_name' => 'NO_CHECK',
			're_user_name' => $nickname,
			'amount' => $amt,
			'desc' => self::DESC_WITHDRAW,
			'spbill_create_ip' => '139.199.31.56',
		];
		$sign = self::makeSign($postData);
		$postData['sign'] = $sign;
		$postData = AppUtil::data_to_xml($postData);

		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
		$ret = self::post($url, $postData);
		if ($ret) {
			$ret = AppUtil::xml_to_data($ret);
			AppUtil::logFile($ret, 5, __FUNCTION__, __LINE__);
			if (isset($ret['return_code']) && $ret['return_code'] == 'SUCCESS') {
				$payment_no = $ret['payment_no'];
				$partner_trade_no = $ret['partner_trade_no'];
				RedpacketTrans::replace([
					'tPId' => $partner_trade_no,
					'tCategory' => RedpacketTrans::CAT_WITHDRAW,
					'tPayNo' => $payment_no,
					'tPayRaw' => $ret,
					'tStatus' => 1,
				]);
				return true;
			}
		}
		return false;
	}

	protected static function post($url, $vars, $second = 30, $aHeader = [])
	{
		$ch = curl_init();
		//超时时间
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		//以下两种方式需选择一种

		//第一种方法，cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLCERT, AppUtil::rootDir() . '../imei_cert/apiclient_cert.pem');
		//默认格式为PEM，可以注释
		curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLKEY, AppUtil::rootDir() . '../imei_cert/apiclient_key.pem');

		//第二种方式，两个文件合成一个.pem文件
		//curl_setopt($ch, CURLOPT_SSLCERT, getcwd() . '/all.pem');

		if (count($aHeader) >= 1) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
		}

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		$data = curl_exec($ch);
		if ($data) {
			curl_close($ch);
			return $data;
		} else {
			$error = curl_errno($ch);
			echo "call faild, errorCode:$error\n";
			curl_close($ch);
			return false;
		}
	}

	public static function makeSign($values)
	{
		//签名步骤一：按字典序排序参数
		ksort($values);
		$string = self::urlParams($values);
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=" . \WxPayConfig::KEY;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}

	public static function urlParams($values)
	{
		$buff = "";
		foreach ($values as $k => $v) {
			if ($k != "sign" && $v && !is_array($v)) {
				$buff .= $k . "=" . $v . "&";
			}
		}
		$buff = trim($buff, "&");
		return $buff;
	}

	public static function nonceStr()
	{
		return \WxPayApi::getNonceStr();
	}

}