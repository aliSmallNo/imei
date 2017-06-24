<div class="form sms-form single">
	<a href="javascript:;" class="btn-change" data-tag="matcher"></a>
	<a href="javascript:;" class="btn-change" data-tag="single"></a>
	<input type="number" placeholder="请输入您的手机号码" class="input-s phone">
	<div class="flex">
		<input type="number" placeholder="请输入验证码" class="input-s code">
		<a href="javascript:;" class="btn-s s1 btn-code">发送验证码</a>
	</div>
	<p class="m-help-block">
		<em href="javascript:;" style="display: none">选择一种身份</em>
	</p>
	<p class="m-help-block">
		<a class="change"><span>我单身，给自己找对象</span></a>
		<a class="change"><span>当媒婆，我推荐单身</span></a>
	</p>
	<div class="flex">

	</div>
	<a href="javascript:;" class="m-submit-m">登录</a>
</div>
<section id="frole">
	<h1><img src="/images/logo62.png" alt="" class="logo">欢迎加入「微媒100」</h1>
	<div class="roles">
		<div class="title">请选择您要注册的身份</div>
		<a href="/wx/mreg" class="btn" data-tag="matcher">当媒婆，我推荐单身</a>
		<a href="/wx/sreg" class="btn on" data-tag="single">我单身，给自己找对象</a>
	</div>
	<a href="javascript:;" class="m-next">进入单身注册</a>
</section>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/imei.js?v=1.1.6" src="/assets/js/require.js"></script>