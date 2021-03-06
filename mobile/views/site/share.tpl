<div class="mycard">
	<div class="card-wrap {{$cls}}">
		<img src="{{$bgSrc}}" alt="">
	</div>
	{{if $preview}}
	<br>
	<div class="btn-wrap" style="padding: .5rem 2rem">
		<a class="btn btn-main btn-share" >马上分享</a>
	</div>
	<div>
		<a class="btn btn-mshare-rule" style="color: #0272ff;display: none;font-size: 1.2rem">活动规则</a>
	</div>
	{{else}}
	<br>
	<span class="btn">
		长按上图保存<br>分享图片到朋友圈，邀请好友来加入
	</span>
	{{/if}}
</div>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>
<input type="hidden" id="cUID" value="{{$userId}}">
<input type="hidden" id="cCITY" value="{{$city}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/mshare.js?v=1.1.6" src="/assets/js/require.js"></script>
