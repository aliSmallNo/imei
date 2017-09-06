<div id="schat">
	<a href="javascript:;" class="help-chat-icon">
		<div class="img"><img src="/images/ico_help_chat.png"></div>
		<div class="text">助聊?</div>
	</a>
	<div class="report_wrap">
		<p class="title chat-tip">不要在对话中轻易给对方微信号，以防被恶意骚扰~</p>
		<ul class="chats"></ul>
	</div>
	<div class="m-bottom-pl"></div>
	<div class="m-bottom-bar">
		<div class="m-chat-wrap off">
			<a class="schat-option" data-tag="tohelpchat" style="display: none">助聊</a>
			<a class="schat-option" data-tag="toblock">拉黑对方</a>
			<a class="schat-option">取消</a>
		</div>
		<div class="help-chat off">
			<div class="help-chat-item">
				<a href="javascript:;">秀</a>
				<a href="javascript:;" help-tag="personal">个人</a>
				<a href="javascript:;" help-tag="experience">经历</a>
				<a href="javascript:;" help-tag="family">家庭</a>
				<a href="javascript:;" help-tag="concept">观念</a>
				<a href="javascript:;" help-tag="interest">兴趣</a>
			</div>
			<div class="help-chat-item">
				<a href="javascript:;">聊</a>
				<a href="javascript:;" help-tag="common">共同</a>
				<a href="javascript:;" help-tag="future">未来</a>
				<a href="javascript:;" help-tag="privacy">隐私</a>
				<a href="javascript:;" help-tag="marriage">婚姻</a>
				<a href="javascript:;">加V</a>
			</div>
		</div>
		<div class="icons"><a class="schat-options"></a></div>
		<div class="input"><input class="chat-input" placeholder="在这输入，注意文明礼貌哦~" maxlength="120"></div>
		<div class="action"><a class="btn-chat-send">发送</a></div>
	</div>
</div>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>
<input type="hidden" id="cUNI" value="{{$uni}}">
<script type="text/template" id="tpl_chat">
	{[#items]}
	<li class="{[dir]}">
		<a href="{[url]}" {[#eid]}data-eid="{[.]}"{[/eid]} class="avatar j-profile"><img src="{[avatar]}"></a>
		<div class="content"><span>{[content]}</span></div>
	</li>
	{[/items]}
</script>
<script type="text/template" id="tpl_chat_topup">
	<div class="topup-wrap">
		<h4>我要跟TA密聊</h4>
		<h5>先捐助我们些媒桂花吧~</h5>
		<div class="topup-opt clearfix">
			{[#items]}
			<a href="javascript:;" data-amt="{[amt]}">
				<div class="img"><img src="/images/ico_rose.png"></div>
				<div class="des">
					<em>x {[amt]}朵</em>
					<i>聊{[num]}句</i>
				</div>
			</a>
			{[/items]}
		</div>
		<div class="topup-action">
			<a href="javascript:;" class="btn-topup">捐媒<br>桂花</a>
		</div>
		<div class="split"><span>或者</span></div>
		<div class="topup-bot">
			<p>没有媒桂花了，分享到朋友圈，收获奖励<br>但是一天内只奖励一次哦~</p>
			<a href="/wx/mshare" class="btn">分享到朋友圈</a>
		</div>
		<a href="javascript:;" class="m-popup-close"></a>
	</div>
</script>
<script type="text/template" id="tpl_chat_share">
	<div class="topup-wrap">
		<h4>你没有媒桂花了哟</h4>
		<div class="topup-bot">
			<p>快去分享到朋友圈，收获奖励，但是每天只奖励一次哦~<br></p>
			<a href="/wx/mshare" class="btn">分享到朋友圈</a>
		</div>
		<a href="javascript:;" class="m-popup-close"></a>
	</div>
</script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script src="/assets/js/socket.io.slim.js"></script>
<script data-main="/js/room.js?v=1.1.1" src="/assets/js/require.js"></script>