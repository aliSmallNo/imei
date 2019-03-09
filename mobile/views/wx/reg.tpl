<style>

	.sms-form::before {
		background: url(/images/i_zdm_brand.png) no-repeat center center;
		background-size: 100% 100%;
		width: 5rem;
		left: 73%;
	}

	.sms-form label .btn-code {
		background: #f62550;
	}

	.sms-form .action .btn-reg {
		background: #f62550;
	}

	.sms-form::before {
		background: url(/images/i_zdm_brand.png) no-repeat center center;
		background-size: 100% 100%;
		width: 5rem;
		left: 73%;
	}

	.sms-form .item {
		margin: 0 2rem 2rem 2rem;
		height: 12rem;
		border-radius: 1rem;
		box-shadow: 0 0 1rem #888;
		text-align: center;
	}

	.sms-form .item .os {
		font-size: 2rem;
		color: #000;
		padding: 1rem 0;
	}

	.sms-form .item .cont {

	}

	.sms-form .item .cont a {
		font-size: 1.5rem;
		padding: .5rem 1rem;
		margin: .5rem 0;
		background: #bd011b;
		border-radius: .5rem;
		color: #fff;
		display: inline-block;
	}
</style>
<div class="sms-form">
	{{if $reg_flag}}
		<div class="item">
			<div class="os">安卓手机</div>
			<div class="cont">
				<a href="https://api.sanbao365.com/api/version/download?from=singlemessage&isappinstalled=0">点击进入准点买安卓系统</a>
			</div>
		</div>
		<div class="item">
			<div class="os">苹果手机</div>
			<div class="cont">
				<a href="https://api.sanbao365.com/index.html">点击进入准点买iOS系统</a>
			</div>
		</div>
	{{else}}
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
	{{/if}}


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
<script data-main="/js/reg.js?v=1.1.6" src="/assets/js/require.js"></script>