<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 31/5/2017
 * Time: 2:52 PM
 */

namespace common\models;

use common\utils\AppUtil;
use common\utils\ImageUtil;
use common\utils\WechatUtil;
use Gregwar\Image\Image;
use yii\db\ActiveRecord;

class UserQR extends ActiveRecord
{
	const CATEGORY_SALES = 10; //Rain: 销售推广
	const CATEGORY_SINGLE = 20; //Rain: 单身汉
	const CATEGORY_MATCH = 30; //Rain: 媒婆

	public static function tableName()
	{
		return '{{%user_qr}}';
	}

	public static function edit($openId, $category = 10, $code, $values = [])
	{
		$newItem = self::findOne([
			"qOpenId" => $openId,
			"qCategory" => $category,
			'qCode' => $code
		]);
		if (!$newItem) {
			$newItem = new self();
			$newItem->qOpenId = $openId;
			$newItem->qCategory = $category;
			$newItem->qCode = $code;
		}
		foreach ($values as $key => $val) {
			$newItem->$key = $val;
		}
		$newItem->save();
		return $newItem->qId;
	}

	public static function getOne($openId, $category = 10)
	{
		if (!$openId) {
			return 0;
		}
		$qrInfo = self::findOne([
			"qOpenId" => $openId,
			"qCategory" => $category,
		]);
		if ($qrInfo && $qrInfo["qExpireTime"] > time() + 60 * 10) {
			return $qrInfo;
		}
		return 0;
	}

	public static function createQR($uid, $category, $code, $title = '微信扫一扫 关注微媒100')
	{
		$ret = '';
		$info = User::findOne(['uId' => $uid]);
		if (!$info) {
			return $ret;
		}
		switch ($category) {
			case self::CATEGORY_SALES:
				if (strpos($code, 'meipo100') === false) {
					$code = 'meipo100-' . $code;
				}
				$code = strtolower($code);
				$qid = self::edit($info['uOpenId'], $category, $code, [
					'qTitle' => $title,
					'qSubTitle' => $code,
					'qUId' => $uid
				]);
				$access_token = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
				$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $access_token;
				$jsonData = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": ' . $qid . '}}}';
				$ret = AppUtil::postJSON($url, $jsonData);
				$ret = json_decode($ret, 1);
				if ($ret && isset($ret["ticket"])) {
					$qUrl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . urlencode($ret["ticket"]);
					$saveAs = AppUtil::imgDir() . 'qr' . $code;
					$saveAs = self::downloadFile($qUrl, $saveAs);
					list($width, $height, $type) = getimagesize($saveAs);
					$rootFolder = AppUtil::rootDir();
					$fontPath = $rootFolder . 'common/assets/bmcheiti.ttf';
					$mergeSize = intval($width / 6.0);
					$mergeImage = $rootFolder . 'common/assets/logo180.jpg';
					$mergeImage = Image::open($mergeImage)->zoomCrop($mergeSize, $mergeSize, 0xffffff, 'left', 'top');
					Image::open($saveAs)
						->write($fontPath, $title, $width / 2, $height - 8, 14, 0, 0x000000, 'center')
						->write($fontPath, $code, $width / 2, 20, 11, 0, 0x000000, 'center')
						->merge($mergeImage, ($width - $mergeSize) / 2, ($height - $mergeSize) / 2, $mergeSize, $mergeSize)
						->save($saveAs);
					$ret = ImageUtil::getUrl($saveAs);
					self::edit($info['uOpenId'], self::CATEGORY_SALES, $code, [
						'qUrl' => $ret,
						'qRaw' => $qUrl,
					]);
				}
				break;
		}
		return $ret;
	}

	private static function downloadFile($url, $saveAs)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$file_content = curl_exec($ch);
		$httpInfo = curl_getinfo($ch);
		curl_close($ch);
		$contentType = $httpInfo["content_type"];
		$contentType = strtolower($contentType);
		$ext = AppUtil::getExtName($contentType);

		$downloaded_file = fopen($saveAs . '.' . $ext, 'w');
		fwrite($downloaded_file, $file_content);
		fclose($downloaded_file);
		return $saveAs . '.' . $ext;
	}

}