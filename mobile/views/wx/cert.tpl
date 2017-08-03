<section id="cert">
	<div class="nav">
		<a href="/wx/single#sme">返回</a>
	</div>
	<div class="cert-content">
		<div class="cert-header">
			上传您手持身份证的照片，审核通过后，会给你加V哦，大大提高您的信誉和牵手成功率哦~
		</div>
		<div class="cert-img">
			<div class="cert-bg"
					 style="background: url('{{$bgImage}}') no-repeat center center;background-size: 100% 100%;">
				<div></div>
			</div>
			<a href="javascript:;" class="choose-img"></a>
		</div>
	</div>
</section>
<input type="hidden" id="certFlag" value="{{$certFlag}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>

<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/cert.js?v=1.1.5" src="/assets/js/require.js"></script>