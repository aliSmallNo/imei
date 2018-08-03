<?php


namespace admin\controllers;

use admin\models\Admin;
use admin\models\Menu;
use common\utils\WechatUtil;


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

		$page = self::getParam("page", 1);
		$name = self::getParam('name');
		$note = self::getParam('note');
		$tag = self::getParam('tag');

		$status = Admin::STATUS_ACTIVE;
		$condition = " aStatus=$status ";
		if ($name) {
			$condition .= " and aLoginId like '%" . $name . "%'";
		}
		if ($note) {
			$condition .= " and aName like '%" . $note . "%'";
		}

		if ($tag) {
			if ($tag == "finance") {
				$condition .= " and aIsFinance =1 ";
			}
			if ($tag == "operator") {
				$condition .= " and aIsOperator =1 ";
			}
			if ($tag == "apply") {
				$condition .= " and aIsApply =1 ";
			}
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
				"tag" => $tag,
				'pagination' => $pagination,
			]);
	}

	public function actionMedia()
	{
		$page = self::getParam("page", 1);
		$type = self::getParam("type", 'image');
		list($items, $count) = WechatUtil::getMedia($type, $page);
		$pagination = self::pagination($page, $count);
		$tabs = [
			['key' => 'image', 'title' => '图片'],
			['key' => 'voice', 'title' => '声音'],
			['key' => 'video', 'title' => '视频'],
		];
		return $this->renderPage('media.tpl',
			[
				'tabs' => $tabs,
				'type' => $type,
				'items' => $items,
				'pagination' => $pagination,
			]);
	}

	public function actionCalcList()
	{
		$items = [];
		$preLeft = 0;
		for ($k = 1; $k < 9999; $k++) {
			$left = mt_rand(2, 20);
			if ($left == $preLeft) {
				$left = mt_rand(2, 20);
			}
			$preLeft = $left;
			$op = mt_rand(0, 1) ? '+' : '-';
			$right = ($op == '+' ? mt_rand(0, 20 - $left) : mt_rand(0, $left));
			if ($right < 1) {
				$right = ($op == '+' ? mt_rand(0, 20 - $left) : mt_rand(0, $left));
			}
			$key = $left . $op . $right;
			$items[$key] = [
				'left' => $left,
				'op' => $op,
				'right' => $right,
			];
			if (count($items) >= 50) {
				break;
			}
		}
		$items = array_values($items);
		return $this->renderPage('calc-list.tpl',
			[
				'items' => $items,
			]);
	}

}
