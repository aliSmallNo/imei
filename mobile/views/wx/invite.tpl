<div class="nomp-wrap">
	<div class="nomp-up">
		<h4>找个好友做媒婆<br>帮我写推荐</h4>
		<strong>?</strong>
		<p>"微媒100" 上每一个单身都有一位身边的小伙伴做"媒婆"，为Ta的真实身份背书，并写上几句推荐语</p>
	</div>
	<div class="nomp-down">
		<a href="javascript:;" class="btn-share">找个媒婆给我写推荐</a>
	</div>
</div>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>
<input type="hidden" id="cWXUrl" value="{{$wxUrl}}">
<input type="hidden" id="cEncryptId" value="{{$encryptId}}">
<input type="hidden" id="cSenderThumb" value="{{$senderThumb}}">
<input type="hidden" id="cSenderName" value="{{$senderName}}">
<input type="hidden" id="cFriend" value="{{$friend}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/invite.js?v=1.1.2" src="/assets/js/require.js"></script>