{{if !$noteString}}
<div class="nomp-wrap">
	<div class="nomp-up">
		<h4>找个好友做媒婆<br>帮我写推荐</h4>
		<strong>?</strong>
		<p>"千寻恋恋" 上每一个单身都有一位身边的小伙伴做"媒婆"，为Ta的真实身份背书，并写上几句推荐语</p>
	</div>
	<div class="nomp-down">
		<a href="javascript:;" class="btn-share">找个媒婆给我写推荐</a>
	</div>
</div>
{{else}}
<div class="received-wrap">
	<h4>我在「千寻恋恋」找对象<br>你来为我写个推荐语吧</h4>
	<div class="profile-wrap">
		<div class="profile">
			<img src="{{$senderThumb}}">
			<div class="info">
				<p class="name">{{$senderName}}</p>
				<p>{{$noteString}}</p>
			</div>
		</div>
		{{if $hasMP}}
		<div class="mp-info">
			<div class="title">
				<img src="{{$mpThumb}}">
				<p>我的媒婆</p>
			</div>
			<div class="content">
				{{$mpComment}}
			</div>
		</div>
		{{else}}
		<div class="btn-wrap">
			<a href="javascript:;" class="btn btn-main btn-link">做Ta的媒婆，写推荐</a>
		</div>
		{{/if}}
	</div>
</div>
<a href="/wx/index" class="m-next btn-enter">进入千寻恋恋</a>
{{/if}}
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>

<input type="hidden" id="cWXUrl" value="{{$wxUrl}}">
<input type="hidden" id="cEncryptId" value="{{$encryptId}}">
<input type="hidden" id="cSenderThumb" value="{{$senderThumb}}">
<input type="hidden" id="cSenderName" value="{{$senderName}}">
<input type="hidden" id="cSenderId" value="{{$senderId}}">
<input type="hidden" id="cFriend" value="{{$friend}}">
<script type="text/template" id="tpl_comment">
	<div class="prompt-wrap">
		<label>给Ta写推荐</label>
		<textarea placeholder="请写些推荐的话给Ta吧~"></textarea>
		<div class="btn-row">
			<a href="javascript:;" class="btn-cancel">取消</a>
			<a href="javascript:;" class="btn-ok">保存</a>
		</div>
	</div>
</script>
<script type="text/template" id="tpl_mp">
	<div class="mp-info">
		<div class="title">
			<img src="{[thumb]}">
			<p>{[name]}</p>
		</div>
		<div class="content">
			{[#comment]}{[.]}{[/comment]}
			{[^comment]}
			<div class="btn-wrap"><a href="javascript:;" class="btn btn-blue btn-comment">给Ta写推荐</a></div>
			{[/comment]}
		</div>
	</div>
</script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/invite.js?v=1.2.1" src="/assets/js/require.js"></script>