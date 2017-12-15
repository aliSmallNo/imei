<section data-title="千寻群" id="join">
	<div class="cr-join">
		<div class="cr-join-logo">
			<div class="cr-join-logo-l">
				<img src="{{$roomInfo.rLogo}}">
			</div>
			<div class="cr-join-logo-r">
				<span src="">{{$roomInfo.rTitle}}</span>
			</div>
		</div>
		<div class="cr-join-intro">
			<h4>群介绍</h4>
			<p>1.本群是严肃，健康的相亲交友群，严禁各种与本群主题无关的聊天。<br>
				2.不得利用聊天室制作、复制和传播下列信息<br>
				散布谣言，扰乱社会秩序的<br>
				宣扬封建迷信、淫秽、赌博、暴力的<br>
				进行未经许可商业广告行为的。</p>
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
		<ul class="chats">

		</ul>
		<div class="m-bottom-pl"></div>
		<div class="m-bottom-bar">
			<div class="input"><input class="chat-input" placeholder="在这输入，注意文明礼貌哦~" maxlength="120"></div>
			<div class="action"><a href="javascript:;" class="btn-chat-send">发送</a></div>
		</div>
	</div>
</section>
<section data-title="" id="members">
	<div class="cr-members">
		<ul>

		</ul>
	</div>
</section>
<input type="hidden" id="ADMINUID" value="{{$roomInfo.rAdminUId}}">
<input type="hidden" id="cUNI" value="{{$uni}}">
<input type="hidden" id="cRID" value="{{$rid}}">
<input type="hidden" id="cUID" value="{{$uid}}">
<input type="hidden" id="cLASTID" value="{{$lastId}}">
<input type="hidden" id="memberFlag" value="{{$memberFlag}}">
<input type="hidden" id="lastUId" value="{{$lastUId}}">

<style>
	.chats li .content .name {
		font-size: 1rem;
		margin-bottom: .2rem;
	}

	.chats li .content:after,.chats li.right .content:after {
		top: 2.5rem;
	}
</style>
<script type="text/template" id="tpl_chat">
	{[#data]}
	{[#type]}
	<li class="{[dir]}" data-r="{[readflag]}">
		<a href="/wx/sh?id={[eid]}" {[#eid]}data-eid="{[.]}" {[/eid]} class="avatar j-profile"><img src="{[avatar]}"></a>
		<div class="content read{[readflag]}">
			<div class="name">{[name]}</div>
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
		<a href="/wx/sh?id={[eid]}">
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
		requirejs(['/js/groom.js?v=1.1.6']);
	});
</script>

