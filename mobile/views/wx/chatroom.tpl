<div class="cr-room">
	<div class="cr-title">
		<div class="cr-title-logo">
			<img src="{{$roomInfo.rLogo}}">
		</div>
		<div class="cr-title-des">
			<span>{{$roomInfo.rTitle}}</span>
		</div>
	</div>
	<div class="cr-guide" style="display: none">
		<p>聊天指南</p>
		<p>1.本群是严肃，健康的相亲交友群，严禁各种与本群主题无关的聊天。</p>
		<p>2.不得利用聊天室制作、复制和传播下列信息<br>
			散布谣言，扰乱社会秩序的<br>
			宣扬封建迷信、淫秽、赌博、暴力的<br>
			进行未经许可商业广告行为的。</p>
	</div>
	<div class="cr-loading-items spinner"></div>
	<ul>
		<!-- adminItems -->
	</ul>
	<div class="cr-bottom-pl"></div>
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
<div class="cr-danmu active" style="display: none">

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
			<div class="cr-top-pl"></div>
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
<input type="hidden" id="cLASTID" value="{{$lastId}}">
<style>
	.cr-room ul li.right {
		direction: rtl;
	}
	.cr-room ul li.right .cr-admin-avatar{
		text-align: right;
	}
	.cr-room ul li.right .cr-admin-r{
		text-align: right;
		padding-left: 4rem;
	}
</style>
<script type="text/html" id="adminTmp">
	{[#data]}
	<li class="{[dir]}">
		<a class="cr-admin-avatar">
			<img src="{[avatar]}">
		</a>
		<div class="cr-admin-r">
			<div class="cr-admin-r-title" style="display: none">{[name]}{[#isAdmin]}(管理员){[/isAdmin]}</div>
			<div class="cr-admin-r-content" style="margin: .2rem 0"><span>{[content]}</span></div>
			<div class="cr-admin-r-time" style="display: block;direction: ltr;"><span>{[addedon]}</span></div>
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
		<div class="avatar {[#isAdmin]}{[#ban]}on{[/ban]}{[/isAdmin]}">
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
					<a href="javascript:;" data-tag="silent" data-ban="{[ban]}">{[#ban]}取消禁言{[/ban]}{[^ban]}禁言{[/ban]}</a>
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
<script data-main="/js/chatroom.js?v=1.1.6" src="/assets/js/require.js"></script>

