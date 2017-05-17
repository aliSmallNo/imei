<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 9:38 AM
 */

namespace mobile\controllers;


class SiteController extends BaseController
{
	public function actionError()
	{
		return self::renderPage('error.tpl');
	}
}