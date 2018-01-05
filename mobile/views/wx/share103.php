<link rel="stylesheet" href="/css/dev.min.css">
<div class="share103-wrap">
	<div class="title">
		<div class="top">恭喜你获得10元千寻币</div>
		<div class="big">10<em>元</em></div>
		<div class="action">
			<a href="javascript:;" class="btn-share">
				转发到朋友圈即可领取
			</a>
		</div>
		<div class="tip">
			快去分享到朋友圈即可领取<br>
			千寻币可用于购买商城内道具
		</div>
	</div>

</div>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>
<input type="hidden" id="cUNI" value="{{$uni}}">
<script type="text/template" id="tpl_wx_info">
	<?= $wxInfoString ?>
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/share.js?v=1.3.1" src="/assets/js/require.js"></script>
