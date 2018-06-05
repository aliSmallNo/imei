<?php
/**
 * Created by PhpStorm.
 * User: zp
 * Date: 2018/04/10
 * Time: 12:22 PM
 */

namespace admin\controllers;


use admin\controllers\BaseController;
use admin\models\Admin;
use common\utils\YouzanUtil;

class YouzController extends BaseController
{

	public function actionSalesman()
	{
		// https://www.youzanyun.com/apilist/detail/group_ump/salesman/youzan.salesman.accounts.get
		Admin::staffOnly();
		$page = self::getParam("page", 1);

		$method = 'youzan.salesman.accounts.get';
		$params = [
			'page_no' => $page,
			'page_size' => 20,
		];

		$count = 0;
		$items = [];
		$res = YouzanUtil::getData($method, $params);
		if (isset($res['response'])) {
			$count = $res['response']['total_results'];
			$items = $res['response']['accounts'];
		}
		$pagination = self::pagination($page, $count);
		return $this->renderPage('salesman.tpl',
			[
				'page' => $page,
				'pagination' => $pagination,
				'items' => $items,
			]);
	}

	public function actionDetail()
	{
		Admin::staffOnly();
		$cid = self::getParam("id", 0);
		$postId = self::postParam("cid");
		if ($postId) {
			$cid = $postId;
			$images = [];
			$uploads = $_FILES["images"];
			if ($uploads && isset($uploads["tmp_name"]) && isset($uploads["error"])) {
				foreach ($uploads["tmp_name"] as $key => $file) {
					if ($uploads["error"][$key] == UPLOAD_ERR_OK) {
						$upName = $uploads["name"][$key];
						$fileExt = strtolower(pathinfo($upName, PATHINFO_EXTENSION));
						$images[] = ImageUtil::upload2Cloud($file, "", ($fileExt ? $fileExt : ""), 800);
						unlink($file);
					}
				}
			}
			CRMTrack::add($postId, [
				"status" => trim(self::postParam("status")),
				"note" => trim(self::postParam("note")),
				"image" => json_encode($images),
			], $this->admin_id);

		}
		list($items, $client) = CRMTrack::tracks($cid);
		$options = CRMClient::$StatusMap;
		foreach ($options as $key => $option) {
			$options[$key] = ($key - 100) . "% " . $option;
		}
		$isAssigner = Admin::isAssigner();
		if (!$isAssigner && !$client["bd"] && !in_array(Admin::getAdminId(), [])) {
			$len = strlen($client["phone"]);
			if ($len > 4) {
				$client["phone"] = substr($client["phone"], 0, $len - 4) . "****";
			}
		}
		return $this->renderPage('detail.tpl',
			[
				'detailcategory' => "crm/clients",
				'items' => $items,
				'client' => $client,
				"cid" => $cid,
				"options" => $options,
				"adminId" => $this->admin_id
			]);
	}


	public function actionStat()
	{
		Admin::staffOnly();
		$staff = Admin::getBDs(CRMClient::CATEGORY_YANXUAN);
		return $this->renderPage('stat.tpl',
			[
				"beginDate" => date("Y-m-d", time() - 15 * 86400),
				"endDate" => date("Y-m-d"),
				"staff" => $staff,
				"colors" => json_encode(array_values(CRMClient::$StatusColors))
			]);
	}
}