<?php
$env = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : 'prod';
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', $env);
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));
require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/common/config/bootstrap.php');
require(__DIR__ . '/console/config/bootstrap.php');
$config = yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/common/config/main.php'),
	require(__DIR__ . '/common/config/main-local.php'),
	require(__DIR__ . '/console/config/main.php')
);
$tube = $_SERVER['argv'][1];
$_SERVER['argv'] = [];
$_SERVER['argv'][0] = '';
$_SERVER['argv'][1] = 'queue/task';
$_SERVER['argv'][2] = $tube;
$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
