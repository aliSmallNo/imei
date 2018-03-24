<?php
/**
 * Created by PhpStorm.
 * User: b_tt
 * Date: 18/3/19
 * Time: 16:53
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
	require(__DIR__ . '/../config/main.php'),
	require(__DIR__ . '/../config/main-local.php')
);

(new yii\web\Application($config))->run();