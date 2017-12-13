<link rel="stylesheet" href="/css/dev.min.css">
<section id="sec_home">
	<div class="swiper-container">
		<div class="swiper-wrapper">
			{{foreach from=$headers item=item}}
				<div class="swiper-slide"><img src="{{$item.image}}" data-url="{{$item.url}}" alt=""></div>
			{{/foreach}}
		</div>
		<div class="swiper-pagination"></div>
	</div>

</section>
<section id="sec_list">
	<ul class="charges"></ul>
	<div class="spinner none"></div>
	<div class="no-more none">没有更多了~</div>
</section>
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
		document.location.hash = "#sec_home";
	}
	requirejs(['/js/config.js?v=1.2.1'], function () {
		requirejs(['/js/shop.js?v=1.4.1']);
	});
</script>