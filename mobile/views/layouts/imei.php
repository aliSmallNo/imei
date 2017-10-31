<?php $this->beginPage() ?>
	<!DOCTYPE html>
	<html lang="zh-cmn-Hans">
	<head>
		<title>千寻恋恋-媒桂花飘香</title>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<meta http-equiv="Cache-Control" content="no-siteapp, no-cache">
		<meta http-equiv="Expires" content="0">
		<meta http-equiv="Pragma" content="no-cache">
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
		<meta name="format-detection" content="telephone=no">
		<meta name="keywords" content="千寻恋恋,微媒100,微媒,媒桂花,相亲,交友,真实相亲,真实交友,北京奔跑吧货滴">
		<meta name="description" content="千寻恋恋是北京奔跑吧货滴科技有限公司倾力打造的一个真实相亲交友平台">
		<link type="image/png" href="/favicon.png" rel="icon">
		<link type="image/png" href="/favicon.png" rel="shortcut icon">
		<link rel="stylesheet" href="/assets/css/layer.min.css?v=1.1.2">
		<link rel="stylesheet" href="/css/imei.min.css?v=<?= $this->params['ver'] ?>">
		<script src="/assets/js/jweixin-1.2.0.js"></script>
		<script>
			var _vds = _vds || [];
			window._vds = _vds;
			(function () {
				_vds.push(['setAccountId', '85375c72629aa991']);
				(function () {
					var vds = document.createElement('script');
					vds.type = 'text/javascript';
					vds.async = true;
					vds.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'dn-growing.qbox.me/vds.js';
					var s = document.getElementsByTagName('script')[0];
					s.parentNode.insertBefore(vds, s);
				})();
			})();
		</script>
		<script src="https://assets.growingio.com/sdk/wx/vds-wx-plugin.js"></script>
	</head>
	<body>
	<?php $this->beginBody() ?>
	<main>
		<div class="app-cork">
			<h4>正在初始化，请稍候...</h4>
		</div>
		<?= $content ?>
	</main>
	<?php $this->endBody() ?>
	</body>
	</html>
<?php $this->endPage() ?>