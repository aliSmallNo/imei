<div class="sms-form">
	<div class="row">
		<label>
			<span>手机号</span>
			<input type="number" class="phone">
		</label>
	</div>
	<div class="row">
		<label>
			<span>验证码</span>
			<input type="number" class="code">
			<a href="javascript:;" class="btn-code">获取验证码</a>
		</label>
	</div>
	<div class="action">
		<a href="javascript:;" class="btn-reg">注册</a>
	</div>
	<div class="tip">
		<div>
			今天注册即可得：
			<ol>
				<li> 66朵媒桂花</li>
				<li> 6次异性配对</li>
				<li> 免费约会一次</li>
			</ol>
		</div>
	</div>
</div>
<div class="m-protocol">
	<a href="javascript:;">点击注册默认同意《<b>千寻恋恋用户协议</b>》</a>
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
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/reg0.js?v=1.1.2" src="/assets/js/require.js"></script>