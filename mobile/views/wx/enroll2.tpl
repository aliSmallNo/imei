<link rel="stylesheet" href="/css/dev.min.css?v=2.2">
<div style="height: 5rem"></div>
<div class="enroll-title">
	<img src="/images/enroll/word02.png" alt="">
</div>
<div class="enroll-form">
	<h4>身份证正面照</h4>
	<div class="pic-row">
		<div class="pic-cell">
			<a href="javascript:;" data-tag="front" localId=""></a>
		</div>
		<div class="pic-cell">
			<img src="/images/cert/cert_3x.png">
		</div>
	</div>
	<h4>手持身份证照片</h4>
	<div class="pic-row">
		<div class="pic-cell">
			<a href="javascript:;" data-tag="hold" localId=""></a>
		</div>
		<div class="pic-cell">
			<img src="/images/cert/cert_4x.png">
		</div>
	</div>
</div>
<input type="hidden" id="certFlag" value="{{$certFlag}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js" data-main="/js/enroll2.js?v=1.2.0"></script>