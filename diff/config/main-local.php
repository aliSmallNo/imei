<?php
return [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'xJfQY02qpOU7WATQEb0zNTUKXAidQqpV'
        ],
	    'db' => [
		    'class' => 'yii\db\Connection',
		    'tablePrefix' => 'df_',
		    'username' => 'cdb_dev',
		    'password' => 'bNbAuqjCi58y8sFg',
		    'dsn' => 'mysql:host=599c25ad1f53d.bj.cdb.myqcloud.com;port=5210;dbname=diff',
	    ],
	    'redis' => [
		    'class' => 'yii\redis\Connection',
		    'hostname' => '127.0.0.1',
		    'port' => 6379
	    ],
    ],
];
