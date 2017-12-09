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
	const CATEGORY_MATCH_SHARE = 35; //Rain: 媒婆推广
	const CATEGORY_SHARES = 36; //Rain: 媒婆推广
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

	public static function createQR($uid, $category, $code = '', $bottomTitle = '微信扫一扫 关注千寻恋恋', $logoFlag = false)
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
		if ($logoFlag) {
			$thumb = 'https://img.meipo100.com/default-meipo-sm.jpg';
		}
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

				list($accessUrl, $originUrl) = self::makeQR($qid, 'qr' . $qid, $code, $bottomTitle, $thumb);
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

	public static function shares($uid, $avatar = '')
	{
		$conn = AppUtil::db();
		if (!$avatar) {
			$sql = 'select uThumb from im_user WHERE uId=:id ';
			$avatar = $conn->createCommand($sql)->bindValues([
				':id' => $uid
			])->queryScalar();
		}
		$rootFolder = AppUtil::rootDir();
		$backgrounds = [
			[$rootFolder . 'mobile/web/images/share/share01.jpg', 280, 145, 545],
			[$rootFolder . 'mobile/web/images/share/share02.jpg', 260, 155, 550],
			[$rootFolder . 'mobile/web/images/share/share03.jpg', 250, 160, 350],
			[$rootFolder . 'mobile/web/images/share/share04.jpg', 260, 20, 570],
		];
		$category = self::CATEGORY_SHARES;
		$qrItems = [];
		$sql = 'select * from im_user_qr 
			WHERE qUId=:uid AND qCategory=:cat AND qMD5=:md5 AND qStatus=1';
		$cmd = $conn->createCommand($sql);

		$sql = 'update im_user_qr set qStatus=0 WHERE qUId=:uid AND qCategory=:cat AND qMD5=:md5';
		$cmdUpdate = $conn->createCommand($sql);

		$sql = 'INSERT INTO im_user_qr(qUId,qOpenId,qCategory,qMD5, qUrl,qRaw) 
			SELECT uId,uOpenId,:cat,:md5,:url,:raw FROM im_user WHERE uId=:uid ';
		$cmdAdd = $conn->createCommand($sql);

		foreach ($backgrounds as $background) {
			list($bgImage, $qrSize, $offsetX, $offsetY) = $background;
			$raw = json_encode([$uid, $avatar, $bgImage, $qrSize, $offsetX, $offsetY], JSON_UNESCAPED_UNICODE);
			$md5 = md5($raw);
			$ret = $cmd->bindValues([
				':uid' => $uid,
				':cat' => $category,
				':md5' => $md5,
			])->queryScalar();
			if ($ret) {
				$qrItems[] = $ret['qUrl'];
				continue;
			}

			$qrFile = self::getQRCode($uid, self::CATEGORY_MATCH, $avatar);
			if (AppUtil::isDev()) {
				$qrFile = $rootFolder . 'mobile/web/images/qrmeipo100.jpg';
			} elseif (strpos($qrFile, 'http') !== false) {
				$tmpFile = AppUtil::imgDir() . 'qr' . date('ymdHi') . RedisUtil::getImageSeq();
				$qrFile = self::downloadFile($qrFile, $tmpFile);
			}
			list($width, $height, $type) = getimagesize($bgImage);

			$saveAs = AppUtil::imgDir() . 'qr' . date('ymdHi') . RedisUtil::getImageSeq() . '.jpg';
			$mergeImg = Image::open($qrFile)->zoomCrop($qrSize, $qrSize, 0xffffff, 'left', 'top');
			$img = Image::open($bgImage)
				->merge($mergeImg, $offsetX, $offsetY, $qrSize, $qrSize)
				->save($saveAs);
			$qUrl = ImageUtil::getUrl($saveAs);

			$cmdUpdate->bindValues([
				':uid' => $uid,
				':cat' => $category,
				':md5' => $md5,
			])->execute();

			$cmdAdd->bindValues([
				':uid' => $uid,
				':cat' => $category,
				':md5' => $md5,
				':raw' => $raw,
				':url' => $qUrl,
			])->execute();

			$qrItems[] = $qUrl;
		}
		return $qrItems;
	}


	public static function mpShareQR($uid, $avatar = '', $title = '')
	{
		$conn = AppUtil::db();
		if (!$avatar) {
			$sql = 'select uThumb from im_user WHERE uId=:id ';
			$avatar = $conn->createCommand($sql)->bindValues([
				':id' => $uid
			])->queryScalar();
		}
		if (!$title) {
			$title = "你所以为的巧合\n不过是另一个人使用『千寻恋恋』的结果";
		}
		$subTitle = "想找对象就上千寻恋恋\n\n本地相亲交友平台\n扫一扫脱单就这么简单";
		$category = self::CATEGORY_MATCH_SHARE;

		$rootFolder = AppUtil::rootDir();
		$bgImage = $rootFolder . 'mobile/assets/bg_mp02.jpg';
		$raw = json_encode([$uid, $avatar, $title, $subTitle, $bgImage], JSON_UNESCAPED_UNICODE);
		$md5 = md5($raw);

		$sql = 'select * from im_user_qr 
			WHERE qUId=:uid AND qCategory=:cat AND qMD5=:md5 AND qStatus=1';
		$ret = $conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':cat' => $category,
			':md5' => $md5,
		])->queryOne();
		if ($ret) {
			return $ret['qUrl'];
		}

		$qrFile = self::getQRCode($uid, self::CATEGORY_MATCH, $avatar);
		if (AppUtil::isDev()) {
			$qrFile = $rootFolder . 'mobile/web/images/qrmeipo100.jpg';
		} elseif (strpos($qrFile, 'http') !== false) {
			$tmpFile = AppUtil::imgDir() . 'qr' . RedisUtil::getImageSeq();
			$qrFile = self::downloadFile($qrFile, $tmpFile);
		}
		$qrSize = 330;
		list($width, $height, $type) = getimagesize($bgImage);
		$fontPath = $rootFolder . 'common/assets/FZY3JW.ttf';
		$fontPath2 = $rootFolder . 'common/assets/FZZQJW.ttf';
		$saveAs = AppUtil::imgDir() . 'qr' . RedisUtil::getImageSeq() . '.jpg';
		$mergeImg = Image::open($qrFile)->zoomCrop($qrSize, $qrSize, 0xffffff, 'left', 'top');
		$arrSubTitle = explode("\n", $subTitle);
		$img = Image::open($bgImage)
			->merge($mergeImg, 15, $height - $qrSize - 25, $qrSize, $qrSize)
			->write($fontPath, $title, $width / 2, 608, 25, 0, 0x111111, 'center');
		foreach ($arrSubTitle as $k => $text) {
			$img = $img->write($fontPath2, $text, ($width + $qrSize) / 2, $height - 240 + $k * 40, 19, 0, 0x111111, 'center');
		}
		$img->save($saveAs);
		$qUrl = ImageUtil::getUrl($saveAs);

		$sql = 'update im_user_qr set qStatus=0 WHERE qUId=:uid AND qCategory=:cat';
		$conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':cat' => $category,
		])->execute();

		$sql = 'INSERT INTO im_user_qr(qUId,qOpenId,qCategory,qMD5,qTitle,qSubTitle,qUrl,qRaw) 
			SELECT uId,uOpenId,:cat,:md5,:title,:subtitle,:url,:raw FROM im_user WHERE uId=:uid ';
		$conn->createCommand($sql)->bindValues([
			':uid' => $uid,
			':cat' => $category,
			':md5' => $md5,
			':title' => $title,
			':subtitle' => $subTitle,
			':raw' => $raw,
			':url' => $qUrl,
		])->execute();
		return $qUrl;
	}

	protected static function makeQR($qid, $qrName = '', $topTitle = '', $bottomTitle = '', $mergeFile = '')
	{
		if (!$qrName) {
			$qrName = 'qr' . $qid;
		}
		$access_token = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
		//api位置：https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1443433542
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
				$img->write($fontPath, $topTitle, $width / 2, 20, 11, 0, 0x444444, 'center');
			}
			$img->save($saveAs);
			$accessUrl = ImageUtil::getUrl($saveAs);
			unlink($mergeFile);
			return [$accessUrl, $originUrl];
		}
		return ['', ''];
	}

	protected static function downloadFile($url, $saveAs)
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
		'cheny' => ['name' => '陈瑶', 'avatar' => 'star_cheny.jpg'],
	];

	public static function createInvitation($uid, $nickname, $starId, $h2, $h5)
	{
		$uInfo = User::findOne(['uId' => $uid]);
		if (!$uInfo) return '';
		$avatar = $uInfo->uAvatar;
		if (AppUtil::isDev()) {
			$avatar = AppUtil::rootDir() . 'mobile/assets/star_wuyz.jpg';
		}
		if (!$nickname) {
			$nickname = $uInfo->uName;
		}
		$gender = $uInfo->uGender;
		$rootFolder = AppUtil::rootDir();

		$star = self::$SuperStars[$starId];
		$h4 = $nickname . ' & ' . $star['name'];
		/*if ($gender == User::GENDER_FEMALE) {
			$h4 = $star['name'] . ' & ' . $nickname;
		}*/

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

	public static function createInvitationForMarry($uId, $name1, $name2, $dt)
	{
		$rootFolder = AppUtil::rootDir();
		$bgFile = $rootFolder . 'mobile/assets/qt100.jpg';
		$qrFile = UserQR::createQR($uId, UserQR::CATEGORY_SALES, 'marry2');

		$raw = json_encode([$name1, $name2, $dt, $qrFile], JSON_UNESCAPED_UNICODE);
		$md5 = md5($raw);
		$qrInfo = self::findOne(['qUId' => $uId, 'qCategory' => self::CATEGORY_MARRY, 'qMD5' => $md5]);
		if ($qrInfo && !AppUtil::isDev()) {
			return $qrInfo->qUrl;
		}
		$mergeFile = $rootFolder . 'common/assets/qr_invitation.jpeg';
		if ($qrFile && !AppUtil::isDev()) {
			$mergeFile = $qrFile;
			if (strpos($qrFile, 'http') !== false) {
				$mergeFile = ImageUtil::getFilePath($qrFile);
			}
		}
		$mergeSize = 330;
		$mergeImg = Image::open($mergeFile)->zoomCrop($mergeSize, $mergeSize, 0xffffff, 'center', 'center');
		$img = Image::open($bgFile)->merge($mergeImg, 20, 860, $mergeSize, $mergeSize);

		$gy = date("Y", strtotime($dt));
		$gm = date("m", strtotime($dt));
		$gd = date("d", strtotime($dt));
		$w = date("w", strtotime($dt));
		$man = $name1;
		$woman = $name2;
		$xing = mb_substr($man, 0, 1);
		$time = "下午18点18分";
		$addr = "微媒大道88号";
		$addrDes = "五洲国际大酒店2楼宴会厅";
		$from = $xing . "爸爸&" . $xing . "妈妈";
		$h5Font = $rootFolder . 'common/assets/hkst.ttf';

		$saveAs = 'inv' . RedisUtil::getImageSeq() . '.jpg';
		$saveAs = AppUtil::imgDir() . $saveAs;

		// $img = Image::open($bgFile);
		if ($gy) {
			$img->write($h5Font, $gy, 455, 573, 15, 34, 0x000000, 'center');
		}
		if ($gm) {
			$img->write($h5Font, $gm, 510, 529, 15, 34, 0x000000, 'center');
		}
		if ($gd) {
			$img->write($h5Font, $gd, 550, 500, 15, 34, 0x000000, 'center');
		}
		if ($w) {
			$img->write($h5Font, $w, 640, 441, 15, 34, 0x000000, 'center');
		}
		if ($man) {
			$img->write($h5Font, $man, 470, 636, 15, 34, 0x000000, 'center');
		}
		if ($woman) {
			$img->write($h5Font, $woman, 490, 663, 15, 34, 0x000000, 'center');
		}
		if ($time) {
			$img->write($h5Font, $time, 524, 708, 10, 34, 0x000000, 'center');
		}
		if ($addrDes) {
			$img->write($h5Font, $addrDes, 560, 732, 10, 34, 0x000000, 'center');
		}
		if ($addr) {
			$img->write($h5Font, $addr, 545, 752, 10, 34, 0x000000, 'center');
		}
		if ($from) {
			$img->write($h5Font, $from, 700, 668, 10, 34, 0x000000, 'center');
		}
		$img->save($saveAs);
		$accessUrl = ImageUtil::getUrl($saveAs);

		self::deleteAll(['qUId' => $uId, 'qCategory' => self::CATEGORY_MARRY]);
		$entity = new self();
		$entity->qUId = $uId;
		$entity->qCategory = self::CATEGORY_MARRY;
		$entity->qCode = 'meipo100-marry2';
		$entity->qMD5 = $md5;
		$entity->qRaw = $raw;
		$entity->qUrl = $accessUrl;
		$entity->save();
		return $accessUrl;
	}


}