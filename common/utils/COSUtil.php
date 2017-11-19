<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 18/11/2017
 * Time: 6:36 PM
 */

namespace common\utils;


class COSUtil
{
	protected $uploadPath;
	protected $uploadType;
	protected $uploadBaseName;
	protected $uploadExtension;

	const UPLOAD_URL = 100;
	const UPLOAD_PATH = 110;
	const UPLOAD_MEDIA = 120;

	public static function init($strPath, $name = '', $extension = '')
	{
		$util = new self();
		$util->uploadPath = $strPath;
		$util->uploadType = self::UPLOAD_URL;
		if (strpos($strPath, 'http') === false) {
			$util->uploadType = self::UPLOAD_PATH;
		}
		$util->uploadBaseName = $name;
		if (!$name) {
			$util->uploadBaseName = pathinfo($strPath, PATHINFO_BASENAME);
		}
		$util->uploadExtension = $extension;
		if (!$extension) {
			$util->uploadExtension = pathinfo($strPath, PATHINFO_EXTENSION);
		}
		return $util;
	}

	protected function read()
	{
		
	}

}