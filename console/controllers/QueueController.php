<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 26/5/2017
 * Time: 7:07 PM
 */

namespace console\controllers;

use console\lib\beanstalkSocket;
use console\utils\QueueUtil;
use yii\base\Exception;
use yii\console\Controller;

class QueueController extends Controller
{
	/**
	 * 后台监听beanstalk的worker
	 * @param $tube string 监听的tube名称
	 * @return void
	 */
	public function actionTask($tube = '')
	{
		try {
			$beanstalk = new beanstalkSocket(QueueUtil::$QueueConfig);
			if (!$beanstalk->connect()) {
				QueueUtil::logFile('beanstalk disconnect!', __FUNCTION__, __LINE__);
				return;
			}
			if (!$tube) {
				$tube = QueueUtil::QUEUE_TUBE;
			}
			QueueUtil::logFile('beanstalk connected ', __FUNCTION__, __LINE__);
			$beanstalk->useTube($tube);
			$beanstalk->watch($tube);
			$beanstalk->ignore('default');
			while (1) {
				$job = $beanstalk->reserve();
				QueueUtil::logFile($job, __FUNCTION__, __LINE__);
				$jobId = $job['id'];
				$jobBody = json_decode($job['body'], 1);
				$method = $jobBody['consumer'];
				$params = $jobBody['params'];
				if (method_exists(QueueUtil::class, $method)) {
					$result = QueueUtil::$method($params);
					QueueUtil::logFile($method . ' result: ' . $result, __FUNCTION__, __LINE__);
					if ($result) {
						$beanstalk->delete($jobId);
					} else {
						$beanstalk->bury($jobId, 40);
					}
				} else {
					QueueUtil::logFile(' QueueUtil 中没找到方法 ' . $method, __FUNCTION__, __LINE__);
					$beanstalk->delete($jobId);
				}
				sleep(1);
			}
			$beanstalk->disconnect();
		} catch (Exception $ex) {
			$msg = $ex->getMessage();
			QueueUtil::logFile($msg, __FUNCTION__, __LINE__);
		}
	}

}