<link rel="stylesheet" href="/css/zp.min.css?v=1.2.0">
<section id="bag_home">
	<div class="bag-top-bar">
		<a href="javascript:;" class="on" data-tag="gift"><span>我的礼物</span></a>
		<a href="javascript:;" data-tag="receive"><span>我收到的</span></a>
		<a href="javascript:;" data-tag="sent"><span>我送出的</span></a>
	</div>
	<ul class="bag-content bag-wrapper">

	</ul>
	<div class="spinner "></div>
	<div class="no-more font12 " style="display: none">没有更多了~</div>

</section>
<section id="sec_list">
	<ul class="charges"></ul>
	<div class="spinner none"></div>
	<div class="no-more none">没有更多了~</div>
</section>

<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>

<input type="hidden" id="cUID" value="{{$uid}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script type="text/template" id="tpl_order">
	{[#items]}
	<li>
		<div><img src="{[gImage]}"></div>
		<p> X <span>{[co]}</span></p>
		<em>{[gName]}</em>
		<div style="display: none"><a href="javascript:;">去赠送</a></div>
	</li>
	{[/items]}
</script>
<script src="/assets/js/require.js"></script>
<script>
	if (document.location.hash === "" || document.location.hash === "#") {
		document.location.hash = "#bag_home";
	}
	requirejs(['/js/config.js?v=1.2.3'], function () {
		requirejs(['/js/shopbag.js?v=1.2.8']);
	});
</script>