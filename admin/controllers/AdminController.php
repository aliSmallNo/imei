<?php


namespace admin\controllers;

use admin\models\Admin;
use admin\models\Menu;
use common\utils\RedisUtil;
use common\utils\ResponseUtil;


class AdminController extends BaseController
{



	/**
	 * 增加权限用户
	 *
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

	public function actionEdituser()
	{
		$tag = self::postParam("tag");
		$tag = strtolower($tag);
		$id = self::postParam("id");
		$ret = ["code" => 159, "msg" => self::ICON_ALERT_HTML . "无操作！"];
		switch ($tag) {
			case "edit":
				$name = self::postParam("name");
				$pass = self::postParam("pass");

				$data = [
					"aId" => $id ? $id : 0,
					"aLoginId" => $name,
					"aPass" => md5(strtolower($pass)),
					"aFolders" => self::postParam("rights"),
					"aLevel" => self::postParam("level"),
					"aName" => self::postParam("note"),
					"aPhone" => self::postParam("phone"),
				];
				if ($id && !$pass) {
					unset($data['aPass']);
				}
				$aId = Admin::saveUser($data);
				$msg = "";
				if ($aId) {
					if ($id) {
						Admin::clearById($id);
						$msg = self::ICON_OK_HTML . "修改用户" . $name . "成功! ";
					} else {
						$msg = self::ICON_OK_HTML . "添加用户" . $name . "成功! ";
					}
				}
				$ret = ["code" => 0, "msg" => $msg];
				break;
			case "delete":
				$result = Admin::checkAccessLevel(Admin::LEVEL_HIGH, true);
				if ($result) {
					$ret = Admin::remove($id, Admin::getAdminId());
				} else {
					$ret = ["code" => 159, "msg" => self::ICON_ALERT_HTML . "无操作权限！"];
				}
				break;

		}
		\Yii::$app->response->format = 'json';

		return ResponseUtil::renderAPI($ret["code"], $ret["msg"]);
	}

	/**
	 * 用户列表
	 * */
	public function actionUsers()
	{
		Admin::staffOnly();
		$perSize = 20;
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
		$list = Admin::getUsers($condition, $page, $perSize);

		//$pagination = Utils::createPagination($page, $perSize, $count);
		$pages = new \yii\data\Pagination(['totalCount' => $count, 'pageSize' => $perSize]);
		$pages->setPage($page - 1);
		$res = \yii\widgets\LinkPager::widget(['pagination' => $pages]);
		$pagination = str_replace('<ul class="pagination">', '<div class="dataTables_paginate paging_simple_numbers"><ul class="pagination">', $res);
		$pagination = mb_ereg_replace('&laquo;', '<i class="fa fa-angle-double-left"></i>', $pagination);
		$pagination = mb_ereg_replace('&raquo;', '<i class="fa fa-angle-double-right"></i>', $pagination);

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
