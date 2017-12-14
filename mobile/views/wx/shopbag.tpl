<link rel="stylesheet" href="/css/zp.min.css?v=1.2.0">
<section id="bag_home">
	<div class="bag-top-bar">
		<a href="javascript:;" class="on" data-tag="gift">我的礼物</a>
		<a href="javascript:;" data-tag="receive">我收到的</a>
		<a href="javascript:;" data-tag="prop">功能卡</a>
	</div>
	<ul class="bag-content">
		<li>
			<img src="/images/shop/stuff_qq.png">
			<p> X <span>5</span></p>
			<div><a href="javascript:;">去赠送</a></div>
		</li>
		<li>
			<img src="/images/shop/stuff_qq.png">
			<p> X <span>5</span></p>
			<div><a href="javascript:;">去赠送</a></div>
		</li>
		<li>
			<img src="/images/shop/stuff_qq.png">
			<p> X <span>5</span></p>
			<div><a href="javascript:;">去赠送</a></div>
		</li>
		<li>
			<img src="/images/shop/stuff_qq.png">
			<p> X <span>5</span></p>
			<div><a href="javascript:;">去赠送</a></div>
		</li>
	</ul>

	<div style="height: 5rem"></div>
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
<script type="text/template" id="tpl_record">
	{[#items]}
	<li>
		<div class="title">
			<h4>{[title]}
				<small>{[note]}</small>
			</h4>
			<h5>{[dt]}</h5>
		</div>
		<div class="content"><em class="{[unit]} amt{[prefix]}">{[prefix]}{[amt]}</em></div>
	</li>
	{[/items]}
</script>
<script src="/assets/js/require.js"></script>
<script>
	if (document.location.hash === "" || document.location.hash === "#") {
		document.location.hash = "#bag_home";
	}
	requirejs(['/js/config.js?v=1.2.3'], function () {
		requirejs(['/js/shopbag.js?v=1.2.7']);
	});
</script>