<?php $this->beginPage() ?>
	<!DOCTYPE html>
	<html lang="zh-cmn-Hans">
	<head>
		<title>媒婆100 - 运营维护后台</title>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<meta http-equiv="Cache-Control" content="no-siteapp, no-cache">
		<meta http-equiv="Expires" content="0">
		<meta http-equiv="Pragma" content="no-cache">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, shrink-to-fit=no, user-scalable=0">
		<meta name="format-detection" content="telephone=no">
		<meta name="google" content="notranslate">
		<meta name="description" content="微媒100是北京奔跑吧货滴科技有限公司倾力打造的一个真实相亲交友平台">
		<meta name="author" content="北京奔跑吧货滴科技有限公司">
		<link rel="icon" href="/favicon.png" type="image/png">
		<link rel="icon" href="/favicon-16.png" sizes="16x16" type="image/png">
		<link rel="icon" href="/favicon-32.png" sizes="32x32" type="image/png">
		<link rel="icon" href="/favicon-48.png" sizes="48x48" type="image/png">
		<link rel="icon" href="/favicon-62.png" sizes="62x62" type="image/png">
		<link rel="icon" href="/favicon-192.png" sizes="192x192" type="image/png">
		<link rel="apple-touch-icon-precomposed" href="/favicon-114.png">

		<link rel="stylesheet" href="/assets/js/bootstrap336.min.css">
		<link rel="stylesheet" href="/assets/js/font-awesome450.min.css">
		<link rel="stylesheet" href="/assets/js/metisMenu113.min.css">
		<link rel="stylesheet" href="/css/admin.min.css?v=1.2.4">

		<link rel="stylesheet" href="/css/jquery-ui.min.css">
		<!--[if lt IE 9]>
		<script src="/assets/js/html5shiv.min.js"></script>
		<script src="/assets/js/respond.min.js"></script>
		<![endif]-->
		<script src="/assets/js/jquery221.min.js"></script>
		<script src="/assets/js/bootstrap336.min.js"></script>
		<script src="/assets/js/metisMenu113.min.js"></script>
		<script src="/assets/lib/layer/layer.js"></script>
		<script>
			var gIconOK = '  ';
			var gIconAlert = '  ';
		</script>
	</head>
	<body>
	<?php $this->beginBody() ?>
	<div class="wrap">
		<div class="container">
			<?= $content ?>
		</div>
	</div>
	<?php $this->endBody() ?>

	<script src="/assets/js/mustache.min.js"></script>
	<script src="/assets/lib/My97DatePicker/WdatePicker.js"></script>
	<script src="/js/sb-admin-2.js"></script>
	<script src="/assets/js/iscroll.js"></script>
	<script type="text/html" id="admin_todo_tpl">
		{[#items]}
		<li class="{[light]}">
			<a href="{[action]}">
				<div>
					<span class="badge">{[no]}</span><b>{[title]}</b>
					<span class="pull-right text-muted"><em>{[time]}</em></span>
				</div>
				<div>{[tip]}</div>
			</a>
		</li>
		{[#show_divider]}
		<li class="divider"></li>{[/show_divider]}
		{[/items]}
	</script>
	<script type="text/html" id="admin_wxmsg_tpl">
		{[#items]}
		<li>
			<a href="/info/wxreply?id={[bFrom]}">
				<div>
					<b>{[wNickName]}</b>
					<span class="pull-right text-muted"><em>{[dt]}</em></span>
				</div>
				<div>{[bContent]}</div>
			</a>
		</li>
		<li class="divider"></li>
		{[/items]}
		<li>
			<a class="text-center" href="/info/listwx">
				<strong>更多微信公众号消息</strong>
				<i class="fa fa-angle-right"></i>
			</a>
		</li>
	</script>
	<script src="/assets/js/countUp.js"></script>
	<script src="/js/footer.min.js?v=1.1"></script>
	</body>
	</html>
<?php $this->endPage() ?>