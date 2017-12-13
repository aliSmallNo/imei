<style>
	.cr-join .cr-join-logo {
		display: flex;
		padding: 1rem 2rem;
		background: #fff;
	}

	.cr-join .cr-join-logo .cr-join-logo-l {
		flex: 0 0 6rem;
	}

	.cr-join .cr-join-logo .cr-join-logo-l img {
		height: 5rem;
		width: 5rem;
		border-radius: .3rem;
	}

	.cr-join .cr-join-logo .cr-join-logo-r {
		flex: 1;
		font-weight: 800;
		padding: .5rem 0;
		font-size: 1.3rem;
	}

	.cr-join .cr-join-intro {
		background: #fff;
		margin-top: 1rem;
		padding: 1rem 2rem;
	}

	.cr-join .cr-join-intro h4 {
		font-size: 1.3rem;

	}

	.cr-join .cr-join-intro p {
		font-size: 1rem;
		color: #999;
		margin-top: 1rem;
		letter-spacing: .15rem;
	}

	.cr-join .cr-join-member {
		background: #fff;
		margin-top: 1rem;
		padding: 1rem 2rem;
	}

	.cr-join .cr-join-member h4 {
		font-size: 1.3rem;
	}

	.cr-join .cr-join-member ul {
		margin-top: 1rem;
	}

	.cr-join .cr-join-member ul li {
		width: 5rem;
		display: inline-block;
	}

	.cr-join .cr-join-member ul li img {
		width: 4rem;
		height: 4rem;
		border-radius: .5rem;
		margin-left: .5rem;
	}

	.cr-join .cr-join-member ul li p {
		text-align: center;
		font-size: 1rem;
	}

	.cr-join .cr-join-btn {
		margin: 2rem;
	}

	.cr-join .cr-join-btn a {
		display: block;
		padding: 1rem;
		background: #00aa00;
		color: #fff;
		font-size: 1.5rem;
		text-align: center;
		border-radius: .5rem;
		font-weight: 200;
	}
</style>
<section data-title="千寻群" id="join">
	<div class="cr-join">
		<div class="cr-join-logo">
			<div class="cr-join-logo-l">
				<img src="https://bpbhd-10063905.file.myqcloud.com/image/t1711201155436.jpg">
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
			<ul>
				<li><img src="https://bpbhd-10063905.file.myqcloud.com/image/t1711201155436.jpg">
					<p>兔斯基</p></li>
				<li><img src="https://bpbhd-10063905.file.myqcloud.com/image/t1711201155436.jpg">
					<p>兔斯基</p></li>
				<li><img src="https://bpbhd-10063905.file.myqcloud.com/image/t1711201155436.jpg">
					<p>兔斯基</p></li>
				<li><img src="https://bpbhd-10063905.file.myqcloud.com/image/t1711201155436.jpg">
					<p>兔斯基</p></li>
				<li><img src="https://bpbhd-10063905.file.myqcloud.com/image/t1711201155436.jpg">
					<p>兔斯基</p></li>
			</ul>
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
		<p class="title chat-tip">不要在对话中轻易给对方微信号，以防被恶意骚扰~</p>
		<ul class="chats">

		</ul>
		<div class="m-bottom-pl"></div>
		<div class="m-bottom-bar">
			<div class="input"><input class="chat-input" placeholder="在这输入，注意文明礼貌哦~" maxlength="120"
																style="-webkit-user-modify: read-write-plaintext-only"></div>
			<div class="action"><a href="javascript:;" class="btn-chat-send">发送</a></div>
		</div>
	</div>
</section>
<style>
	.cr-member a {
		display: flex;
		padding: .5rem 1rem;
		border-bottom: .1rem solid #eee;
		background: #fff;
	}

	.cr-member a img {
		flex: 0 0 4rem;
		width: 4rem;
		height: 4rem;
		border-radius: .2rem;
	}

	.cr-member a p {
		flex: 1;
		margin: 1rem;
	}
</style>
<section data-title="群成员(8)" id="members">
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
		<a href="javascript:;">
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

	requirejs(['/js/config.js?v=1.1'], function () {
		requirejs(['/js/groom.js?v=1.1.5']);
	});
</script>

