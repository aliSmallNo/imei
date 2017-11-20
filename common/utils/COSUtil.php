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
	protected $resPath;
	protected $resSavedPath;
	protected $resType;
	protected $resRename;
	protected $resExtension;
	protected $uploadFolder;

	const UPLOAD_URL = 100;
	const UPLOAD_PATH = 110;
	const UPLOAD_MEDIA = 120;

	private $App_Id = '10063905';
	private $Bucket = 'imei';
	private $Secret_Id = 'AKIDEqyJNctINqB6re8NBeckX0wOH2CnGL0R';
	private $Secret_Key = 'At5h3sa9zKz8rsSMVqPUMN4L48uHNfNk';
	private $Host = "web.file.myqcloud.com";

	public static function init($resType, $resPath)
	{
		$util = new self();
		$util->resPath = $resPath;
		$util->resType = $resType;
		$util->resRename = date('Ymd') . (1000001 + RedisUtil::getImageSeq());
		$util->resSavedPath = $util->save2Local();
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
				$this->resExtension = pathinfo($this->resPath, PATHINFO_EXTENSION);
				$this->resRename .= '.' . $this->resExtension;
				$content = file_get_contents($this->resPath);
				break;
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


	public function upload($thumbFlag = false, $squareFlag = false)
	{
		$data = [
			'op' => 'upload',
			"insertOnly" => 0
		];
		$srcPath = $this->resSavedPath;
		// Rain: 对图片做压缩
		if ($this->uploadFolder == 'image') {
			$thumbSide = 200;
			$defaultSide = 680;
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

		$url = $this->getUrl() . "/" . $this->resRename;
		var_dump($url);
		$temp = $data;
		unset($temp['filecontent']);
		var_dump($temp);
		$ret = $this->curlUpload($url, $data);
		$ret = json_decode($ret, true);
		return isset($ret['data']['access_url']) ? $ret['data']['access_url'] : json_encode($ret);
	}

	protected function curlUpload($url, $data)
	{
		$method = "POST";
		$field = "/" . $this->App_Id . "/" . $this->Bucket . "/" . $this->uploadFolder . "/" . $this->resRename;
		$header = $this->getHeader();
		$curlHandler = curl_init();
		curl_setopt($curlHandler, CURLOPT_URL, $url);
		$method = strtoupper($method);
		$header = isset($header) ? $header : [];
		$header[] = 'Method:' . $method;

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
	 * @param string $oneTimeField
	 * @param bool $resetFlag
	 * @return array
	 */
	protected function getHeader($oneTimeField = '', $resetFlag = false)
	{
		$signStr = '';
		if (!$oneTimeField) {
			$redis = RedisUtil::init(RedisUtil::KEY_COS_SIGN);
			$signStr = $redis->getCache();
		}
		if ($oneTimeField || $resetFlag || !$signStr) {
			$current = time();
			$expired = $current + 86400 + 120;
			if ($oneTimeField) {
				$expired = 0;
			}
			$rnd = mt_rand(1000001, 9999999);
			$srcStr = "a=%s&b=%s&k=%s&e=%s&t=%s&r=%s&f=%s";
			$srcStr = sprintf($srcStr, $this->App_Id, $this->Bucket, $this->Secret_Id,
				$current, $rnd, $expired, $oneTimeField);
			$bin = hash_hmac('SHA1', $srcStr, $this->Secret_Key, true);
			$bin .= $srcStr;
			$signStr = base64_encode($bin);
		}
		if (!$oneTimeField) {
			RedisUtil::init(RedisUtil::KEY_COS_SIGN)->setCache($signStr);
		}
		$ret = [
			"Content-Type:multipart/form-data",
			"Host:" . $this->Host,
			'Authorization:' . $signStr,
		];
//		var_dump($ret);
		return $ret;
	}

}