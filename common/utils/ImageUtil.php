<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 31/5/2017
 * Time: 9:16 AM
 */

namespace common\utils;

use Gregwar\Image\Image;

class ImageUtil
{
	const DEFAULT_IMAGE = "http://bpbhd-10063905.file.myqcloud.com/common/qxll360.jpg";
	const DEFAULT_AVATAR = "http://bpbhd-10063905.file.myqcloud.com/common/qxll360.jpg";

	// Rain: 大于10m,要分片上传
	const MAX_UNSLICE_FILE_SIZE = 10485760;
	const MAX_RETRY_TIMES = 3;
	const SIZE_500K = 500000;
	const SIZE_1M = 1048576;
	const SLICE_SIZE_3M = 3145728;

	const WIDTH_THUMB = 240;
	const WIDTH_IMAGE = 640;

	private static $CategoryImei = "imei";

	public static function getUrl($fileName)
	{
		$root = AppUtil::imgDir(true);
		$fileName = str_replace($root, '', $fileName);
		return trim(AppUtil::imageUrl(), '/') . '/' . trim($fileName, '/');
	}

	public static function getFilePath($url)
	{
		//https://img.meipo100.com/2017/84/qr17514.jpg
		$urlPrefix = AppUtil::imageUrl();
		if (AppUtil::isDev()) {
			$urlPrefix = 'https://img.meipo100.com';
		}
		$url = str_replace($urlPrefix, '', $url);
		return AppUtil::imgDir(true) . trim($url, '/');
	}

	/**
	 * 生成腾讯云对象存储服务签名 Cloud Object Storage
	 * @param bool $oneTimeFlag 是否是一次性的
	 * @param string $fileId 文件ID,用于一次性的签名
	 * @return array 签名
	 */
	protected static function cosInfo($oneTimeFlag = false, $fileId = "")
	{
		$app_id = "10063905";
		$bucket = "bpbhd";
		$url = "http://web.file.myqcloud.com/files/v2/$app_id/$bucket";
		$host = "web.file.myqcloud.com";
		$redis = RedisUtil::init(RedisUtil::KEY_CLOUD_COS, $oneTimeFlag ? 1 : 0);
		$signStr = $redis->getCache();
		if (!$signStr) {
			$secret_id = "AKIDEqyJNctINqB6re8NBeckX0wOH2CnGL0R";
			$secret_key = "At5h3sa9zKz8rsSMVqPUMN4L48uHNfNk";
			$current = time();
			$expired = $current + 3600 * 20;
			$rdm = rand();
			$srcStr = "a=$app_id&b=$bucket&k=$secret_id&e=$expired&t=$current&r=$rdm&f=";
			if ($oneTimeFlag) {
				//$fileId = "/200001/newbucket/tencent_test.jpg";
				$srcStr = "a=$app_id&b=$bucket&k=$secret_id&e=0&t=$current&r=$rdm&f=$fileId";
			}
			$bin = hash_hmac('SHA1', $srcStr, $secret_key, true);
			$bin .= $srcStr;
			$signStr = base64_encode($bin);
			$redis->setCache($signStr);
		}
		return [
			"url" => $url,
			"header" => ['Authorization:' . $signStr, "Host:" . $host]
		];
	}

	public static function resizeFileName($srcPath, $width, $height)
	{
		$fileExt = pathinfo($srcPath, PATHINFO_EXTENSION);
		if (!isset($fileExt)) {
			$mime = mime_content_type($srcPath);
			$fileExt = substr($mime, strpos($mime, '/') + 1);
		}
		$newFileName = substr($srcPath, 0, strlen($srcPath) - strlen($fileExt) - 1);
		$newFileName .= $width . "x" . $height . "." . $fileExt;
		return $newFileName;
	}

	public static function getItemImages($imageData, $defaultImage = "", $keepHttp = false)
	{
		if (!$defaultImage) {
			$defaultImage = self::DEFAULT_IMAGE;
		}
		if ($imageData) {
			if (is_string($imageData)
				&& strlen($imageData) > 5
				&& strpos($imageData, "[") === false
			) {
				return [$imageData];
			}

			$imageData = json_decode($imageData, true);
			$images = [];
			if (is_array($imageData)) {
				$prefix = AppUtil::apiUrl();
				foreach ($imageData as $url) {
					if (!is_string($url)) {
						continue;
					}
					if (strpos($url, "http") !== 0 && strpos($url, "//") !== 0) {
						$images[] = $prefix . $url;
					} else {
						$images[] = $url;
					}
				}
			} elseif (is_string($imageData) && strlen($imageData) > 5) {
				$images[] = $imageData;
			}
			if ($images) {
				if (!$keepHttp) {
					foreach ($images as $key => $image) {
						$images[$key] = str_replace("http://", "//", $image);
					}
				}
				return $images;
			}
		}
		return [$defaultImage];
	}

	/**
	 * @param array $postData
	 * @param bool $thumbFlag
	 * @param bool $squareFlag
	 * @return string json
	 */
	public static function uploadItemImages($postData, $thumbFlag = false, $squareFlag = false)
	{
		if (isset($postData["error"]) && isset($postData["tmp_name"])) {
			$result = [];
			foreach ($postData['error'] as $key => $value) {
				if ($value == UPLOAD_ERR_OK) {
					$tmpName = $postData["tmp_name"][$key];
					$upName = $postData["name"][$key];
					$upSize = $postData["size"][$key];
					$fileExt = pathinfo($upName, PATHINFO_EXTENSION);
					$fileExt = strtolower($fileExt ? $fileExt : "");
					if ($thumbFlag) {
						$url = self::upload2COS($tmpName, true, $squareFlag, $fileExt);
						$url && $result[] = $url;
					}
					$url = self::upload2COS($tmpName, false, $squareFlag, $fileExt);
					$url && $result[] = $url;
					unlink($tmpName);
				}
			}
			if ($result) {
				return json_encode($result);
			}
		}
		return '';
	}

	/**
	 * 上传图片到云
	 * @param string $srcPath 文件路径
	 * @param string $fileExt 文件扩展名
	 * @param int $maxWidth 最大的width值
	 * @param int $maxHeight 最大的height值
	 * @return string
	 */
	public static function upload2Cloud($srcPath, $fileExt = "", $maxWidth = self::WIDTH_IMAGE, $maxHeight = 0)
	{

		if (!file_exists($srcPath) || filesize($srcPath) < 10) {
			return "";
		}
		$cosInfo = self::cosInfo();

		$category = self::$CategoryImei;

		$header = $cosInfo["header"];
		$header[] = "Content-Type:multipart/form-data";
		$data = [
			'op' => 'upload',
			"insertOnly" => 0
		];

		// Rain: 对图片做压缩
		if (($maxWidth || $maxHeight) && in_array($fileExt, ["png", "jpg", "jpeg"])) {
			if ($maxWidth && $maxHeight) {
				$data['filecontent'] = Image::open($srcPath)->zoomCrop($maxWidth, $maxHeight, 0xffffff, 'center', 'center')->get();
				$sha1 = hash('sha1', $data['filecontent']);
				$data['sha'] = $sha1;
			} else {
				list($newWidth, $newHeight) = getimagesize($srcPath);
				if ($maxWidth && $newWidth > $maxWidth) {
					$newWidth = $maxWidth;
					$newHeight = intval($maxWidth * $newHeight / $newWidth);
				} elseif ($maxHeight && $newHeight > $maxHeight) {
					$newWidth = intval($maxHeight * $newWidth / $newHeight);
					$newHeight = $maxHeight;
				}
				if ($newWidth && $newHeight) {
					$data['filecontent'] = Image::open($srcPath)->cropResize($newWidth, $newHeight)->get();
					$sha1 = hash('sha1', $data['filecontent']);
					$data['sha'] = $sha1;
				}
			}
		}
		if (!isset($data["filecontent"])) {
			if (function_exists('curl_file_create')) {
				$data['filecontent'] = curl_file_create($srcPath);
			} else {
				$data['filecontent'] = '@' . $srcPath;
			}
			$sha1 = hash_file('sha1', $srcPath);
			$data['sha'] = $sha1;
		}

		if ($fileExt) {
			$fileExt = "." . $fileExt;
		}
		$imageName = self::imageName($fileExt);
		$ret = self::curlUpload($cosInfo["url"] . "/$category/" . $imageName, $data, $header);
		$ret = json_decode($ret, true);

		return isset($ret['data']['access_url']) ? $ret['data']['access_url'] : json_encode($ret);
	}

	public static function upload2COS($srcPath, $thumbFlag = false, $squareFlag = false, $fileExt = "")
	{
		if (!file_exists($srcPath) || filesize($srcPath) < 10) {
			return false;
		}
		$cosInfo = self::cosInfo();

		$category = self::$CategoryImei;

		$header = $cosInfo["header"];
		$header[] = "Content-Type:multipart/form-data";
		$data = [
			'op' => 'upload',
			"insertOnly" => 0
		];

		// Rain: 对图片做压缩
		if (in_array($fileExt, ["png", "jpg", "jpeg"])) {
			$thumbSide = 200;
			$defaultSide = 480;
			$newWidth = 0;
			$newHeight = 0;
			list($srcWidth, $srcHeight) = getimagesize($srcPath);
			if ($thumbFlag && ($srcWidth > $thumbSide || $srcHeight > $thumbSide)) {
				$newWidth = $thumbSide;
				$newHeight = $thumbSide;
			} elseif (($srcWidth > $defaultSide || $srcHeight > $defaultSide)) {
				$newWidth = $defaultSide;
				$newHeight = $defaultSide;
			}
			if ($newWidth && $newHeight) {
				if ($squareFlag) {
					$side = min($newWidth, $newHeight);
					$data['filecontent'] = Image::open($srcPath)->zoomCrop($side, $side, 0xffffff, 'center', 'center')->get();
				} else {
					$data['filecontent'] = Image::open($srcPath)->cropResize($newWidth, $newHeight)->get();
				}
				$sha1 = hash('sha1', $data['filecontent']);
				$data['sha'] = $sha1;
			}
		}
		if (!isset($data["filecontent"])) {
			if (function_exists('curl_file_create')) {
				$data['filecontent'] = curl_file_create($srcPath);
			} else {
				$data['filecontent'] = '@' . $srcPath;
			}
			$sha1 = hash_file('sha1', $srcPath);
			$data['sha'] = $sha1;
		}

		if ($fileExt) {
			$fileExt = "." . $fileExt;
		}
		$imageName = self::imageName($fileExt);
		$imageName = $cosInfo["url"] . "/$category/" . $imageName;
		$ret = self::curlUpload($imageName, $data, $header);
		$ret = json_decode($ret, true);
		return isset($ret['data']['access_url']) ? $ret['data']['access_url'] : json_encode($ret);
	}

	public static function uploadSlices2COS($srcPath, $fileExt = "", $fileSize = 0)
	{
		if (!file_exists($srcPath) || filesize($srcPath) < 10) {
			return false;
		}
		$cosInfo = self::cosInfo();
		$header = $cosInfo["header"];
		$header[] = "Content-Type:multipart/form-data";

		$category = self::$CategoryImei;
		if (function_exists('curl_file_create')) {
			$data['filecontent'] = curl_file_create($srcPath);
		} else {
			$data['filecontent'] = '@' . $srcPath;
		}
		$sha1 = hash_file('sha1', $srcPath);

		$data = [
			"op" => 'upload_slice',
			"insertOnly" => 0,
			"slice_size" => self::SLICE_SIZE_3M,
			"filesize" => $fileSize,
			"sha" => $sha1
		];

		if ($fileExt) {
			$fileExt = "." . $fileExt;
		}
		$fileName = self::imageName($fileExt);
		$url = $cosInfo["url"] . "/$category/" . $fileName;
		$ret = self::curlUpload($url, $data, $header);
		$ret = json_decode($ret, true);
		if (isset($ret['code']) && $ret['code'] != 0) {
			return $ret;
		}
		if (isset($ret['data']['access_url'])) {
			//秒传命中，直接返回了url
			return $ret['data']['access_url'];
		}

		$sliceSize = $ret['data']['slice_size'];
		if ($sliceSize > self::SLICE_SIZE_3M || $sliceSize <= 0
		) {
			$ret['code'] = -1;
			$ret['message'] = 'illegal slice size';
			return json_encode($ret);
		}
		$session = $ret['data']['session'];
		$offset = $ret['data']['offset'];

		while ($fileSize > $offset) {
			$fileContent = file_get_contents($srcPath, false, null, $offset, $sliceSize);
			if ($fileContent === false) {
				return json_encode([
					'code' => -1,
					'message' => 'read file ' . $srcPath . ' error',
					'data' => [],
				]);
			}

			$boundary = '---------------------------' . substr(md5(mt_rand()), 0, 10);
			array_pop($header);
			$header[] = 'Content-Type: multipart/form-data; boundary=' . $boundary;
			$data = self::getSliceBody($fileContent, $offset, $session, basename($srcPath), $boundary);

			$retry_times = 0;
			do {
				$ret = self::curlUpload($url, $data, $header);
				$ret = json_decode($ret, true);
				if ($ret['code'] == 0) {
					break;
				}
				$retry_times++;
			} while ($retry_times < self::MAX_RETRY_TIMES);

			if ($ret['code'] != 0) {
				return $ret;
			}

			if (isset($ret['data']['session'])) {
				$session = $ret['data']['session'];
			}
			$offset += $sliceSize;
		}
		return isset($ret['data']['access_url']) ? $ret['data']['access_url'] : json_encode($ret);
	}

	protected static function getSliceBody($fileContent, $offset, $session, $fileName, $boundary)
	{
		$formData = '';

		$formData .= '--' . $boundary . "\r\n";
		$formData .= "content-disposition: form-data; name=\"op\"\r\n\r\nupload_slice\r\n";

		$formData .= '--' . $boundary . "\r\n";
		$formData .= "content-disposition: form-data; name=\"offset\"\r\n\r\n" . $offset . "\r\n";

		$formData .= '--' . $boundary . "\r\n";
		$formData .= "content-disposition: form-data; name=\"session\"\r\n\r\n" . $session . "\r\n";

		$formData .= '--' . $boundary . "\r\n";
		$formData .= "content-disposition: form-data; name=\"fileContent\"; filename=\"" . $fileName . "\"\r\n";
		$formData .= "content-type: application/octet-stream\r\n\r\n";

		$data = $formData . $fileContent . "\r\n--" . $boundary . "--\r\n";

		return $data;
	}

	protected static function imageName($fileExt)
	{
		$fileExt = trim($fileExt, '.');
		return date('ymdHis') . RedisUtil::getImageSeq() . '.' . $fileExt;
	}

	protected static function curlUpload($url, $data, $header = [], $method = "POST")
	{
		$curlHandler = curl_init();
		curl_setopt($curlHandler, CURLOPT_URL, $url);
		$method = strtoupper($method);
		$header = isset($header) ? $header : [];
		$header[] = 'Method:' . $method;

		curl_setopt($curlHandler, CURLOPT_TIMEOUT, 150);
		curl_setopt($curlHandler, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlHandler, CURLOPT_CUSTOMREQUEST, $method);
		isset($data) && in_array($method, array('POST', 'PUT')) && curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $data);
		if (strpos($url, "https:") === 0) {
			curl_setopt($curlHandler, CURLOPT_SSL_VERIFYPEER, false);   //true any ca
			curl_setopt($curlHandler, CURLOPT_SSL_VERIFYHOST, 1);       //check only host
			/*if (isset($rq['ssl_version'])) {
				curl_setopt($curlHandler, CURLOPT_SSLVERSION, $rq['ssl_version']);
			} else {
				curl_setopt($curlHandler, CURLOPT_SSLVERSION, 4);
			}*/
			curl_setopt($curlHandler, CURLOPT_SSLVERSION, 4);
		}
		$ret = curl_exec($curlHandler);
		curl_close($curlHandler);
		return $ret;
	}

	public static function upload2Server($postData, $squareFlag = false, $savedKey = '')
	{
		$result = [];
		if (!isset($postData["error"]) || !isset($postData["tmp_name"])) {
			return $result;
		}
		if (!$savedKey) {
			$savedKey = RedisUtil::getImageSeq();
		}

		foreach ($postData['error'] as $key => $value) {
			if ($value != UPLOAD_ERR_OK) {
				continue;
			}
			$tmpName = $postData["tmp_name"][$key];
			$upName = $postData["name"][$key];
			$fileExt = pathinfo($upName, PATHINFO_EXTENSION);
			$fileExt = strtolower($fileExt ? $fileExt : "");

			$content = file_get_contents($tmpName);
			$path = AppUtil::imgDir() . $savedKey;
			$fileName = $path . '.' . $fileExt;
			$fileThumb = $path . '_t.' . $fileExt;
			$fileNormal = $path . '_n.' . $fileExt;

			file_put_contents($fileName, $content);
			$content = null;
			$thumbSize = 160;
			$figureSize = 560;
			if ($squareFlag) {
				$figureWidth = $figureHeight = $figureSize;
			} else {
				list($srcWidth, $srcHeight) = getimagesize($fileName);
				if ($srcWidth > $srcHeight) {
					$figureHeight = $figureSize;
					$figureWidth = $srcWidth * $figureSize / $srcHeight;
				} else {
					$figureWidth = $figureSize;
					$figureHeight = $srcHeight * $figureSize / $srcWidth;
				}
			}
			Image::open($fileName)->zoomCrop($thumbSize, $thumbSize, 0xffffff, 'center', 'center')->save($fileThumb);
			$thumb = self::getUrl($fileThumb);
			Image::open($fileName)->zoomCrop($figureWidth, $figureHeight, 0xffffff, 'center', 'center')->save($fileNormal);
			$figure = self::getUrl($fileNormal);
			unlink($tmpName);
			$result[] = [$thumb, $figure];
		}
		return $result;
	}

	public static function save2Server($imageUrl, $squareFlag = false, $top = -1, $left = -1)
	{
		$key = RedisUtil::getImageSeq() . date('His');
		if (strpos($imageUrl, 'http') !== 0) {
			// Rain: Media ID (Wechat Server ID)
			$accessToken = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
			$baseUrl = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=%s&media_id=%s";
			$imageUrl = sprintf($baseUrl, $accessToken, $imageUrl);
		}

		$ch = curl_init($imageUrl);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($ch);
		$httpInfo = curl_getinfo($ch);
		curl_close($ch);

		$contentType = $httpInfo["content_type"];
		$contentType = strtolower($contentType);
		$ext = AppUtil::getExtName($contentType);
		if (!$ext || strlen($content) < 128) {
			return ['', ''];
		}
		if ($ext == "amr") {
			$filePrefix = AppUtil::catDir(false, "voice") . $key;
			$fileName = $filePrefix . ".amr";
			$fileMP3 = $filePrefix . ".mp3";
			file_put_contents($fileName, $content);
			$content = null;
			exec('/usr/bin/ffmpeg -i ' . $fileName . ' -ab 12.2k -ar 16000 -ac 1 ' . $fileMP3, $out);
			$ret = ImageUtil::getUrl($fileMP3);
			//$ret = '/voice/' . $key . '.' . $ext;
			return [$ret, $ret];
		} else {
			$path = AppUtil::imgDir() . $key;
			$fileName = $path . '.' . $ext;
			file_put_contents($fileName, $content);
			$content = null;
			$thumbSize = $thumbWidth = $thumbHeight = 180;
			$figureSize = $figureWidth = $figureHeight = 640;
			if ($squareFlag) {
				$thumbSize = $thumbWidth = $thumbHeight = 150;
				$figureSize = $figureWidth = $figureHeight = 560;
			}
			list($srcWidth, $srcHeight) = getimagesize($fileName);
			if ($srcWidth > $srcHeight) {
				$figureWidth = round($figureHeight * $srcWidth / $srcHeight);
				$thumbWidth = round($thumbHeight * $srcWidth / $srcHeight);
			} else {
				$figureHeight = round($figureWidth * $srcHeight / $srcWidth);
				$thumbHeight = round($thumbWidth * $srcHeight / $srcWidth);
			}
			$fileThumb = $path . '_t.' . $ext;
			$fileNormal = $path . '_n.' . $ext;
			$thumbObj = Image::open($fileName)->zoomCrop($thumbWidth, $thumbHeight, 0xffffff, 'left', 'top');
			$figureObj = Image::open($fileName)->zoomCrop($figureWidth, $figureHeight, 0xffffff, 'left', 'top');
			if ($squareFlag) {
				if ($top >= 0) {
					$thumbY = round($thumbHeight * $top / 100.0);
					$figureY = round($figureHeight * $top / 100.0);
				} else {
					$thumbY = round(($thumbHeight - $thumbSize) / 2.0);
					$figureY = round(($figureHeight - $figureSize) / 2.0);
				}
				if (2 > $thumbY) $thumbY = 0;
				if (2 > $figureY) $figureY = 0;

				if ($left >= 0) {
					$thumbX = round($thumbWidth * $left / 100.0);
					$figureX = round($figureWidth * $left / 100.0);
				} else {
					$thumbX = round(($thumbWidth - $thumbSize) / 2.0);
					$figureX = round(($figureWidth - $figureSize) / 2.0);
				}
				if (2 > $thumbX) $thumbX = 0;
				if (2 > $figureX) $figureX = 0;
				$thumbObj = $thumbObj->crop($thumbX, $thumbY, $thumbSize, $thumbSize);
				$figureObj = $figureObj->crop($figureX, $figureY, $figureSize, $figureSize);
			}
			$thumbObj->save($fileThumb);
			$thumb = self::getUrl($fileThumb);
			$figureObj->save($fileNormal);
			$figure = self::getUrl($fileNormal);
			//unlink($fileName);
			$thumbObj = null;
			$figureObj = null;
			return [$thumb, $figure];
		}
	}

	public static function clippingMask($sourceFile, $maskFile, $saveAs, $width, $height = 0)
	{
		if (!$height) {
			$height = $width;
		}
		$circle = new \Imagick();
		$circle->readImage($maskFile);
		$circle->cropThumbnailImage($width, $height);

		$imagick = new \Imagick();
		$imagick->readImage($sourceFile);
		$imagick->setImageFormat('png');
		$imagick->setimagematte(true);
		$imagick->cropThumbnailImage($width, $height);
		$imagick->compositeimage($circle, \Imagick::COMPOSITE_COPYOPACITY, 0, 0);

		$border = new \Imagick();
		$maskFile = str_replace('.png', '_border.png', $maskFile);
		$border->readImage($maskFile);
		$border->cropThumbnailImage($width, $height);
		$imagick->compositeimage($border, \Imagick::COMPOSITE_ATOP, 0, 0);

		$imagick->writeImage($saveAs);
		$circle->destroy();
		$imagick->destroy();
		$border->destroy();

		return $saveAs;
	}

	public static function rotate($imageUrl, $angle = -90)
	{
		$saveAs = self::getFilePath($imageUrl);
		if (!is_file($saveAs)) {
			return false;
		}
		$saveThumb = str_replace('_n.', '_t.', $saveAs);
		if (!is_file($saveThumb)) {
			return false;
		}
		Image::open($saveAs)->rotate($angle)->save($saveAs);
		Image::open($saveThumb)->rotate($angle)->save($saveThumb);
		return true;
	}

	public static function multiAvatar($avatars)
	{
		$bgColor = 0xeeeeee;
		$bgSize = 240;
		$padding = 2;
		$imgSize = 120;
		$cnt = count($avatars);
		if ($cnt > 4) {
			$imgSize = 80;
		}
		$back = Image::create($bgSize, $bgSize)->fill($bgColor);
		$merges = $downloadFiles = [];
		$dir = AppUtil::imgDir();
		foreach ($avatars as $avatar) {
			$downloadFile = self::downloadFile($avatar, $dir . 'a_' . RedisUtil::getImageSeq());
			$downloadFiles[] = $downloadFile;
			$tmp = Image::open($downloadFile)->zoomCrop($imgSize - $padding * 2, $imgSize - $padding * 2, $bgColor, 'center', 'center');
			$merges[] = Image::create($imgSize, $imgSize)->merge($tmp, $padding, $padding,
				$imgSize - $padding * 2, $imgSize - $padding * 2)->fill($bgColor);
		}
		$index = 0;
		switch ($cnt) {
			case 2:
				$back->merge($merges[$index++], 0, ($bgSize - $imgSize) / 2)
					->merge($merges[$index++], $imgSize, ($bgSize - $imgSize) / 2);
				break;
			case 3:
				$back->merge($merges[$index++], ($bgSize - $imgSize) / 2, 0)
					->merge($merges[$index++], 0, $imgSize)
					->merge($merges[$index++], $imgSize, $imgSize);
				break;
			case 4:
				$back->merge($merges[$index++], 0, 0)
					->merge($merges[$index++], $imgSize, 0)
					->merge($merges[$index++], 0, $imgSize)
					->merge($merges[$index++], $imgSize, $imgSize);
				break;
			case 5:
				$back->merge($merges[$index++], ($bgSize - $imgSize * 2) / 2, ($bgSize - $imgSize * 2) / 2)
					->merge($merges[$index++], ($bgSize + $imgSize * 2) / 2, ($bgSize - $imgSize * 2) / 2)
					->merge($merges[$index++], 0, ($bgSize + $imgSize * 2) / 2)
					->merge($merges[$index++], $imgSize, ($bgSize + $imgSize * 2) / 2)
					->merge($merges[$index++], $imgSize * 2, ($bgSize + $imgSize * 2) / 2);
				break;
			case 6:
				$back->merge($merges[$index++], 0, ($bgSize - $imgSize * 2) / 2)
					->merge($merges[$index++], $imgSize, ($bgSize - $imgSize * 2) / 2)
					->merge($merges[$index++], $imgSize * 2, ($bgSize - $imgSize * 2) / 2)
					->merge($merges[$index++], 0, ($bgSize + $imgSize * 2) / 2)
					->merge($merges[$index++], $imgSize, ($bgSize + $imgSize * 2) / 2)
					->merge($merges[$index++], $imgSize * 2, ($bgSize + $imgSize * 2) / 2);
				break;
			case 7:
				$back->merge($merges[$index++], $imgSize, 0)
					->merge($merges[$index++], 0, $imgSize)
					->merge($merges[$index++], $imgSize, $imgSize)
					->merge($merges[$index++], $imgSize * 2, $imgSize)
					->merge($merges[$index++], 0, $imgSize * 2)
					->merge($merges[$index++], $imgSize, $imgSize * 2)
					->merge($merges[$index++], $imgSize * 2, $imgSize * 2);
				break;
			case 8:
				$back->merge($merges[$index++], $imgSize / 2, 0)
					->merge($merges[$index++], $imgSize / 2 + $imgSize, 0)
					->merge($merges[$index++], 0, $imgSize)
					->merge($merges[$index++], $imgSize, $imgSize)
					->merge($merges[$index++], $imgSize * 2, $imgSize)
					->merge($merges[$index++], 0, $imgSize * 2)
					->merge($merges[$index++], $imgSize, $imgSize * 2)
					->merge($merges[$index++], $imgSize * 2, $imgSize * 2);
				break;
			case 9:
				$back->merge($merges[$index++], 0, 0)
					->merge($merges[$index++], $imgSize, 0)
					->merge($merges[$index++], $imgSize * 2, 0)
					->merge($merges[$index++], 0, $imgSize)
					->merge($merges[$index++], $imgSize, $imgSize)
					->merge($merges[$index++], $imgSize * 2, $imgSize)
					->merge($merges[$index++], 0, $imgSize * 2)
					->merge($merges[$index++], $imgSize, $imgSize * 2)
					->merge($merges[$index++], $imgSize * 2, $imgSize * 2);
				break;
		}
		$saveAs = $dir . 'm' . RedisUtil::getImageSeq() . '.jpg';
		$padding++;
		Image::create($bgSize + $padding * 2, $bgSize + $padding * 2)
			->merge($back, $padding, $padding, $bgSize, $bgSize)
			->fill($bgColor)->save($saveAs, 'jpg', 85);
		foreach ($downloadFiles as $file) {
			unlink($file);
		}
		return $saveAs;

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

	public static function downImage($url)
	{
		$saveAs = AppUtil::imgDir().$key = RedisUtil::getImageSeq() . date('His');
		return self::downloadFile($url, $saveAs);
	}

}