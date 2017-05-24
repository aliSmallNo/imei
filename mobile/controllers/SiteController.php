<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 9:38 AM
 */

namespace mobile\controllers;


use Yii;

class SiteController extends BaseController
{

	public function actionError()
	{
		$exception = Yii::$app->errorHandler->exception;
		if($exception){
			var_dump($exception);
			exit();
		}
		return self::renderPage('error.tpl');
	}
}