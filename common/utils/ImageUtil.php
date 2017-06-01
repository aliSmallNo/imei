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
	const DEFAULT_IMAGE = "//bpbhd-10063905.file.myqcloud.com/common/ic_default.jpg";
	const DEFAULT_AVATAR = "//bpbhd-10063905.file.myqcloud.com/common/ic_default_sm.png";

	// Rain: 大于10m,要分片上传
	const MAX_UNSLICE_FILE_SIZE = 10485760;
	const MAX_RETRY_TIMES = 3;
	const SIZE_500K = 500000;
	const SIZE_1M = 1048576;
	const SLICE_SIZE_3M = 3145728;

	const WIDTH_THUMB = 240;
	const WIDTH_IMAGE = 640;

	private static $CategoryImei = "imei";

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
		$signStr = RedisUtil::getCache(RedisUtil::KEY_COS_KEY, $oneTimeFlag ? 1 : 0);
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
			RedisUtil::setCache($signStr, RedisUtil::KEY_COS_KEY, $oneTimeFlag ? 1 : 0);
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
	 * 上传图片到云
	 * @param string $srcPath 文件路径
	 * @param string $category 种类
	 * @param string $fileExt 文件扩展名
	 * @param int $maxWidth 最大的width值
	 * @param int $maxHeight 最大的height值
	 * @return string
	 */
	public static function upload2Cloud($srcPath, $fileExt = "", $maxWidth = 0, $maxHeight = 0)
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

	public static function upload2COS($srcPath, $thumbFlag = false, $fileExt = "")
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
			$thumbSide = 280;
			$defaultSide = 420;
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
				$data['filecontent'] = Image::open($srcPath)->cropResize($newWidth, $newHeight)->get();
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
}