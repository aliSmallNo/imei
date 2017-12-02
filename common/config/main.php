<?php

return [
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
	'components' => [
		'cache' => [
			'class' => 'yii\caching\FileCache',
		],
		'session' => [
			'class' => 'yii\web\DbSession',
			'sessionTable' => 'im_session',
		],
	],
];
