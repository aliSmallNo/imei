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
	const CAT_IMAGE_MISC = 180;

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

	public function edit($data, $admin_id = 1)
	{
		$cid = isset($data['cId']) && $data['cId'] ? $data['cId'] : 0;
		unset($data['cId']);
		$modifyFlag = $cid > 0;
		$sql = $modifyFlag ? 'update im_cog set ' : 'insert into im_cog (';
		$sql2 = '';
		$params = [];
		foreach ($data as $key => $val) {
			$params[':' . $key] = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val;
			if ($modifyFlag) {
				$sql .= $key . '=:' . $key . ',';
			} else {
				$sql .= $key . ',';
				$sql2 .= ':' . $key . ',';
			}
		}
		$params[':cUpdatedBy'] = $admin_id;
		if ($modifyFlag) {
			$sql .= 'cUpdatedBy=:cUpdatedBy,cUpdatedOn=now() WHERE cId=' . $cid;
		} else {
			$sql .= 'cAddedBy,cUpdatedBy) Values(';
			$sql2 .= ':cUpdatedBy,:cUpdatedBy)';
			$sql .= $sql2;
		}
		$ret = $this->conn->createCommand($sql)->bindValues($params)->execute();
		return $ret;
	}

	public function notices($page = 1, $pageSize = 20)
	{
		return $this->items(
			['cCategory IN (' . implode(',', [self::CAT_NOTICE_TEXT, self::CAT_NOTICE_IMAGE]) . ')'],
			[],
			$page,
			$pageSize);
	}

	public function chatHeaders($activeOnly = false)
	{
		return self::figures(self::CAT_CHAT_HEADER, $activeOnly);
	}

	public function homeHeaders($activeOnly = false)
	{
		return self::figures(self::CAT_HOME_HEADER, $activeOnly);
	}

	public function homeFigures($activeOnly = false)
	{
		return self::figures(self::CAT_HOME_IMAGE, $activeOnly);
	}

	public function miscFigures($activeOnly = false)
	{
		return self::figures(self::CAT_IMAGE_MISC, $activeOnly, 1, 20);
	}

	protected function figures($cat, $activeOnly = false, $page = 1, $pageSize = 100)
	{
		return $this->items(
			['cCategory=:cat ' . ($activeOnly ? ' AND cStatus=1' : '')],
			[':cat' => $cat],
			$page,
			$pageSize);
	}

	protected function items($criteria = [], $params = [], $page = 1, $pageSize = 100)
	{
		$strCriteria = '';
		if ($criteria) {
			$strCriteria = ' AND ' . implode(' AND ', $criteria);
		}
		$limit = ' LIMIT ' . ($page - 1) * $pageSize . ',' . $pageSize;

		$sql = 'SELECT cId as id,cCategory as cat,cRaw as raw,cCount as `count`,
			cStatus as status, cExpiredOn as exp, cAddedOn as addon, cUpdatedOn as editon, a.aName as `name`
 			FROM im_cog as c
 			LEFT JOIN im_admin as a on c.cUpdatedBy=a.aId
 			WHERE cStatus<2 ' . $strCriteria . ' ORDER BY cExpiredOn desc, cUpdatedOn DESC ' . $limit;
		$ret = $this->conn->createCommand($sql)->bindValues($params)->queryAll();
		$items = [];
		foreach ($ret as $k => $item) {
			$active = ($item['status'] == 1 && isset($item['exp']) && $item['exp'] >= date('Y-m-d'));
			$raw = json_decode($item['raw'], 1);
			unset($raw['count']);
			$item = array_merge($item, $raw);
			unset($item['raw']);
			$item['url'] = (isset($item['url']) ? $item['url'] : '');
			$item['title'] = (isset($item['title']) ? $item['title'] : '');
			$item['content'] = (isset($item['content']) ? $item['content'] : '');
			$item['active'] = $active ? 1 : 0;
			$item['st'] = $active ? '有效' : '失效';
			$item['dt'] = AppUtil::miniDate($item['editon']);
			$items[] = $item;
		}
		return $items;
	}
}
