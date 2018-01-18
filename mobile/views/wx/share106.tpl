
<link rel="stylesheet" href="/css/zp.min.css?v=1.3.6">

<div class="share106-qr">
	<div class="title">
		<div class="top">{{$money}}元</div>
		<div class="qrcode">
			<img src="{{$qrCode}}" alt="">
		</div>
	</div>
</div>

<div class="share106_action">
	<a href="javascript:;" class="btn-share">立即分享</a>
</div>

<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>
<input type="hidden" id="cUID" value="{{$uni}}">
<input type="hidden" id="cUNI" value="{{$uni}}">
<input type="hidden" id="cWXUrl" value="{{$wxUrl}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js"></script>
<script>
	requirejs(['/js/config.js?v=1.2.5'], function () {
		requirejs(['/js/share106.js?v=1.2.5']);
	});
</script>

