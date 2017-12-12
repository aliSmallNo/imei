
<div class="schat-top-bar">
	<div class="cr-room" style="flex: 1;">
		<div class="cr-title" style="padding: .5rem 1rem">
			<div class="cr-title-logo">
				<img src="{{$roomInfo.rLogo}}">
			</div>
			<div class="cr-title-des">
				<span>{{$roomInfo.rTitle}}</span>
			</div>
		</div>
	</div>
</div>

<div class="report_wrap schat-content">
	<div class="spinner"></div>
	<p class="title chat-tip">不要在对话中轻易给对方微信号，以防被恶意骚扰~</p>
	<ul class="chats">

	</ul>
	<div class="m-bottom-pl"></div>
	<div class="m-bottom-bar">
		<div class="input"><input class="chat-input" placeholder="在这输入，注意文明礼貌哦~" maxlength="120"></div>
		<div class="action"><a href="javascript:;" class="btn-chat-send">发送</a></div>
	</div>
</div>

<input type="hidden" id="ADMINUID" value="{{$roomInfo.rAdminUId}}">
<input type="hidden" id="cUNI" value="{{$uni}}">
<input type="hidden" id="cRID" value="{{$rid}}">
<input type="hidden" id="cUID" value="{{$uid}}">
<input type="hidden" id="cLASTID" value="{{$lastId}}">

<script type="text/template" id="tpl_chat">
	{[#data]}
	{[#type]}
	<li class="{[dir]}" data-r="{[readflag]}">
		<a href="{[url]}" {[#eid]}data-eid="{[.]}" {[/eid]} class="avatar j-profile"><img src="{[avatar]}"></a>
		<div class="content read{[readflag]}">
			<a href="javascript:;" class="j-content-wrap">
				{[#image]}<img src="{[.]}">{[/image]}
				{[^image]}{[content]}{[/image]}
			</a>
		</div>
	</li>
	{[/type]}
	{[^type]}
	<li class="{[dir]}">
		<span>{[content]}</span>
	</li>
	{[/type]}
	{[/data]}
</script>

<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js"></script>
<script>
	requirejs(['/js/config.js?v=1.1'], function () {
		requirejs(['/js/groom.js?v=1.2.9']);
	});
</script>

