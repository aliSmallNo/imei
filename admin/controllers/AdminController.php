<?php


namespace admin\controllers;

use admin\models\Admin;
use admin\models\Menu;
use common\models\Redpacket;
use common\utils\AppUtil;
use common\utils\ImageUtil;
use common\utils\RedisUtil;


class AdminController extends BaseController
{
	public $layout = "main";

	/**
	 * 增加权限用户
	 * */
	public function actionUser()
	{
		Admin::staffOnly();
		$id = self::getParam("id");
		$bModify = strlen($id) > 0;
		$userInfo = [];
		if ($id) {
			$userInfo = Admin::findOne(["aId" => $id]);
			$userInfo = $userInfo->toArray();
			$userInfo["aFolders"] = json_decode($userInfo["aFolders"], true);
		}
		$rights = Menu::getRootMenu();
		if ($userInfo) {
			$include = $userInfo["aFolders"];
			foreach ($rights as $key => $menu) {
				$rights[$key]["checked"] = in_array($key, $include) ? "checked" : "";
			}
		} else {
			foreach ($rights as $key => $menu) {
				$rights[$key]["checked"] = $menu["branched"] ? "checked" : "";
			}
		}

		$levels = Admin::$accessLevels;

		$adminUserInfo = Admin::userInfo();
		//print_r($adminUserInfo);exit;

		if ($adminUserInfo['level'] < Admin::LEVEL_HIGH) {
			unset($levels[Admin::LEVEL_HIGH]);
			$adminUserInfo["aFolders"] = json_decode($adminUserInfo['aFolders'], true);
			foreach ($rights as $key => $value) {
				if (!in_array($key, $adminUserInfo["aFolders"])) {
					unset($rights[$key]);
				}
			}
		}
		return $this->renderPage('user.tpl',
			[
				'detailcategory' => $bModify ? 'admin/users' : "admin/user",
				'userInfo' => $userInfo,
				'rights' => $rights,
				'levels' => $levels,
				"id" => $id,
			]
		);
	}

	/**
	 * 用户列表
	 * */
	public function actionUsers()
	{
		Admin::staffOnly();

		$conn = AppUtil::db();
		$sql = 'select bId,bType,bResult from im_user_buzz where bType =\'voice\' and bResult like \'%amr\';';
		$ret = $conn->createCommand($sql)->queryAll();
		foreach ($ret as $row) {
			$id = $row['bId'];
			$fileName = AppUtil::catDir(true) . $row['bResult'];
			$fileMP3 = AppUtil::catDir(false, 'voice') . RedisUtil::getImageSeq() . '.mp3';
			exec('/usr/bin/ffmpeg -i ' . $fileName . ' -ab 12.2k -ar 16000 -ac 1 ' . $fileMP3, $out);
			$addr = ImageUtil::getUrl($fileMP3);
			$sql = 'update hd_user_buzz set bResult=:addr WHERE bId=:id ';
			$conn->createCommand($sql)->bindValues([
				':addr' => $addr,
				':id' => $id
			])->execute();
		}

		$page = self::getParam("page", 1);
		$name = self::getParam('name');
		$note = self::getParam('note');

		$status = Admin::STATUS_ACTIVE;
		$condition = " aStatus=$status ";
		if ($name) {
			$condition .= " and aLoginId like '%" . $name . "%'";
		}
		if ($note) {
			$condition .= " and aName like '%" . $note . "%'";
		}

		$count = Admin::getCountByCondition($condition);
		$list = Admin::getUsers($condition, $page, self::PAGE_SIZE);

		$pagination = self::pagination($page, $count);
		$menus = Menu::getRootMenu();
		return $this->renderPage('users.tpl',
			[
				"note" => $note,
				'list' => $list,
				'menus' => $menus,
				"name" => $name,
				'pagination' => $pagination,
			]);
	}

}
