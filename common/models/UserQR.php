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
use common\utils\RedisUtil;
use common\utils\WechatUtil;
use Gregwar\Image\Image;
use yii\db\ActiveRecord;

class UserQR extends ActiveRecord
{
	const CATEGORY_SALES = 10; //Rain: 销售推广
	const CATEGORY_SINGLE = 20; //Rain: 拉单身汉
	const CATEGORY_MATCH = 30; //Rain: 拉媒婆
	const CATEGORY_MARRY = 100; //Rain: 婚礼请帖

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
		$newItem->qDate = date('Y-m-d H:i:s');
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

	public static function getQRCode($uid, $category, $avatar = '')
	{
		$md5 = md5(json_encode([$uid, $category, $avatar], JSON_UNESCAPED_UNICODE));
		$qrInfo = self::findOne(['qUId' => $uid, 'qCategory' => $category, 'qMD5' => $md5]);
		if ($qrInfo && isset($qrInfo['qUrl']) && $qrInfo['qUrl']) {
			return $qrInfo['qUrl'];
		}
		return self::createQR($uid, $category, '');
	}

	public static function createQR($uid, $category, $code = '', $bottomTitle = '微信扫一扫 关注微媒100')
	{
		if (AppUtil::isDev()) {
			return '/images/qrmeipo100.jpg';
		}
		$accessUrl = '';
		$info = User::findOne(['uId' => $uid]);
		if (!$info) {
			return $accessUrl;
		}
		$thumb = $info['uThumb'];
		$md5 = md5(json_encode([$uid, $category, $thumb], JSON_UNESCAPED_UNICODE));
		switch ($category) {
			case self::CATEGORY_SALES:
				if (!$code) {
					$code = 'meipo100';
				}
				if (strpos($code, 'meipo100') === false) {
					$code = 'meipo100-' . $code;
				}
				$code = strtolower($code);
				$qid = self::edit($info['uOpenId'], $category, $code, [
					'qTitle' => $bottomTitle,
					'qSubTitle' => $code,
					'qUId' => $uid,
					'qMD5' => $md5
				]);

				list($accessUrl, $originUrl) = self::makeQR($qid, 'qr' . $code, $code, $bottomTitle, $thumb);
				if ($accessUrl) {
					self::edit($info['uOpenId'], $category, $code, [
						'qUrl' => $accessUrl,
						'qRaw' => $originUrl,
					]);
				}
				break;
			case self::CATEGORY_SINGLE:
			case self::CATEGORY_MATCH:
				$qid = self::edit($info['uOpenId'], $category, $code, [
					'qTitle' => $bottomTitle,
					'qSubTitle' => $code,
					'qUId' => $uid,
					'qMD5' => $md5
				]);

				list($accessUrl, $originUrl) = self::makeQR($qid, 'qr' . $qid, $code, $bottomTitle, $thumb);
				if ($accessUrl) {
					self::edit($info['uOpenId'], $category, $code, [
						'qUrl' => $accessUrl,
						'qRaw' => $originUrl,
					]);
				}
				break;
		}
		return $accessUrl;
	}

	protected static function makeQR($qid, $qrName = '', $topTitle = '', $bottomTitle = '', $mergeFile = '')
	{
		if (!$qrName) {
			$qrName = 'qr' . $qid;
		}
		$access_token = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $access_token;
		$jsonData = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": ' . $qid . '}}}';
		$ret = AppUtil::postJSON($url, $jsonData);
		$ret = json_decode($ret, 1);
		if ($ret && isset($ret["ticket"])) {
			$originUrl = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . urlencode($ret["ticket"]);
			$saveAs = AppUtil::imgDir() . $qrName;
			$saveAs = self::downloadFile($originUrl, $saveAs);
			list($width, $height, $type) = getimagesize($saveAs);
			$rootFolder = AppUtil::rootDir();
			$fontPath = $rootFolder . 'common/assets/bmcheiti.ttf';
			$mergeSize = intval($width / 6.0);
			if (!$mergeFile) {
				$mergeFile = $rootFolder . 'common/assets/logo180.jpg';
			}
			if (strpos($mergeFile, 'http') === 0) {
				$mergeFile = self::downloadFile($mergeFile, AppUtil::imgDir() . 'm_' . $qrName);
			}
			$mergeImg = Image::open($mergeFile)->zoomCrop($mergeSize, $mergeSize, 0xffffff, 'left', 'top');
			$img = Image::open($saveAs)->merge($mergeImg, ($width - $mergeSize) / 2, ($height - $mergeSize) / 2, $mergeSize, $mergeSize);
			if ($bottomTitle) {
				$img->write($fontPath, $bottomTitle, $width / 2, $height - 8, 14, 0, 0x000000, 'center');
			}
			if ($topTitle) {
				$img->write($fontPath, $topTitle, $width / 2, 20, 11, 0, 0x000000, 'center');
			}
			$img->save($saveAs);
			$accessUrl = ImageUtil::getUrl($saveAs);
			unlink($mergeFile);
			return [$accessUrl, $originUrl];
		}
		return ['', ''];
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


	static $SuperStars = [
		'fanbb' => ['name' => '范冰冰', 'avatar' => 'star_fanbb.jpg'],
		'hug' => ['name' => '胡歌', 'avatar' => 'star_hug.jpg'],
		'linzl' => ['name' => '林志玲', 'avatar' => 'star_linzl.jpg'],
		'luh' => ['name' => '鹿晗', 'avatar' => 'star_luh.jpg'],
		'wuyf' => ['name' => '吴亦凡', 'avatar' => 'star_wuyf.jpg'],
		'wuyz' => ['name' => '吴彦祖', 'avatar' => 'star_wuyz.jpg'],
		'yangm' => ['name' => '杨幂', 'avatar' => 'star_yangm.jpg'],
		'zhaoly' => ['name' => '赵丽颖', 'avatar' => 'star_zhaoly.jpg'],
	];

	public static function createInvitation($uid, $starId, $h2, $h5)
	{
		$uInfo = User::findOne(['uId' => $uid]);
		if (!$uInfo) return '';
		$avatar = $uInfo->uAvatar;
		if (AppUtil::isDev()) {
			$avatar = AppUtil::rootDir() . 'mobile/assets/star_wuyz.jpg';
		}
		$nickname = $uInfo->uName;
		$gender = $uInfo->uGender;
		$rootFolder = AppUtil::rootDir();

		$star = self::$SuperStars[$starId];
		$h4 = $nickname . ' & ' . $star['name'];
		if ($gender == User::GENDER_FEMALE) {
			$h4 = $star['name'] . ' & ' . $nickname;
		}
		AppUtil::logFile($avatar, 5, __FUNCTION__, __LINE__);
		if (strpos($avatar, 'http') !== false) {
			$tmpFile = AppUtil::imgDir() . RedisUtil::getImageSeq();
			$avatar = self::downloadFile($avatar, $tmpFile);
		}
		$maskSize = 230;
		$maskFile = $rootFolder . 'mobile/assets/mask_heart.png';
		$saveAs = AppUtil::imgDir() . RedisUtil::getImageSeq() . '.png';
		$avatar = ImageUtil::clippingMask($avatar, $maskFile, $saveAs, $maskSize);
		$starAvatar = $rootFolder . 'mobile/assets/' . $star['avatar'];
		$saveAs = AppUtil::imgDir() . RedisUtil::getImageSeq() . '.png';
		$starAvatar = ImageUtil::clippingMask($starAvatar, $maskFile, $saveAs, $maskSize);

		$qrFile = UserQR::createQR($uid, UserQR::CATEGORY_SALES, 'marry');
		$raw = json_encode([$h2, $h4, $h5, $qrFile], JSON_UNESCAPED_UNICODE);
		$md5 = md5($raw);
		$qrInfo = self::findOne(['qUId' => $uid, 'qCategory' => self::CATEGORY_MARRY, 'qMD5' => $md5]);
		if ($qrInfo && !AppUtil::isDev()) {
			return $qrInfo->qUrl;
		}

		$saveAs = 'inv' . RedisUtil::getImageSeq() . '.jpg';
		$saveAs = AppUtil::imgDir() . $saveAs;

		$mergeFile = $rootFolder . 'common/assets/qr_invitation.jpeg';
		if ($qrFile && !AppUtil::isDev()) {
			$mergeFile = $qrFile;
			if (strpos($qrFile, 'http') !== false) {
				$mergeFile = ImageUtil::getFilePath($qrFile);
			}
		}
		$bgFile = $rootFolder . 'mobile/assets/bg_invitation.jpg';
		$h2Font = $rootFolder . 'common/assets/twinklestar.ttf';
		$h4Font = $h5Font = $rootFolder . 'common/assets/bmcheiti.ttf';
		list($width, $height, $type) = getimagesize($bgFile);
		$mergeSize = 210;
		$mergeImg = Image::open($mergeFile)->zoomCrop($mergeSize, $mergeSize, 0xffffff, 'left', 'top');
		$img = Image::open($bgFile)->merge($mergeImg, 15, 828, $mergeSize, $mergeSize);
		$img->merge(Image::open($starAvatar), 295, 336, $maskSize, $maskSize)
			->merge(Image::open($avatar), 115, 336, $maskSize, $maskSize);
		if ($h2) {
			$img->write($h2Font, $h2, $width / 2, 295, 62, 0, 0x6a131c, 'center');
		}
		if ($h4) {
			$img->write($h4Font, $h4, $width / 2, 605, 28, 0, 0x6a131c, 'center');
		}
		if ($h5) {
			$img->write($h5Font, $h5, $width / 2 + 65, 900, 21, 0, 0x6a131c, 'center');
		}
		$img->save($saveAs);
		$accessUrl = ImageUtil::getUrl($saveAs);
		unlink($avatar);
		unlink($starAvatar);
		self::deleteAll(['qUId' => $uid, 'qCategory' => self::CATEGORY_MARRY]);
		$entity = new self();
		$entity->qUId = $uid;
		$entity->qCategory = self::CATEGORY_MARRY;
		$entity->qCode = 'meipo100-marry';
		$entity->qMD5 = $md5;
		$entity->qRaw = $raw;
		$entity->qUrl = $accessUrl;
		$entity->save();
		return $accessUrl;
	}
}