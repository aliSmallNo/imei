<div class="cr-room">
	<div class="cr-title">
		<div class="cr-title-logo">
			<img src="{{$roomInfo.rLogo}}">
		</div>
		<div class="cr-title-des">
			<span>{{$roomInfo.rTitle}}</span>
		</div>
	</div>
	<div class="cr-guide">
		<p>聊天指南</p>
		<p>1.本群是严肃，健康的相亲交友群，严禁各种与本群主题无关的聊天。</p>
		<p>2.不得利用聊天室危害国家安全、泄露国家秘密，不得侵犯国家社会集体的和公民的合法权益，不得利用聊天室制作、复制和传播下列信息
			捏造或者歪曲事实，散布谣言，扰乱社会秩序的
			宣扬封建迷信、淫秽、色情、赌博、暴力、凶杀、恐怖、教唆犯罪的
			公然侮辱他人或者捏造事实诽谤他人的，或者进行其他恶意攻击的
			进行未经许可商业广告行为的。</p>
	</div>
	<ul>
		<li>
			<div class="cr-admin-avatar">
				<img src="{{$avatar}}">
			</div>
			<div class="cr-admin-r">
				<div class="cr-admin-r-title">管理员</div>
				<div class="cr-admin-r-content"><span>恭喜你成功进入千寻恋恋聊天室</span></div>
				<div class="cr-admin-r-time"> </div>
			</div>
		</li>

		<!-- adminItems -->

	</ul>
</div>

<div class="cr-bot">
	<div class="cr-bot-input">
		<input type="text" placeholder="来说点什么吧">
	</div>
	<a href="javascript:;" class="cr-icon active" data-tag="danmu"></a>
	<a href="javascript:;" class="cr-icon" data-tag="chat"></a>
	<a href="javascript:;" class="cr-icon" data-tag="opt"></a>
	<a href="javascript:;" class="cr-send" data-tag="send">发送</a>
</div>
<div class="cr-danmu active">

	<!-- danmuItems -->

</div>

<a href="javascript:;" class="cr-mask"></a>
<div class="cr-shade"></div>
<div class="cr-bot-alert">
	<div class="cr-chat-list">
		<div class="cr-chat-list-top">
			<div class="count">讨论(<span>7</span>)</div>
			<a href="javascript:;"></a>
		</div>
		<div class="cr-chat-list-items">
			<ul>
				<!-- chatItems -->
			</ul>
			<div class="cr-no-more">

			</div>
		</div>
	</div>
</div>

<input type="hidden" id="ADMINUID" value="{{$roomInfo.rAdminUId}}">
<input type="hidden" id="cUNI" value="">
<input type="hidden" id="cRID" value="{{$rid}}">
<input type="hidden" id="cUID" value="{{$uid}}">

<script type="text/html" id="adminTmp">
	{[#data]}
	<li>
		<div class="cr-admin-avatar">
			<img src="{[avatar]}">
		</div>
		<div class="cr-admin-r">
			<div class="cr-admin-r-title">管理员</div>
			<div class="cr-admin-r-content"><span>{[content]}</span></div>
			<div class="cr-admin-r-time">{[addedon]}</div>
		</div>
	</li>
	{[/data]}
</script>
<script type="text/html" id="danmuTmp">
	{[#data]}
	<div>
		<em>
			<a>
				<span>{[content]}</span>
				<img src="{[avatar]}">
			</a>
		</em>
	</div>
	{[/data]}
</script>
<script type="text/html" id="chatTmp">
	{[#data]}
	<li>
		<div class="avatar">
			<img src="{[avatar]}">
		</div>
		<div class="r">
			<div class="r-des">
				<div class="r-des-name">
					<div class="name">{[name]}</div>
					<div class="time">{[addedon]}</div>
				</div>
				{[#isAdmin]}
				<a href="javascript:;" class="r-des-opts" data-tag="show-opt"></a>
				<div class="r-des-opts-des" data-uid="{[senderid]}" data-rid="{[rid]}" data-cid="{[cid]}">
					<a href="javascript:;" data-tag="silent">禁言</a>
					<a href="javascript:;" data-tag="delete">删除</a>
				</div>
				{[/isAdmin]}
			</div>
			<div class="r-content">
				{[content]}
			</div>
		</div>
	</li>
	{[/data]}
</script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/chatroom.js?v=1.1.5" src="/assets/js/require.js"></script>

