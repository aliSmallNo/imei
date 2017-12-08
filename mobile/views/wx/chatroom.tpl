<style>
	.cr-room {

	}

	.cr-room .cr-title {
		display: flex;
		background: #fff;
		padding: 1rem;
	}

	.cr-room .cr-title .cr-title-logo {
		flex: 0 0 4rem;
	}

	.cr-room .cr-title .cr-title-logo img {
		width: 3rem;
		height: 3rem;
		border-radius: 100%;
	}

	.cr-room .cr-title .cr-title-des {
		flex: 1;
		padding: .5rem;
		font-size: 1.5rem;
		font-weight: 800;
	}

	.cr-guide {
		padding: 1rem;
	}

	.cr-guide p {
		font-size: 1.2rem;
		color: #777;
	}

	.cr-room ul li {
		display: flex;
		margin: 1rem 0;
	}

	.cr-room ul li .cr-admin-avatar {
		flex: 0 0 4rem;
	}

	.cr-room ul li .cr-admin-avatar img {
		width: 3rem;
		height: 3rem;
		border-radius: 100%;
	}

	.cr-room ul {
		padding: 3rem 1rem 5rem 1rem;
	}

	.cr-room ul li .cr-admin-r {
		flex: 1;
	}

	.cr-room ul li .cr-admin-r .cr-admin-r-title {
		font-size: 1.1rem;
	}

	.cr-room ul li .cr-admin-r .cr-admin-r-content {
		font-size: 1.3rem;
		margin: 1rem 0;
	}

	.cr-room ul li .cr-admin-r .cr-admin-r-content span {
		background: #b0e65c;
		padding: .5rem;
		font-size: 1.2rem;
		display: inline-block;
	}

	.cr-room ul li .cr-admin-r .cr-admin-r-time {
		font-size: .8rem;
		padding: .5rem;
	}
</style>
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

<style>
	.bg-cr {
		background: #f8f8f8;
	}

	.cr-mask {
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		display: none;
		background-color: rgba(0, 0, 0, 0);
		pointer-events: auto;
		z-index: 11;
	}

	.cr-shade {
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		display: none;
		background-color: rgba(0, 0, 0, 0.6);
		pointer-events: auto;
		z-index: 20;
	}

	.cr-bot-alert {
		position: fixed;
		left: 0;
		right: 0;
		bottom: 0;
		z-index: 21;
		display: none;
	}

	.cr-bot {
		position: fixed;
		left: 0;
		right: 0;
		bottom: 0;
		height: 4rem;
		background: #fff;
		z-index: 23;
		display: flex;
	}

	.cr-bot .cr-bot-input {
		flex: 1;
		margin: 0 1rem 0 1rem;
		align-self: center;
	}

	.cr-bot .cr-bot-input input {
		width: 90%;
		height: 2rem;
		border: none;
		background: #eee;
		border-radius: 2rem;
		padding: .3rem 1rem;
		font-size: 1.2rem;
		color: #777;
	}

	.cr-bot .cr-icon {
		margin: 0 .5rem 0 .5rem;
		display: inline-block;
		flex: 0 0 2rem;
		align-self: center;

		width: 2rem;
		height: 2rem;
	}

	.cr-bot .cr-icon[data-tag=danmu] {
		background: url(/images/cr_tanmu_no.png);
		background-size: 2rem 2rem;
	}

	.cr-bot .cr-icon[data-tag=danmu].active {
		background: url(/images/cr_tanmu_yes.png);
		background-size: 2rem 2rem;
	}

	.cr-bot .cr-icon[data-tag=opt] {
		background: url(/images/cr_set_list.png);
		background-size: 2rem 2rem;
	}

	.cr-bot .cr-icon[data-tag=chat] {
		background: url(/images/cr_chat_list.png);
		background-size: 2rem 2rem;
	}

	.cr-bot .cr-send {
		flex: 0 0 4rem;
		align-self: center;
		font-size: 1.2rem;
		background: #10c753;
		color: #fff;
		text-align: center;
		padding: .5rem;
		margin: 0 .5rem 0 .5rem;
		border-radius: 2rem;
		display: none;
	}

	.cr-danmu {
		position: fixed;
		right: 0;
		bottom: 4rem;
		width: 20rem;
		max-height: 12rem;
		overflow: hidden;
		z-index: 10;
		display: none;
	}

	.cr-danmu.active {
		display: block;
	}

	.cr-danmu div {
		text-align: right;
		margin: .75rem 1rem;

	}

	.cr-danmu div em {
		display: inline-block;
	}

	.cr-danmu div a {
		background: rgba(119, 119, 119, .5);
		text-align: right;
		display: flex;
		padding: .5rem;
		border-radius: .5rem;
	}

	.cr-danmu div a span {
		font-size: 1rem;
		color: #fff;
		align-self: center;
		flex: 1;
		padding: 0 .5rem;
		max-width: 14rem;

		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.cr-danmu div a img {
		width: 2rem;
		height: 2rem;
		border-radius: 100%;
		align-self: center;
		flex: 0 0 2rem;
	}
</style>
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
<style>
	.cr-chat-list {
		background: #fff;
		padding-bottom: 4rem;
	}

	.cr-chat-list .cr-chat-list-top {
		display: flex;
		padding: 1rem;
		background: #f8f8f8;
	}

	.cr-chat-list .cr-chat-list-top .count {
		flex: 1;
		font-size: 1.2rem;
		color: #777;
	}

	.cr-chat-list .cr-chat-list-top a {
		flex: 0 0 3rem;
		width: 2rem;
		height: 2rem;
		position: relative;
	}

	.cr-chat-list .cr-chat-list-top a:after {
		position: absolute;
		content: '';
		left: .5rem;
		width: .8rem;
		height: .8rem;
		border-left: .1rem solid #777;
		border-bottom: .1rem solid #777;
		transform: rotate(-45deg);
	}

	.cr-chat-list .cr-chat-list-items {
		list-style: none;
		max-height: 38rem;
		min-height: 20rem;
		overflow-x: hidden;
		overflow-y: auto;
	}

	.cr-chat-list ul li {
		display: flex;
		padding: 1rem;
		border-bottom: 1px solid #f8f8f8;
	}

	.cr-chat-list ul li .avatar {
		flex: 0 0 4rem;
	}

	.cr-chat-list ul li .avatar img {
		width: 3rem;
		height: 3rem;
		border-radius: 100%;
	}

	.cr-chat-list ul li .r {
		flex: 1;
	}

	.cr-chat-list ul li .r .r-des {
		display: flex;
		position: relative;
	}

	.cr-chat-list ul li .r .r-des .r-des-name {
		flex: 1;
		padding: .25rem;
	}

	.cr-chat-list ul li .r .r-des .r-des-name .name {
		font-size: 1rem;
	}

	.cr-chat-list ul li .r .r-des .r-des-name .time {
		font-size: .8rem;
		margin: .5rem 0;
	}

	.cr-chat-list ul li .r .r-des .r-des-opts {
		flex: 0 0 3rem;
		background: url("/images/cr_chat_set.png");
		background-size: 3rem 3rem;
		position: relative;
		display: block;
	}

	.cr-chat-list ul li .r .r-des .r-des-opts-des {
		position: absolute;
		right: .5rem;
		top: 2.5rem;
		padding: .2rem 0;
		background: #777;
		border-radius: .2rem;
		display: none;
	}

	.cr-chat-list ul li .r .r-des .r-des-opts-des:after {
		position: absolute;
		content: '';
		left: 3rem;
		top: -.5rem;
		width: 0;
		height: 0;
		border-bottom: 1rem solid #777;
		border-left: 1rem solid transparent;
		border-right: 1rem solid transparent;
	}

	.cr-chat-list ul li .r .r-des .r-des-opts-des a {
		background: #777;
		padding: .3rem 1.5rem;
		font-size: 1rem;
		border-bottom: 1px solid #fff;
		color: #fff;
		display: block;
	}

	.cr-chat-list ul li .r .r-des .r-des-opts-des a:last-child {
		border: none;
	}

	.cr-chat-list ul li .r .r-content {
		font-size: 1.2rem;
		margin-top: .5rem;
		letter-spacing: .1rem;
		line-height: 1.8rem;
	}

	.cr-chat-list .cr-no-more {
		font-size: 1rem;
		color: #777;
		text-align: center;
		padding: 1rem;
	}
</style>

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

