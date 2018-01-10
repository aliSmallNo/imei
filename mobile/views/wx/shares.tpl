<style>
	.swiper-container {
		width: 100%;
		height: 40rem;
	}

	.swiper-slide {
		width: 24rem;
		height: 35.6rem;
	}

	.swiper-slide img {
		width: 24rem;
		height: 35.6rem;
	}

	.action {
		padding: 1rem 3rem;
	}

	.action a {
		font-weight: 500;
		font-size: 1.6rem;
		height: 4rem;
		line-height: 4rem;
		border-radius: 2rem;
		display: block;
		text-align: center;
		color: #6d4c41;
		background: #fdd835;
	}
	.action a:active{
		background: #ddd000;
	}

	.big-img {
		width: 100%;
		text-align: center;
		padding-top: 2rem;
	}

	.big-img img {
		width: 98%;
	}
</style>
{{if $qrcode}}
	<div class="big-img">
		<img src="{{$qrcode}}" alt="">
	</div>
{{else}}
	<div style="height: 3rem"></div>
	<div class="swiper-container">
		<div class="swiper-wrapper">
			{{foreach from=$shares item=share}}
				<div class="swiper-slide">
					<img src="{{$share}}" alt="">
				</div>
			{{/foreach}}
		</div>
		<div class="swiper-pagination"></div>
	</div>
	<div class="action">
		<a href="javascript:;" class="btn-share">立即分享</a>
	</div>
{{/if}}
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>

<input type="hidden" id="cUID" value="{{$uid}}">
<input type="hidden" id="cUNI" value="{{$uni}}">
<input type="hidden" id="cIDX" value="{{$idx}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js"></script>
<script>
	requirejs(['/js/config.js?v=1.1.1'], function () {
		requirejs(['/js/shares.js?v=1.5.7']);
	});
</script>
