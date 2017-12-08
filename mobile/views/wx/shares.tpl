<style>
	.swiper-container {
		width: 100%;
		height: 40rem;
	}

	.swiper-slide {
		background-position: center;
		background-size: cover;
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
</style>
<div style="height: 3rem"></div>
<div class="swiper-container">
	<div class="swiper-wrapper">
		<div class="swiper-slide" style="background-image:url(/images/share/share01.jpg)"></div>
		<div class="swiper-slide" style="background-image:url(/images/share/share02.jpg)"></div>
		<div class="swiper-slide" style="background-image:url(/images/share/share03.jpg)"></div>
		<div class="swiper-slide" style="background-image:url(/images/share/share04.jpg)"></div>
	</div>
	<div class="swiper-pagination"></div>
</div>
<div class="action">
	<a href="javascript:;" class="btn-share">立即分享</a>
</div>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>
<input type="hidden" id="cUNI" value="{{$uni}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js"></script>
<script>
	requirejs(['/js/config.js'], function () {
		requirejs(['/js/shares.js?v=1.4.6']);
	});
</script>
