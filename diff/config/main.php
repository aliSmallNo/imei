<?php

$params = require 'params-local.php';

return [
	'id' => 'app-diff',
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'diff\controllers',
	'bootstrap' => ['log'],
	'modules' => [],
	'language' => 'zh-cmn-Hans',
	'charset' => 'utf-8',
	'components' => [
		'request' => [
			'csrfParam' => '_csrf-mobile',
			'enableCookieValidation' => false,
			'enableCsrfValidation' => false,
		],
		'cache' => [
			'class' => 'yii\caching\FileCache',
		],
		'user' => [
			'identityClass' => 'diff\models\User',
			'enableAutoLogin' => true,
			'identityCookie' => ['name' => '_identity-mobile', 'httpOnly' => true],
		],
		'session' => [
			// this is the name of the session cookie used for login on the backend
			'name' => 'advanced-mobile',
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'view' => [
			'renderers' => [
				'tpl' => [
					'class' => 'yii\smarty\ViewRenderer',
					'cachePath' => '@mobile/runtime/Smarty/cache',
					'options' => [
						'left_delimiter' => '{{',
						'right_delimiter' => '}}',
					],
				],
			],
		],
		'errorHandler' => [
			'errorAction' => 'api/error',
		],
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => [

			],
		],
	],
	'params' => $params,
];
