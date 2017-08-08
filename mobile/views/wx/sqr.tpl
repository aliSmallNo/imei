<div class="qrcode-wrap">
	<div class="top">
		<img src="/images/logo240.png">
		<p>TA在下一个情人节等你</p>
	</div>
	<h4>单身么？</h4>
	<h5>这里有<b>真实靠谱</b>的本地单身</h5>
	<h5><b>等你！</b></h5>
	<ul class="clearfix">
		<li><img src="/images/model1.jpg"></li>
		<li><img src="/images/model2.jpg"></li>
		<li><img src="/images/model3.jpg"></li>
	</ul>
	<h4>不是单身么？</h4>
	<h5>推荐身边的优秀单身</h5>
	<h5><b>丰厚的礼金等你！</b></h5>
	<div class="qrcode">
		<p>长按识别二维码 关注微媒100</p>
		<p>即刻开始 还有活动哦~</p>
		<img src="{{$qrcode}}" class="qrcode">
		<p>长按识别二维码 惊喜等着你</p>
	</div>
</div>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>
<input type="hidden" id="cUID" value="{{$uId}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/sqr.js?v=1.1.1" src="/assets/js/require.js"></script>