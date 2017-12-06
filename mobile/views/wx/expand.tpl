<div style="height: 1rem"></div>
{{if $sentFlag}}
	<div class="avatar">
		<img src="{{$thumb}}">
	</div>
	<h4 class="big">
		最好的我们<br>
		邀请你加入实名制高端婚恋平台
	</h4>
	<div style="height: 2.5rem"></div>
	<div class="board">
		千寻恋恋交友网是由腾讯众创推出的高端婚恋交友平台，已有120万高颜值、高收入、高学历的单身男女加入千寻恋恋。15年诚信婚恋交友机构，每日撮合成功千对以上。<br>
		千寻恋恋拥有独特苛刻的审核系统，保证所有用户的身份真实。在千寻恋恋你可以轻松遇到让你心动的Ta，和有缘的Ta互相心动，发起约会。
	</div>
	<h4 class="big-w">
		微信扫一扫 关注千寻恋恋
	</h4>
	<div class="qr">
		<img src="{{$qrcode}}">
	</div>
{{else}}
	<div class="word">
		<img src="/images/share/word01.png">
	</div>
	<div style="height: 1.5rem"></div>
	<div class="content">
		<h5>
			每邀请一名好友，并注册成功<br>
			可获得媒桂花
		</h5>
		<h4>99
			<small>朵</small>
		</h4>
		<h6>
			<div>每次分享都会随机获得以下大礼包：</div>
			七天畅聊卡，月度畅聊卡，季度畅聊卡<br>
			10朵媒桂花，50朵媒桂花，99朵媒桂花
		</h6>
	</div>
	<div class="action">
		<a href="javascript:;" class="btn-share">立即分享</a>
	</div>
{{/if}}
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>
{{$uni}}
<input type="hidden" id="cUNI" value="{{$uni}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js"></script>
<script>
	requirejs(['/js/config.js?v=1.1'], function () {
		requirejs(['/js/expand.js?v=1.1.4']);
	});
</script>
