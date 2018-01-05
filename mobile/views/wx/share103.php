<link rel="stylesheet" href="/css/dev.min.css?v=1.3.6">
<?php if ($qrCode) { ?>
	<div class="share103-qr">
		<div class="title">
			<div class="top">10元</div>
			<div class="qrcode">
				<img src="<?= $qrCode ?>" alt="">
			</div>
		</div>
	</div>
<?php } else { ?>
	<div class="share103-wrap">
		<div class="title">
			<div class="top">恭喜你获得10元千寻币</div>
			<div class="big">10<em>元</em></div>
			<div class="action">
				<a href="javascript:;" class="btn-share">
					分享到朋友圈即可领取
				</a>
			</div>
			<div class="tip">
				快去分享到朋友圈即可领取<br>
				千寻币可用于购买商城内道具
			</div>
		</div>
	</div>
<?php } ?>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>
<input type="hidden" id="cUID" value="<?= $uid ?>">
<input type="hidden" id="cUNI" value="<?= $uni ?>">
<input type="hidden" id="cWXUrl" value="<?= $wxUrl ?>">
<script type="text/template" id="tpl_wx_info">
	<?= $wxInfoString ?>
</script>
<script src="/assets/js/require.js"></script>
<script>
	requirejs(['/js/config.js?v=1.1.1'], function () {
		requirejs(['/js/share103.js?v=1.1.2']);
	});
</script>
