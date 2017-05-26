<?php
return [
	'components' => [
		'db' => [
			'class' => 'yii\db\Connection',
			'tablePrefix' => 'im_',
			'username' => 'cdb_outerroot',
			'password' => 'y1sVt0vB7Zyn9ssJ',
			'dsn' => 'mysql:host=57cd732c929ed.bj.cdb.myqcloud.com;port=5704;dbname=imei',
		],
		'redis' => [
			'class' => 'yii\redis\Connection',
			'hostname' => '127.0.0.1',
			'port' => 6379
		],
		'sphinx' => [
			'class' => 'yii\sphinx\Connection',
			'dsn' => 'mysql:host=127.0.0.1;port=9306;'
		]
	],
];
