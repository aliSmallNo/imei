<?php $this->beginPage() ?>
	<!DOCTYPE html>
	<html lang="zh-cmn-Hans">
	<head>
		<title>微媒100-送人玫瑰手有余香</title>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<meta http-equiv="Cache-Control" content="no-siteapp, no-cache">
		<meta http-equiv="Expires" content="0">
		<meta http-equiv="Pragma" content="no-cache">
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
		<meta name="format-detection" content="telephone=no">
		<meta name="keywords" content="微媒,微媒100,相亲,交友,真实相亲,真实交友,北京奔跑吧货滴">
		<meta name="description" content="微媒100是北京奔跑吧货滴科技有限公司倾力打造的一个真实相亲交友平台">
		<link type="image/png" href="/favicon.png" rel="icon">
		<link type="image/png" href="/favicon.png" rel="shortcut icon">
		<link rel="stylesheet" href="/assets/css/layer.min.css?v=1.1.2">
		<link rel="stylesheet" href="/css/imei.min.css?v=1.5.7">
		<script src="/assets/js/jweixin-1.2.0.js"></script>
	</head>
	<body style="background: #F4F4F4">
	<?php $this->beginBody() ?>
	<div class="app">
		<div class="app-cork">
			<h4>正在初始化，请稍候...</h4>
		</div>
		<?= $content ?>
	</div>
	<?php $this->endBody() ?>
	</body>
	</html>
<?php $this->endPage() ?>