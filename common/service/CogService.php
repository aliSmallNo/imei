<?php
/**
 * Created by PhpStorm.
 * User: weirui
 * Date: 30/11/2017
 * Time: 11:20 AM
 */

namespace common\service;


use common\utils\AppUtil;

class CogService
{
	const CAT_NOTICE_TEXT = 100;
	const CAT_NOTICE_FIGURE = 102;
	const CAT_HOME_HEADER = 110;
	const CAT_HOME_FIGURE = 120;
	const CAT_CHAT_HEADER = 130;

	/**
	 * @var \yii\db\Connection
	 */
	protected $conn = null;

	public static function init()
	{
		$util = new self();
		$util->conn = AppUtil::db();
		return $util;
	}

	public function add($cat, $content, $url, $admin_id = 1, $idx = 1, $count = 0, $expiredOn = '')
	{
		$sql = "INSERT INTO im_cog(cCategory,cUrl,cCount,cExpiredOn,cContent,cIndex,cAddedBy)
				VALUES(:cat,:url,:cnt,:exp,:content,:idx,:aid) ";
		$content = is_array($content) ? json_encode($content, JSON_UNESCAPED_UNICODE) :
			json_encode([$content], JSON_UNESCAPED_UNICODE);
		$this->conn->createCommand($sql)->bindValues([
			':cat' => $cat,
			':url' => $url,
			':cnt' => $count,
			':exp' => $expiredOn,
			':content' => $content,
			':idx' => $idx,
			':aid' => $admin_id
		])->execute();
		return $this->conn->getLastInsertID();
	}

	public function notices($page = 1, $pageSize = 20)
	{
		return $this->items(
			['cCategory IN (' . implode(',', [self::CAT_NOTICE_TEXT, self::CAT_NOTICE_FIGURE]) . ')'],
			[],
			$page,
			$pageSize);
	}

	public function chatHeaders()
	{
		return $this->items(
			['cCategory=:cat AND cStatus=1'],
			[':cat' => self::CAT_CHAT_HEADER]);
	}

	public function homeHeaders()
	{
		return $this->items(
			['cCategory=:cat AND cStatus=1'],
			[':cat' => self::CAT_HOME_HEADER]);
	}

	public function homeFigures()
	{
		return $this->items(
			['cCategory=:cat AND cStatus=1'],
			[':cat' => self::CAT_HOME_FIGURE]);
	}

	protected function items($criteria = [], $params = [], $page = 1, $pageSize = 100)
	{
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$limit = ' LIMIT ' . ($page - 1) * $pageSize . ',' . $pageSize;
		$params = array_merge($params, [
			':cat' => self::CAT_NOTICE_TEXT
		]);
		$sql = 'SELECT cId as id,cCategory as cat,cUrl as url,cCount as cnt,cContent as content,cTitle as title,cIndex as idx,
			cStatus as status,cExpiredOn as exp,cAddedOn as addon,cUpdatedOn as editon, a.aName as `name`
 			FROM im_cog as c
 			LEFT JOIN im_admin as a on c.cUpdatedBy=a.aId
 			WHERE cId>0 ' . $strCriteria . ' ORDER BY cRank, cUpdatedOn DESC ' . $limit;
		$ret = $this->conn->createCommand($sql)->bindValues($params)->queryAll();
		foreach ($ret as $k => $item) {
			$active = ($item['status'] == 1);
			if (isset($item['exp'])) {
				$active = ($item['exp'] > date('Y-m-d'));
			}
			$ret[$k]['content'] = json_decode($item['content'], 1);
			$ret[$k]['active'] = $active;
			$ret[$k]['st'] = $active ? '' : '失效';
			$ret[$k]['dt'] = AppUtil::prettyDate($item['editon']);
		}
		return $ret;
	}
}