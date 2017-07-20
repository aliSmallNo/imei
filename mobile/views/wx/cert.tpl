<style>
	.cert-content {
		background: #fff;
		padding-bottom: 30rem;
	}

	.cert-header {
		padding: 2rem;
		font-size: 1.2rem;
	}

	.cert-bg {
		background-size: 26rem 16rem;
		width: 26rem;
		height: 16rem;
		margin: 2rem auto;
	}

	.cert-bg div {
		width: 26rem;
		height: 16rem;
		background: rgba(0, 0, 0, .5);
	}

	.cert-img {
		position: relative;
	}

	.cert-img a {
		position: absolute;
		font-size: 5rem;
		color: #fff;
		bottom: 5rem;
		right: 12rem;
		border: 1px dashed #fff;
		border-radius: 5rem;
		padding: 0 1.8rem .5rem 1.5rem;
	}
</style>
<section id="cert">
	<div class="cert-content">
		<div class="cert-header">
			按要求上传您手持身份证的照片，后台会审核您上传的照片，不通过会通知您，并且需要重新上传！
		</div>
		<div class="cert-img">
			<div class="cert-bg" style="background: url('/images/cert_sample.jpg') no-repeat center center ">
				<div></div>
			</div>
			<a href="javascript:;" class="choose-img">+</a>
		</div>
	</div>


</section>

<input type="hidden" id="cUID" value="{{$hid}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>

<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/cert.js?v=1.1.4" src="/assets/js/require.js"></script>