<div class="form sms-form">
	<div class="row phone">
		<label>
			<span>手机号</span>
		</label>
		<input type="number" placeholder="请输入您的手机号码" class="group phone">
	</div>
	<div class="row">
		<label>
			<span>验证码</span>
		</label>
		<div class="group">
			<input type="number" placeholder="请输入验证码" class="code">
			<a href="javascript:;" class="btn-code line">发送验证码</a>
		</div>
	</div>
	<div class="row">
		<label>
			<span>选择性别</span>
		</label>
		<a href="javascript:;" class="j-radio" data-tag="11"><span>男性</span></a>
		<a href="javascript:;" class="j-radio" data-tag="10"><span>女性</span></a>
	</div>
	<div class="row" style="display: none">
		<label>
			<span>所在城市</span>
		</label>
		<a href="javascript:;" class="group action location"></a>
		<div class="tip">正在定位你的位置...</div>
	</div>
	<a href="javascript:;" class="m-submit-m m-submit-m-active">进入注册</a>
</div>
<div class="m-protocol">

</div>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script>
	var mProvinces = {{$provinces}};
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/imei.js?v=1.2.0" src="/assets/js/require.js"></script>