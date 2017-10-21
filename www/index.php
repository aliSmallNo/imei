<?php

$postParam = function ($field, $defaultVal = "") {
	return isset($_POST[$field]) ? trim($_POST[$field]) : $defaultVal;
};

$postUrl = function ($url, $data = [], $flag = false) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	if ($flag) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);

	if ($data) {
		curl_setopt($ch, CURLOPT_POST, 1);
		$data = http_build_query($data);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}
	$ret = curl_exec($ch);
	curl_close($ch);
	return $ret;
};

$data = [];
$fields = ["phone", "mess"];
foreach ($fields as $field) {
	$data[$field] = trim($postParam($field));
}

$postUrl("https://admin.meipo100.com/api/source", $data);

echo json_encode(['msg' => 0]);