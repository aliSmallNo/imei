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
	const CAT_NOTICE_IMAGE = 102;
	const CAT_HOME_HEADER = 110;
	const CAT_HOME_IMAGE = 120;
	const CAT_CHAT_HEADER = 130;

	/**
	 * @var \yii\db\Connection
	 */
	protected $conn = null;

	public static function init($cn = null)
	{
		$util = new self();
		if (!$cn) {
			$cn = AppUtil::db();
		}
		$util->conn = $cn;
		return $util;
	}

	public function add($cat, $raw, $expiredOn = '', $admin_id = 1)
	{
		$sql = "INSERT INTO im_cog(cCategory,cRaw,cExpiredOn,cAddedBy,cUpdatedBy) VALUES(:cat,:raw,:exp,:aid,:aid) ";
		$raw = is_array($raw) ? json_encode($raw, JSON_UNESCAPED_UNICODE) : $raw;
		$this->conn->createCommand($sql)->bindValues([
			':cat' => $cat,
			':exp' => $expiredOn,
			':raw' => $raw,
			':aid' => $admin_id
		])->execute();
		return $this->conn->getLastInsertID();
	}

	public function notices($page = 1, $pageSize = 20)
	{
		return $this->items(
			['cCategory IN (' . implode(',', [self::CAT_NOTICE_TEXT, self::CAT_NOTICE_IMAGE]) . ')'],
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
			[':cat' => self::CAT_HOME_IMAGE]);
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
		$sql = 'SELECT cId as id,cCategory as cat,cRaw as raw,
			cStatus as status,cExpiredOn as exp,cAddedOn as addon,cUpdatedOn as editon, a.aName as `name`
 			FROM im_cog as c
 			LEFT JOIN im_admin as a on c.cUpdatedBy=a.aId
 			WHERE cId>0 ' . $strCriteria . ' ORDER BY cUpdatedOn DESC ' . $limit;
		$ret = $this->conn->createCommand($sql)->bindValues($params)->queryAll();
		$items = [];
		foreach ($ret as $k => $item) {
			$active = ($item['status'] == 1);
			if (isset($item['exp']) && $item['exp']) {
				$active = ($item['exp'] >= date('Y-m-d'));
			}
			$raw = json_decode($item['raw'], 1);
			$item = array_merge($item, $raw);
			unset($item['raw']);
			$item['url'] = (isset($item['url']) ? $item['url'] : '');
			$item['title'] = (isset($item['title']) ? $item['title'] : '');
			$item['active'] = $active;
			$item['st'] = $active ? '' : '失效';
			$item['dt'] = AppUtil::prettyDate($item['editon']);
			$items[] = $item;
		}
		return $items;
	}
}