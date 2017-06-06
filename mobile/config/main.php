<?php

$params = array_merge(
	require(__DIR__ . '/../../common/config/params.php'),
	require(__DIR__ . '/../../common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);


return [
	'id' => 'app-mobile',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'mobile\controllers',
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
		'user' => [
			'identityClass' => 'common\models\User',
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
			'errorAction' => 'site/error',
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
