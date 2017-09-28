<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 28/9/2017
 * Time: 2:22 PM
 */

namespace common\utils;

require_once __DIR__ . '/../lib/WxPay/WxPay.Config.php';
require_once __DIR__ . '/../lib/WxPay/WxPay.Api.php';
class PayUtil
{

	const DESC_WITHDRAW = '趣红包-现金提现';

	/**
	 * 提现
	 * @param string $openId
	 * @param string $tradeNo
	 * @param string $nickname
	 * @param int $amt
	 * @return bool
	 */
	public static function withdraw($openId, $tradeNo, $nickname, $amt)
	{
		/*
		 * <xml>
		<mch_appid>wxe062425f740c30d8</mch_appid>
		<mchid>10000098</mchid>
		<nonce_str>3PG2J4ILTKCH16CQ2502SI8ZNMTM67VS</nonce_str>
		<partner_trade_no>100000982014120919616</partner_trade_no>
		<openid>ohO4Gt7wVPxIT1A9GjFaMYMiZY1s</openid>
		<check_name>FORCE_CHECK</check_name>
		<re_user_name>张三</re_user_name>
		<amount>100</amount>
		<desc>节日快乐!</desc>
		<spbill_create_ip>10.2.3.10</spbill_create_ip>
		<sign>C97BDBACF37622775366F38B629F45E3</sign>
		</xml>
		 * */
		$postData = [
			'mch_appid' => \WxPayConfig::APPID,
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
		AppUtil::logFile($ret, 5, __FUNCTION__, __LINE__);
		return true;
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