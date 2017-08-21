<div class="m-sign">
	<div class="logo"></div>
	<div class="smp"></div>
	<div class="sign-bar">
		<a href="javascript:;" class="btn {{if $isSign}}signed{{/if}}">{{$title}}</a>
	</div>
	<div class="sign-user">
		<img src="{{$avatar}}" alt="">
		<em>{{$nickname}}</em>
	</div>
	<a href="javascript:;" class="help">怕错过签到？记得置顶公众号哟</a>
	<div class="rules"><p class="title">签到规则</p>
		<div class="content">
			<ol>
				<li>一天只可签到1次哟！</li>
				<li>媒婆身份签到成功即可随机获得不定数额的现金奖励，单身身份签到成功也可随机获得不定数额的媒桂花哟</li>
				<li>媒婆现金奖励需要30天后才可提现</li>
				<li>奔跑吧货滴保留法律范围内允许的对此活动的解释权</li>
			</ol>
			<p>注：签到成功后直接将奖励充到账户中，不会再消息通知您了哦</p>
		</div>
	</div>
</div>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/sign.js?v=1.2.0" src="/assets/js/require.js"></script>