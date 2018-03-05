<section data-title="千寻群聊" id="join">
	<div class="cr-join">
		<div class="cr-join-logo">
			<div class="cr-join-logo-l">
				<img src="{{$roomInfo.rLogo}}">
			</div>
			<div class="cr-join-logo-r">
				<span>{{$roomInfo.rTitle}}</span>
			</div>
		</div>
		<div class="cr-join-intro">
			<h4>群介绍</h4>
			<p>{{$roomInfo.rNote}}</p>
		</div>
		<div class="cr-join-member">
			<h4>全部群成员(<span>0</span>)</h4>
			<a href="javascript:;" class="ul">

			</a>
		</div>
		<div class="cr-join-btn">
			<a href="javascript:;">申请加入</a>
		</div>
	</div>
</section>
<section data-title="千寻群聊" id="chat">
	<div class="cr-room cr-top-bar">
		<div class="cr-title" style="padding: .5rem 1rem">
			<div class="cr-title-logo">
				<img src="{{$roomInfo.rLogo}}">
			</div>
			<div class="cr-title-des">
				<span>{{$roomInfo.rTitle}}</span>
			</div>
			<a href="javascript:;" class="cr-title-member">
				<img src="/images/cr_members.png" style="width: 3rem">
			</a>
		</div>
	</div>
	<div class="report_wrap schat-content">
		<div class="spinner"></div>
		<a href="javascript:;" class="cr-his-more">更多历史消息</a>
		<ul class="chats room-messages"></ul>
		<div class="m-bottom-pl"></div>
		<div class="m-bottom-bar">
			<div class="input"><input class="chat-input" placeholder="在这输入，注意文明礼貌哦~" maxlength="120"></div>
			<div class="action"><a href="javascript:;" class="btn-chat-send">发送</a></div>
		</div>
	</div>
</section>
<section data-title="" id="members">
	<div class="cr-members">
		<ul></ul>
	</div>
</section>
<input type="hidden" id="ADMINUID" value="{{$roomInfo.rAdminUId}}">
<input type="hidden" id="cUNI" value="{{$uni}}">
<input type="hidden" id="cRID" value="{{$rid}}">
<input type="hidden" id="cUID" value="{{$uid}}">
<input type="hidden" id="cLASTID" value="{{$lastId}}">
<input type="hidden" id="memberFlag" value="{{$memberFlag}}">
<input type="hidden" id="RTitle" value="{{{{$roomInfo.rTitle}}}}">
<input type="hidden" id="lastUId" value="{{$lastUId}}">
<input type="hidden" id="lastNAME" value="{{$lastname}}">
<input type="hidden" id="subscribe" value="{{$subscribe}}">
<input type="hidden" id="other_room" value="{{$otherRoom}}">
<input type="hidden" id="DELETED" value="{{$deleted}}">
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>
<script type="text/template" id="tpl_chat">
	{[#data]}
	{[#type]}
	<li class="{[dir]}" data-r="{[readflag]}">
		<a href="{[^del]}{[#phone]}/wx/sh?id={[eid]}{[/phone]}{[^phone]}javascript:;{[/phone]}{[/del]}{[#del]}javascript:;{[/del]}"
		   {[#eid]}data-eid="{[.]}" {[/eid]} class="avatar j-profile"><img src="{[avatar]}"></a>
		<div class="content read{[readflag]}">
			<div class="name">
				{[#isAdmin]}
				<i class="u-admin"></i>
				<i class="lever">{[pic_name]}</i>
				{[/isAdmin]}
				{[^isAdmin]}
				{[#isMember]}
				<i class="u-member"></i>
				<i class="lever">{[pic_name]}</i>
				{[/isMember]}
				{[^isMember]}
				<i class="u-new"></i>
				{[/isMember]}
				{[/isAdmin]}
				<span>{[name]}</span>
			</div>
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
<script type="text/template" id="joinTmp">
	{[#data]}
	<li>
		<img src="{[uThumb]}">
		<p>{[uName]}</p>
	</li>
	{[/data]}
</script>
<script type="text/template" id="memTmp">
	{[#data]}
	<li class="cr-member">
		<a href="{[#uPhone]}/wx/sh?id={[eid]}{[/uPhone]}{[^uPhone]}javascript:;{[/uPhone]}" data-eid="{[eid]}">
			<img src="{[uThumb]}">
			<p>{[uName]}</p>
		</a>
	</li>
	{[/data]}
</script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js"></script>
<script>
	if (document.location.hash === "" || document.location.hash === "#") {
		document.getElementById("memberFlag").value == 1 ? document.location.hash = "#chat" : document.location.hash = "#join";
	}
	requirejs(['/js/config.js?v=1.2'], function () {
		requirejs(['/js/groom.js?v=1.5.6']);
	});
</script>

