<link rel="stylesheet" href="/css/zp.min.css?v=1.3.6">

<div class="zone_container">
	<div class="zone_container_top">
		<ul class="zone_container_top_bar">
			<li><a href="javascript:;">心动</a></li>
			<li><a href="javascript:;">全部</a></li>
			<li><a href="javascript:;">话题</a></li>
			<li><a href="javascript:;">语音</a></li>
		</ul>
		<div class="zone_container_topic">
			<div class="zone_container_topic_title">热门话题</div>
			<ul>
				<li><a href="javascript:;">#但是我骚啊#</a></li>
				<li><a href="javascript:;">#但是我骚啊#</a></li>
				<li><a href="javascript:;">#但是我骚啊#</a></li>
				<li><a href="javascript:;">#但是我骚啊#</a></li>
			</ul>
		</div>
	</div>
</div>

<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>
<input type="hidden" id="cWXUrl" value="{{$wxUrl}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js"></script>
<script>
	requirejs(['/js/config.js?v=1.2.5'], function () {
		requirejs(['/js/zone.js?v=1.2.5']);
	});
</script>

