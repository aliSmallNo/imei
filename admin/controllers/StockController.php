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
use common\models\CRMStockClient;
use common\models\CRMStockTrack;
use common\utils\ImageUtil;

class StockController extends BaseController
{

	public function actionClients()
	{
		Admin::staffOnly();
		$page = self::getParam("page", 1);
		$name = trim(self::getParam("name"));
		$prov = trim(self::getParam("prov"));
		$phone = trim(self::getParam("phone"));
		$dt1 = trim(self::getParam("dt1"));
		$dt2 = trim(self::getParam("dt2"));
		$cat = trim(self::getParam("cat"), "my");
		$sort = self::getParam("sort", "dd");
		$bdassign = self::getParam("bdassign");
		$perSize = 20;

		$criteria = [" cCategory=" . CRMStockClient::CATEGORY_YANXUAN];
		$params = [];
		$urlParams = [];
		$alert = [];
		if ($dt1) {
			$criteria[] = " cAddedDate >= :dt1";
			$params[":dt1"] = $dt1;
			$urlParams[] = "dt1=" . $dt1;
			$alert[] = "【" . $dt1 . "】";
		}
		if ($dt2) {
			$criteria[] = " cAddedDate <= :dt2";
			$params[":dt2"] = $dt2 . ' 23:55:00';
			$urlParams[] = "dt2=" . $dt2;
			$alert[] = "【" . $dt2 . "】";
		}
		if ($prov) {
			$criteria[] = " (cProvince like :prov or cCity like :prov)";
			$params[":prov"] = "%" . $prov . "%";
			$urlParams[] = "prov=" . $prov;
			$alert[] = "【" . $prov . "】";
		}
		if ($name) {
			$criteria[] = " cName like :name";
			$params[":name"] = "%" . $name . "%";
			$urlParams[] = "name=" . $name;
			$alert[] = "【" . $name . "】";
		}
		if ($phone) {
			$criteria[] = "cPhone like :phone";
			$params[":phone"] = $phone . "%";
			$urlParams[] = "phone=" . $phone;
			$alert[] = "【" . $phone . "】";
		}
		if ($bdassign) {
			$criteria[] = " cBDAssign=" . $bdassign;
			$urlParams[] = "bdassign=" . $bdassign;
			$uInfo = Admin::findOne(["aId" => $bdassign]);
			$alert[] = "【" . $uInfo["aName"] . "】";
		}
		$counters = CRMStockClient::counts($this->admin_id, $criteria, $params);
		$isAssigner = Admin::isAssigner();
		$tabs = [
			"my" => [
				"title" => "我的客户",
				"count" => $counters["mine"]
			],
			"sea" => [
				"title" => "公海客户",
				"count" => $counters["sea"]
			]
		];
		if ($isAssigner) {
			$tabs = [
				"my" => [
					"title" => "我的客户",
					"count" => $counters["mine"]
				],
				"sea" => [
					"title" => "公海客户",
					"count" => $counters["sea"]
				],
				"all" => [
					"title" => "全部客户",
					"count" => $counters["cnt"]
				],
			];
		}
		if (!isset($tabs[$cat])) {
			$cat = "my";
		}

		if ($cat == "my") {
			$criteria[] = " cBDAssign =" . $this->admin_id;
		} elseif ($cat == "sea") {
			$criteria[] = " cBDAssign=0 ";
		}

		list($items, $count) = CRMStockClient::clients($criteria, $params, $sort, $page, $perSize);

		$alertMsg = "";
		if ($alert) {
			$alertMsg = "搜索" . implode("，", $alert) . "，结果如下";
		}
		$pagination = self::pagination($page, $count);
		$sources = CRMStockClient::$SourceMap;

		$bdDefault = $isAssigner ? "" : $this->admin_id;

		if (!$isAssigner && !in_array(Admin::getAdminId(), [2132486, 2136435])) {// huangxin panyue
			foreach ($items as $key => $item) {
				if ($item["cBDAssign"]) {
					continue;
				}
				$len = strlen($item["cPhone"]);
				if ($len > 4) {
					$items[$key]["cPhone"] = substr($item["cPhone"], 0, $len - 4) . "****";
				}
				$len = strlen($item["cWechat"]);
				if ($len > 4) {
					$items[$key]["cWechat"] = substr($item["cWechat"], 0, $len - 4) . "****";
				}
			}
		}

		$sorts = [
			"dd" => ["da", "fa-angle-double-down"],
			"da" => ["dd", "fa-angle-double-up"],
			"sd" => ["sa", "fa-angle-double-down"],
			"sa" => ["sd", "fa-angle-double-up"],
		];
		if (in_array($sort, ["dd", "da"])) {
			list($dNext, $dIcon) = isset($sorts[$sort]) ? $sorts[$sort] : $sorts["dd"];
			list($sNext, $sIcon) = ["sd", "fa-angle-double-down"];
		} else {
			list($dNext, $dIcon) = ["dd", "fa-angle-double-down"];
			list($sNext, $sIcon) = isset($sorts[$sort]) ? $sorts[$sort] : $sorts["sd"];
		}

		return $this->renderPage('clients.tpl',
			[
				'detailcategory' => self::getRequestUri(),
				'items' => $items,
				"strItems" => json_encode($items),
				'page' => $page,
				'pagination' => $pagination,
				"alertMsg" => $alertMsg,
				"urlParams" => implode("&", $urlParams),
				"name" => $name,
				"phone" => $phone,
				"prov" => $prov,
				"dt1" => $dt1,
				"dt2" => $dt2,
				"cat" => $cat,
				"tabs" => $tabs,
				"staff" => Admin::getStaffs(),
				"bds" => Admin::getBDs(CRMStockClient::CATEGORY_YANXUAN),
				"bdassign" => $bdassign,
				"sources" => $sources,
				"bdDefault" => $bdDefault,
				'isAssigner' => $isAssigner,
				"sort" => $sort,
				"sNext" => $sNext,
				"sIcon" => $sIcon,
				"dNext" => $dNext,
				"dIcon" => $dIcon,
				"ageMap" => CRMStockClient::$ageMap,
				"stock_age_map" => CRMStockClient::$stock_age_map,
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
			CRMStockTrack::add($postId, [
				"status" => trim(self::postParam("status")),
				"note" => trim(self::postParam("note")),
				"image" => json_encode($images),
			], $this->admin_id);

		}
		list($items, $client) = CRMStockTrack::tracks($cid);
		$options = CRMStockClient::$StatusMap;
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
				'detailcategory' => "stock/clients",
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
		$staff = Admin::getBDs(CRMStockClient::CATEGORY_YANXUAN);
		return $this->renderPage('stat.tpl',
			[
				"beginDate" => date("Y-m-d", time() - 15 * 86400),
				"endDate" => date("Y-m-d"),
				"staff" => $staff,
				"colors" => json_encode(array_values(CRMStockClient::$StatusColors))
			]);
	}


}