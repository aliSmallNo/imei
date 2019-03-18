<?php

$vendorDir = dirname(__DIR__);

return array(
	'yiisoft/yii2-bootstrap' =>
		array(
			'name' => 'yiisoft/yii2-bootstrap',
			'version' => '2.0.6.0',
			'alias' =>
				array(
					'@yii/bootstrap' => $vendorDir . '/yiisoft/yii2-bootstrap',
				),
		),
	'yiisoft/yii2-gii' =>
		array(
			'name' => 'yiisoft/yii2-gii',
			'version' => '2.0.5.0',
			'alias' =>
				array(
					'@yii/gii' => $vendorDir . '/yiisoft/yii2-gii',
				),
		),
	'yiisoft/yii2-faker' =>
		array(
			'name' => 'yiisoft/yii2-faker',
			'version' => '2.0.3.0',
			'alias' =>
				array(
					'@yii/faker' => $vendorDir . '/yiisoft/yii2-faker',
				),
		),
	'yiisoft/yii2-debug' =>
		array(
			'name' => 'yiisoft/yii2-debug',
			'version' => '2.0.9.0',
			'alias' =>
				array(
					'@yii/debug' => $vendorDir . '/yiisoft/yii2-debug',
				),
		),
	'yiisoft/yii2-redis' =>
		array(
			'name' => 'yiisoft/yii2-redis',
			'version' => '2.0.6.0',
			'alias' =>
				array(
					'@yii/redis' => $vendorDir . '/yiisoft/yii2-redis',
				),
		),
	'yiisoft/yii2-smarty' =>
		array(
			'name' => 'yiisoft/yii2-smarty',
			'version' => '2.0.6.0',
			'alias' =>
				array(
					'@yii/smarty' => $vendorDir . '/yiisoft/yii2-smarty',
				),
		),
	'2amigos/yii2-qrcode-helper' =>
		array(
			'name' => '2amigos/yii2-qrcode-helper',
			'version' => '1.0.2.0',
			'alias' =>
				array(
					'@dosamigos/qrcode' => $vendorDir . '/2amigos/yii2-qrcode-helper/src',
				),
		),
	'yiisoft/yii2-sphinx' =>
		array(
			'name' => 'yiisoft/yii2-sphinx',
			'version' => '2.0.8.0',
			'alias' =>
				array(
					'@yii/sphinx' => $vendorDir . '/yiisoft/yii2-sphinx',
				),
		),
);
