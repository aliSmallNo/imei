<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 11/5/2017
 * Time: 9:38 AM
 */

namespace mobile\controllers;


use common\models\UserQR;
use Yii;

class SiteController extends BaseController
{

	public function actionError()
	{
		$exception = Yii::$app->errorHandler->exception;
		if ($exception) {
			var_dump($exception);
			exit();
		}
		return self::renderPage('error.tpl');
	}

	public function actionShare()
	{
		$uId = self::getParam('id');
		$city = self::getParam('city', '盐城');
		$cls = 'small';
		$bgSrc = '';
		$preview = 0;
		if ($uId) {
			$bgSrc = UserQR::mpShareQR($uId);
			$cls = $preview ? '' : 'big';
		}
		return self::renderPage('share.tpl',
			[
				'preview' => $preview,
				'bgSrc' => $bgSrc,
				'stars' => UserQR::$SuperStars,
				'cls' => $cls,
				'city' => $city,
				'userId' => $uId
			],
			'terse',
			'微媒100',
			'bg-main');
	}
}