<?php

return [
	'timeZone' => 'Asia/Shanghai',
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'components' => [
		'cache' => [
			'class' => 'yii\caching\FileCache',
			/*'class' => 'yii\caching\DbCache',
			'db' => 'imei',
			'cacheTable' => 'cache',*/

		],
		/*'session' => [
	'class' => 'yii\web\DbSession',
	'sessionTable' => 'im_session',
	'timeout' => 50
	],*/
	],
	'bootstrap' => ['gii'],
	'modules' => [
		'gii' => [
			'class' => 'yii\gii\Module',
		],
	],
];
