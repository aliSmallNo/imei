<link rel="stylesheet" href="/css/zp.min.css?v=1.2.0">
<section id="bag_home">
	<div class="bag-top-bar">
		<a href="javascript:;" class="on" data-tag="gift">我的礼物</a>
		<a href="javascript:;" data-tag="receive">我收到的</a>
		<a href="javascript:;" data-tag="prop">功能卡</a>
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
		{[#dt]}
		<p>{[gName]}</p>
		<em>{[dt]}</em>
		{[/dt]}
		{[^dt]}
		<p> X <span>{[co]}</span></p>
		<div><a href="javascript:;">去赠送</a></div>
		{[/dt]}
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