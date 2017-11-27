<link rel="stylesheet" href="/css/dev.min.css?v=2.5">
<div style="height: 5rem"></div>
<div class="enroll-title">
	<img src="/images/enroll/word02.png" alt="">
</div>
<div class="enroll-cert">
	<div class="enroll-cert-wrap">
		<h4>身份证正面照</h4>
		<div class="pic-row">
			<div class="pic-cell">
				<a href="javascript:;" class="j-photo" data-tag="zm" localId="">
					{{if $certFront}}<img src="{{$certFront}}">{{/if}}
				</a>
			</div>
			<div class="pic-cell">
				<div class="pic-cell-img">
					<img src="/images/cert/cert_3x.png">
				</div>
			</div>
		</div>
		<h4>手持身份证照片</h4>
		<div class="pic-row">
			<div class="pic-cell">
				<a href="javascript:;" class="j-photo" data-tag="sc" localId="">
					{{if $certHold}}<img src="{{$certHold}}">{{/if}}
				</a>
			</div>
			<div class="pic-cell">
				<div class="pic-cell-img">
					<img src="/images/cert/cert_4x.png">
				</div>
			</div>
		</div>
	</div>
	<div style="height: 2.5rem"></div>
	<a href="javascript:;" class="j-next">完成</a>
	<div style="height: 5rem"></div>
</div>
<input type="hidden" id="certFlag" value="{{$certFlag}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js" data-main="/js/enroll2.js?v=1.2.6"></script>