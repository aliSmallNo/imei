<?php

namespace admin\controllers;

use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{
//	public $layout = "main";

	/**
	 * Displays homepage.
	 *
	 * @return string
	 */
	public function actionIndex()
	{
		return self::render('index.tpl', [
			"test" => "First test!!!"
		]);
	}

	/**
	 * Login action.
	 *
	 * @return string
	 */
	public function actionLogin()
	{
		return $this->render('login.tpl', [
			"items" => ["aaaa", "bbbb", "cccc", "DDDD"]
		]);
	}

}
