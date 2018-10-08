<?php

$params = array_merge(
	require(__DIR__ . '/../../common/config/params.php'),
	require(__DIR__ . '/../../common/config/params-local.php'),
	require(__DIR__ . '/params.php')
);

return [
	'id' => 'app-admin',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'admin\controllers',
	'bootstrap' => ['log'],
	'modules' => [],
	'language' => 'zh-cmn-Hans',
	'charset' => 'utf-8',
	'components' => [
		'request' => [
			'csrfParam' => '_csrf-admin',
			'enableCookieValidation' => false,
			'enableCsrfValidation' => false
		],
		'user' => [
			'identityClass' => 'common\models\User',
			'enableAutoLogin' => true,
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
					'cachePath' => '@admin/runtime/Smarty/cache',
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
				'/open/site/summary' => '/site/summary',
			],
		],
	],
	'params' => $params,
];
