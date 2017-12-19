<section id="sec_home">
	<div class="swiper-container" style="height: 10rem">
		<div class="swiper-wrapper">
			{{foreach from=$headers item=item}}
				<div class="swiper-slide"><img src="{{$item.image}}" data-url="{{$item.url}}" style="height: 10rem"></div>
			{{/foreach}}
		</div>
		<div class="swiper-pagination"></div>
	</div>
	<a class="gift-header">特权礼包</a>
	<ul class="gift-bags" min-level="1">
		{{foreach from=$bags item=item}}
			<li>
				<a href="javascript:;" style="background-image: url({{$item.image}}) "
				   data-id="{{$item.id}}" data-price="{{$item.price}}" data-unit="{{$item.unit}}"
				   data-img="{{$item.image}}">
					<div class="title">
						<h4>{{$item.name}}</h4>
						<h5><em>{{$item.price}}</em>{{$item.unit}}</h5>
					</div>
				</a>
			</li>
		{{/foreach}}
	</ul>
	<a class="gift-header">普通礼物</a>
	<ul class="gift-stuff" min-level="1">
		{{foreach from=$stuff item=item}}
			<li>
				<a href="javascript:;" style="background-image: url({{$item.image}})"
				   data-id="{{$item.id}}" data-price="{{$item.price}}" data-unit="{{$item.unit}}"
				   data-img="{{$item.image}}">
					<h4>{{$item.name}}</h4>
					<h5>{{$item.price}}{{$item.unit}}</h5>
				</a>
			</li>
		{{/foreach}}
	</ul>
	<a class="gift-header">特权礼物 <em>只限08等级购买</em></a>
	<ul class="gift-stuff" min-level="8">
		{{foreach from=$premium item=item}}
			<li>
				<a href="javascript:;" style="background-image: url({{$item.image}})"
				   data-id="{{$item.id}}" data-price="{{$item.price}}" data-unit="{{$item.unit}}"
				   data-img="{{$item.image}}">
					<h4>{{$item.name}}</h4>
					<h5>{{$item.price}}{{$item.unit}}</h5>
				</a>
			</li>
		{{/foreach}}
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
<div class="m-draw-wrap gift-detail off">
	<div class="header">兑换礼物</div>
	<div class="image"></div>
	<div class="m-stepper">
		<span>数量:</span>
		<a href="javascript:;" class="j-stepper minus">-</a>
		<input type="number" class="num" value="1">
		<a href="javascript:;" class="j-stepper plus">+</a>
		<span> 总价:</span>
		<span class="amount">199</span>
		<span class="unit">媒桂花</span>
	</div>
	<a href="javascript:;" class="btn-next">立即兑换</a>
</div>

<input type="hidden" id="cUID" value="{{$uid}}">
<input type="hidden" id="cLEVEL" value="{{$level}}">
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
	requirejs(['/js/config.js?v=1.2.3'], function () {
		requirejs(['/js/shop.js?v=1.2.8']);
	});
</script>