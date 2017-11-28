<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 27/11/2017
 * Time: 1:52 PM
 */

namespace common\service;

use common\models\User;
use common\utils\ImageUtil;

class UserService
{
	public $id = 0;
	public $open_id = '';
	public $uni = '';
	public $name = '';
	public $phone = '';
	public $thumb = '';
	public $avatar = '';
	public $cert_status = 0;
	public $cert_front = '';
	public $cert_hold = '';

	public static function init($uid)
	{
		$util = new self();
		$util->id = $uid;
		$uInfo = User::findOne(['uId' => $uid]);
		if ($uInfo) {
			$uInfo = $uInfo->toArray();
			$util->open_id = $uInfo['uOpenId'];
			$util->name = $uInfo['uName'];
			$util->uni = $uInfo['uUniqid'];
			$util->phone = $uInfo['uPhone'];
			$util->thumb = ImageUtil::getItemImages($uInfo['uThumb'])[0];
			$util->avatar = ImageUtil::getItemImages($uInfo['uAvatar'])[0];
			$util->cert_status = $uInfo['uCertStatus'];
			$certs = User::getCerts($uInfo['uCertImage']);
			foreach ($certs as $item) {
				if ($item['tag'] == 'zm') {
					$util->cert_front = $item['url'];
				} elseif ($item['tag'] == 'sc') {
					$util->cert_hold = $item['url'];
				}
			}
		}
		return $util;
	}

	public function hasCert()
	{
		return ($this->cert_status == User::CERT_STATUS_PASS
			&& $this->cert_front && $this->cert_hold);
	}
}