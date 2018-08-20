<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 18/11/2017
 * Time: 6:36 PM
 */

namespace common\utils;

use Gregwar\Image\Image;

class COSUtil
{
	protected $resType;
	protected $resRename;
	protected $resExtension;
	protected $uploadFolder;
	public $resPath;
	public $savedPath;
	public $hasError = false;

	const UPLOAD_URL = 100;
	const UPLOAD_PATH = 110;
	const UPLOAD_MEDIA = 120;

	private $App_Id = '10063905';
	private $Bucket = 'bpbhd';
	private $Secret_Id = 'AKIDEqyJNctINqB6re8NBeckX0wOH2CnGL0R';
	private $Secret_Key = 'At5h3sa9zKz8rsSMVqPUMN4L48uHNfNk';
	private $Host = "sh.file.myqcloud.com";

	public static function init($resType, $resPath, $ext = '')
	{
		$util = new self();
		$util->resPath = $resPath;
		$util->resType = $resType;
		$util->resRename = date('ymd') . (1000001 + RedisUtil::getImageSeq());
		if ($ext) {
			$util->resExtension = strtolower($ext);
		}
		$util->savedPath = $util->save2Local();
		$util->hasError = (!$util->savedPath);
		return $util;
	}

	protected function save2Local()
	{
		$content = '';
		switch ($this->resType) {
			case self::UPLOAD_MEDIA:
				$accessToken = WechatUtil::getAccessToken(WechatUtil::ACCESS_CODE);
				$baseUrl = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=%s&media_id=%s";
				$url = sprintf($baseUrl, $accessToken, $this->resPath);
				list($content, $this->resExtension) = $this->download($url);
				$this->resRename .= '.' . $this->resExtension;
				break;
			case self::UPLOAD_URL:
				list($content, $this->resExtension) = $this->download($this->resPath);
				$this->resRename .= '.' . $this->resExtension;
				break;
			case self::UPLOAD_PATH:
				if (is_file($this->resPath)) {
					if (!$this->resExtension) {
						$this->resExtension = pathinfo($this->resPath, PATHINFO_EXTENSION);
					}
					$this->resRename .= '.' . $this->resExtension;
					$content = file_get_contents($this->resPath);
				}
				break;
		}
		if (!$this->resExtension) {
			return '';
		}
		$this->uploadFolder = self::getFolder($this->resExtension);
		$saveAs = AppUtil::catDir(false, $this->uploadFolder) . $this->resRename;
		file_put_contents($saveAs, $content);
		return $saveAs;
	}

	protected function download($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($ch);
		$httpInfo = curl_getinfo($ch);
		curl_close($ch);
		$contentType = $httpInfo["content_type"];
		$contentType = strtolower($contentType);
		$ext = self::getExtension($contentType);
		return [$content, $ext];
	}

	public static function getFolder($ext)
	{
		$ext = strtolower($ext);
		$cat = '';
		switch ($ext) {
			case 'png':
			case 'gif':
			case 'jpg':
			case 'jpeg':
			case 'webp':
				$cat = 'image';
				break;
			case 'mp4':
			case 'avi':
			case 'mov':
			case '3gp':
				$cat = 'video';
				break;
			case 'mp3':
			case 'amr':
			case 'wav':
				$cat = 'audio';
				break;
		}
		return $cat;
	}

	public static function getExtension($contentType)
	{
		$fileExt = '';
		switch ($contentType) {
			case "image/jpeg":
			case "image/jpg":
				$fileExt = "jpg";
				break;
			case "image/png":
				$fileExt = "png";
				break;
			case "image/gif":
				$fileExt = "gif";
				break;
			case 'image/webp':
				$fileExt = "webp";
				break;
			case "audio/mpeg":
			case "audio/mp3":
				$fileExt = "mp3";
				break;
			case "audio/amr":
				$fileExt = "amr";
				break;
			case 'audio/x-wav':
				$fileExt = "wav";
				break;
			case "video/mp4":
			case "video/mpeg4":
				$fileExt = "mp4";
				break;
			case 'video/quicktime':
				$fileExt = "mov";
				break;
			case 'video/x-msvideo':
				$fileExt = "avi";
				break;
			case 'video/3gpp':
				$fileExt = "3gp";
				break;
			default:
				break;
		}
		return $fileExt;
	}


	public function uploadOnly($thumbFlag = false, $squareFlag = false, $compressFlag = true, $resetFlag = false)
	{
		$data = [
			'op' => 'upload',
			"insertOnly" => 0
		];
		$srcPath = $this->savedPath;
		// Rain: 对图片做压缩
		if ($this->uploadFolder == 'image') {
			$thumbSide = 200;
			$defaultSide = 680;

			list($srcWidth, $srcHeight) = getimagesize($srcPath);
			if ($thumbFlag && ($srcWidth > $thumbSide || $srcHeight > $thumbSide)) {
				$newWidth = $thumbSide;
				$newHeight = $thumbSide;
			} elseif ($compressFlag && ($srcWidth > $defaultSide || $srcHeight > $defaultSide)) {
				$newWidth = $defaultSide;
				$newHeight = $defaultSide;
			} else {
				$newWidth = $srcWidth;
				$newHeight = $srcHeight;
			}
			if ($newWidth && $newHeight) {
				if ($squareFlag) {
					$side = min($newWidth, $newHeight);
					$data['filecontent'] = Image::open($srcPath)->zoomCrop($side, $side, 0xffffff, 'center', 'center')->get();
				} elseif ($compressFlag) {
					$data['filecontent'] = Image::open($srcPath)->cropResize($newWidth, $newHeight)->get();
				} else {
					$data['filecontent'] = Image::open($srcPath)->get();
				}
				$data['sha'] = hash('sha1', $data['filecontent']);
			}
		}
		if (!isset($data["sha"])) {
			if (function_exists('curl_file_create')) {
				$data['filecontent'] = curl_file_create($srcPath);
			} else {
				$data['filecontent'] = '@' . $srcPath;
			}
			$data['sha'] = hash_file('sha1', $srcPath);
		}

		$url = $this->getUrl() . "/" . ($thumbFlag ? 't' : 'n') . $this->resRename;
		$ret = $this->curlUpload($url, $data, $resetFlag);
		$ret = json_decode($ret, true);
		$cosUrl = isset($ret['data']['access_url']) ? $ret['data']['access_url'] : json_encode($ret);
		$cosUrl = str_replace('http://', 'https://', $cosUrl);
		return $cosUrl;
	}

	public function uploadBoth($squareFlag = false, $top = -1, $left = -1, $resetFlag = false)
	{
		if ($this->hasError) {
			return ['', ''];
		}
		if ($this->resExtension == "amr") {
			$fileMP3 = str_replace(".amr", ".mp3", $this->savedPath);
			exec('/usr/bin/ffmpeg -i ' . $this->savedPath . ' -ab 12.2k -ar 16000 -ac 1 ' . $fileMP3, $out);
			$ret = ImageUtil::getUrl($fileMP3);
			//$ret = '/voice/' . $key . '.' . $ext;
			return [$ret, $ret, $this->savedPath];
		} else {
			$thumbData = $figureData = [
				'op' => 'upload',
				"insertOnly" => 0
			];
			$thumbSize = $thumbWidth = $thumbHeight = 180;
			$figureSize = $figureWidth = $figureHeight = 640;
			if ($squareFlag) {
				$thumbSize = $thumbWidth = $thumbHeight = 180;
				$figureSize = $figureWidth = $figureHeight = 640;
			}
			list($srcWidth, $srcHeight) = getimagesize($this->savedPath);
			if ($srcWidth > $srcHeight) {
				$figureWidth = round($figureHeight * $srcWidth / $srcHeight);
				$thumbWidth = round($thumbHeight * $srcWidth / $srcHeight);
			} else {
				$figureHeight = round($figureWidth * $srcHeight / $srcWidth);
				$thumbHeight = round($thumbWidth * $srcHeight / $srcWidth);
			}
			$thumbObj = Image::open($this->savedPath)->zoomCrop($thumbWidth, $thumbHeight, 0xffffff, 'left', 'top');
			$figureObj = Image::open($this->savedPath)->zoomCrop($figureWidth, $figureHeight, 0xffffff, 'left', 'top');
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
			$url = $this->getUrl() . "/t" . $this->resRename;
			$thumbData['filecontent'] = $thumbObj->get();
			$thumbData['sha'] = hash('sha1', $thumbData['filecontent']);
			$ret = $this->curlUpload($url, $thumbData, $resetFlag);
			$ret = json_decode($ret, true);
			$thumbUrl = isset($ret['data']['access_url']) ? $ret['data']['access_url'] : json_encode($ret);

			$url = $this->getUrl() . "/n" . $this->resRename;
			$figureData['filecontent'] = $figureObj->get();
			$figureData['sha'] = hash('sha1', $figureData['filecontent']);
			$ret = $this->curlUpload($url, $figureData, $resetFlag);
			$ret = json_decode($ret, true);
			if (isset($ret['data']['access_url'])) {
				$figureUrl = $ret['data']['access_url'];
			} else {
				$figureUrl = json_encode($ret);
				$this->getHeader(true);
			}
			return [$thumbUrl, $figureUrl, $this->savedPath];
		}
	}

	protected function curlUpload($url, $data, $resetFlag = false)
	{
		$method = "POST";
		$header = $this->getHeader($resetFlag);
		$curlHandler = curl_init();
		curl_setopt($curlHandler, CURLOPT_URL, $url);
		$method = strtoupper($method);

		curl_setopt($curlHandler, CURLOPT_TIMEOUT, 150);
		curl_setopt($curlHandler, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlHandler, CURLOPT_CUSTOMREQUEST, $method);
		isset($data) && curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $data);
		if (strpos($url, "https:") === 0) {
			curl_setopt($curlHandler, CURLOPT_SSL_VERIFYPEER, false);   //true any ca
			curl_setopt($curlHandler, CURLOPT_SSL_VERIFYHOST, 1);       //check only host
			curl_setopt($curlHandler, CURLOPT_SSLVERSION, 4);
		}
		$ret = curl_exec($curlHandler);
		curl_close($curlHandler);
		return $ret;
	}

	protected function getUrl()
	{
		$url = "http://%s/files/v2/%s/%s/%s";
		$url = sprintf($url, $this->Host, $this->App_Id, $this->Bucket, $this->uploadFolder);
		return $url;
	}

	/**
	 * @param bool $resetFlag
	 * @return array
	 */
	protected function getHeader($resetFlag = false)
	{
		$redis = RedisUtil::init(RedisUtil::KEY_COS_SIGN);
		$signStr = $redis->getCache();

		if ($resetFlag || !$signStr) {
			$current = time();
			$expired = $current + 86400 + 120;

			$rnd = mt_rand(1000001, 9999999);
			$srcStr = "a=%s&b=%s&k=%s&e=%s&t=%s&r=%s&f=";
			$srcStr = sprintf($srcStr, $this->App_Id, $this->Bucket, $this->Secret_Id,
				$expired, $current, $rnd);
			$signStr = base64_encode(hash_hmac('sha1', $srcStr, $this->Secret_Key, true) . $srcStr);
		}
		RedisUtil::init(RedisUtil::KEY_COS_SIGN)->setCache($signStr);
		$ret = [
			"Content-Type:multipart/form-data",
			"Host:" . $this->Host,
			'Authorization:' . $signStr,
		];
//		var_dump($ret);
		return $ret;
	}

}