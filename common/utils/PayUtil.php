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
	 * @param int $amt 金额，单位分
	 * @param \yii\db\connection $conn
	 * @return array [code（大于0表示失败）, 系统提醒消息]
	 */
	public static function withdraw($openId, $amt, $conn = null)
	{
		if ($amt < 100) {
			return [129, '提现金额不能小于1元'];
		}
		if (!$conn) {
			$conn = AppUtil::db();
		}
		AppUtil::logFile([$openId, $amt], 5, __FUNCTION__, __LINE__);
		$appId = (strpos($openId, 'oYDJe') === 0) ? \WxPayConfig::APPID : \WxPayConfig::X_APPID;
		$sql = 'SELECT wUId,wNickName FROM im_user_wechat WHERE wOpenId=:id or wXcxId=:id';
		$row = $conn->createCommand($sql)->bindValues([':id' => $openId])->queryOne();
		if (!$row) {
			return [129, '提现失败！用户不存在'];
		}
		$nickname = $row['wNickName'];
		$uId = $row['wUId'];
		$balance = RedpacketTrans::balance($uId, $conn);
		if ($balance < $amt) {
			return [129, '提现失败！余额不足'];
		}

		$trade_no = RedisUtil::getIntSeq();
		$sql = 'INSERT INTO im_redpacket_trans(tPId,tCategory,tStatus,tAmt,tUId)
				SELECT :tPId,:tCategory,0,:tAmt,:tUId FROM dual';
		$conn->createCommand($sql)->bindValues([
			':tPId' => $trade_no,
			':tCategory' => RedpacketTrans::CAT_WITHDRAW,
			':tAmt' => $amt,
			':tUId' => $uId,
		])->execute();

		$postData = [
			'mch_appid' => $appId,
			'mchid' => \WxPayConfig::MCHID,
			'nonce_str' => self::nonceStr(),
			'partner_trade_no' => $trade_no,
			'openid' => $openId,
			'check_name' => 'NO_CHECK',
			're_user_name' => $nickname,
			'amount' => $amt,
			'desc' => self::DESC_WITHDRAW,
			'spbill_create_ip' => AppUtil::IP(),
		];
		$sign = self::makeSign($postData);
		$postData['sign'] = $sign;
		$postData = AppUtil::data_to_xml($postData);

		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
		$ret = self::post($url, $postData);
		if ($ret) {
			$ret = AppUtil::xml_to_data($ret);
			AppUtil::logFile($ret, 5, __FUNCTION__, __LINE__);
			if (isset($ret['result_code']) && $ret['result_code'] == 'SUCCESS') {
				$payment_no = $ret['payment_no'];
				$partner_trade_no = $ret['partner_trade_no'];
				$sql = 'update im_redpacket_trans set tPayNo=:tPayNo,tPayRaw=:tPayRaw,tStatus=1 WHERE tPId=:tPId';
				$conn->createCommand($sql)->bindValues([
					':tPId' => $partner_trade_no,
					':tPayNo' => $payment_no,
					':tPayRaw' => json_encode($ret, JSON_UNESCAPED_UNICODE),
				])->execute();
				return [0, '提现成功！请查收'];
			}
		}
		return [0, '将在1~5个工作日内转入你的零钱包'];
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
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		//以下两种方式需选择一种

		//第一种方法，cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLCERT, AppUtil::rootDir() . '../imei_cert/apiclient_cert.pem');
		//默认格式为PEM，可以注释
		curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLKEY, AppUtil::rootDir() . '../imei_cert/apiclient_key.pem');

		curl_setopt($ch, CURLOPT_CAINFO, AppUtil::rootDir() . '../imei_cert/rootca.pem');

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


	/**
	 * 现金提现，直接进入微信零钱
	 * @param string $openId 用户的公众号openid
	 * @param int $amt 金额，单位分
	 * @param \yii\db\connection $conn
	 * @return array [code（大于0表示失败）, 系统提醒消息]
	 */
	public static function withDrawForS28($openId, $amt, $conn = null)
	{
		if ($amt < 100) {
			return [129, '提现金额不能小于1元'];
		}
		if (!$conn) {
			$conn = AppUtil::db();
		}
		AppUtil::logFile([$openId, $amt], 5, __FUNCTION__, __LINE__);
		//$appId = (strpos($openId, 'oYDJe') === 0) ? \WxPayConfig::APPID : \WxPayConfig::X_APPID;
		$appId = \WxPayConfig::APPID;
		//$sql = 'SELECT wUId,wNickName FROM im_user_wechat WHERE wOpenId=:id or wXcxId=:id';
		$sql = 'SELECT wUId,wNickName FROM im_user_wechat WHERE wOpenId=:id ';
		$row = $conn->createCommand($sql)->bindValues([':id' => $openId])->queryOne();
		if (!$row) {
			return [129, '提现失败！用户不存在'];
		}
		$nickname = $row['wNickName'];
		$uId = $row['wUId'];
		//$balance = RedpacketTrans::balance($uId, $conn);
		$balance = 2018;
		if ($balance < $amt) {
			return [129, '提现失败！余额不足'];
		}

		$trade_no = RedisUtil::getIntSeq();
/*		$sql = 'INSERT INTO im_redpacket_trans(tPId,tCategory,tStatus,tAmt,tUId)
				SELECT :tPId,:tCategory,0,:tAmt,:tUId FROM dual';
		$conn->createCommand($sql)->bindValues([
			':tPId' => $trade_no,
			':tCategory' => RedpacketTrans::CAT_WITHDRAW,
			':tAmt' => $amt,
			':tUId' => $uId,
		])->execute();*/

		$postData = [
			'mch_appid' => $appId,
			'mchid' => \WxPayConfig::MCHID,
			'nonce_str' => self::nonceStr(),
			'partner_trade_no' => $trade_no,
			'openid' => $openId,
			'check_name' => 'NO_CHECK',
			're_user_name' => $nickname,
			//'amount' => $amt,
			'amount' => 1,
			'desc' => self::DESC_WITHDRAW,
			'spbill_create_ip' => AppUtil::IP(),
		];
		$sign = self::makeSign($postData);
		$postData['sign'] = $sign;
		$postData = AppUtil::data_to_xml($postData);

		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
		$ret = self::post($url, $postData);
		if ($ret) {
			$ret = AppUtil::xml_to_data($ret);
			AppUtil::logFile($ret, 5, __FUNCTION__, __LINE__);
			if (isset($ret['result_code']) && $ret['result_code'] == 'SUCCESS') {
				$payment_no = $ret['payment_no'];
				$partner_trade_no = $ret['partner_trade_no'];
				/*$sql = 'update im_redpacket_trans set tPayNo=:tPayNo,tPayRaw=:tPayRaw,tStatus=1 WHERE tPId=:tPId';
				$conn->createCommand($sql)->bindValues([
					':tPId' => $partner_trade_no,
					':tPayNo' => $payment_no,
					':tPayRaw' => json_encode($ret, JSON_UNESCAPED_UNICODE),
				])->execute();*/
				return [0, '提现成功！请查收'];
			}
		}
		return [0, '将在1~5个工作日内转入你的零钱包'];
	}
}