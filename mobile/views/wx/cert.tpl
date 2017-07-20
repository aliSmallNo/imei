<style>
	.cert-content {
		background: #fff;
		padding-bottom: 30rem;
	}

	.cert-header {
		padding: 1rem 3rem;
		font-size: 1.2rem;
	}

	.cert-bg {
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
		padding: 0;
		margin: 0;
		position: absolute;
		color: #fff;
		bottom: 5rem;
		right: 12rem;
		border: 1px dashed #fff;
		border-radius: 5rem;
		width: 6rem;
		height: 6rem;
	}
	.cert-img a:before{
		content: ' ';
		position: absolute;
		width: 3rem;
		height: 1px;
		background: #fff;
		left: 1.5rem;
		top: 3rem;
	}
	.cert-img a:after{
		content: ' ';
		position: absolute;
		width: 1px;
		background: #fff;
		height: 3rem;
		left: 3rem;
		top: 1.5rem;
	}
</style>
<section id="cert">
	<div class="nav">
		<a href="#sme">返回</a>
	</div>
	<div class="cert-content">
		<div class="cert-header">
			按要求上传您手持身份证的照片，后台会审核您上传的照片，不通过会通知您，并且需要重新上传！
		</div>
		<div class="cert-img">
			<div class="cert-bg" style="background: url('/images/cert_sample.jpg') no-repeat center center;background-size: 100% 100%;">
				<div></div>
			</div>
			<a href="javascript:;" class="choose-img"></a>
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