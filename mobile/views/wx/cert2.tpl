<style>
	p, h5 {
		color: #c9c9c9;
	}

	.nav {
		background: #fafafa;
		position: fixed;
		left: 0;
		right: 0;
		top: 0;
	}

	.nav a {
		color: #f16192;
	}

	.nav:before {
		border-color: #f16192 #f16192 transparent transparent
	}

	.nav a:last-child {
		text-align: right;
		margin-right: 1rem;
	}

	.c-content {
		padding: 4rem 1rem 3rem 1rem;
	}

	.c-content .c-titles {
		margin-bottom: 1.2rem;
	}

	.c-content .c-titles h3 {
		font-size: 1.5rem;
		margin: .6rem 0;
	}

	.c-content .c-titles p {
		font-size: 1.2rem;
		margin-bottom: 1.5rem;
	}

	.c-up {
		display: flex;
		margin-bottom: 1rem;
	}

	.c-up-item {
		flex: 1;
		margin: 0 1rem 0 0
	}

	.c-up-item h5 {
		margin: 1rem 0;
		font-size: 1.2rem;
	}

	.c-up-item img {
		width: 13rem;
		height: 12rem;
	}

</style>
<div class="nav">
	<a href="/wx/single#sme">返回</a>

	<a href="javascript:;" class="c-btn-submit {{if $certFlag}}hide{{/if}}">提交</a>

</div>
<div class="c-content">
	{{if $certNote && !$certFlag}}
		<div class="tip-block" style="padding: 8px 5px">系统提示你：实名审核失败。{{$certNote}}</div>
	{{/if}}
	<div class="c-titles">
		<h3>为什么要实名认证</h3>
		<p>作为一个真实、严肃的婚恋俱乐部，我们要求用户必须完成身份认证。对于已结婚为目的的用户，我们希望创建一个无酒托，婚托的环境。</p>
		<h3>关于隐私安全</h3>
		<p>您上传的任何身份证照片等资料，仅供人工审核使用他人无法看到，此外我们会对照片进行安全处理，敬请放心。</p>
	</div>
	{{if $certFlag}}
		<div class="tip-block" style="padding: 12px 6px; font-size: 16px;text-align: center">
			你已经实名认证啦~
		</div>
	{{else}}
		<div class="c-up">
			<div class="c-up-item">
				<h5>身份证正面照</h5>
				<a href="javascript:;" data-tag="zm" localId="">
					<img src="/images/cert/cert_1x.png">
				</a>
			</div>
			<div class="c-up-item">
				<h5>手持身份证照片</h5>
				<a href="javascript:;" data-tag="sc" localId="">
					<img src="/images/cert/cert_2x.png">
				</a>
			</div>
		</div>
		<div class="c-up">
			<div class="c-up-item">
				<h5>身份证示例</h5>
				<img src="/images/cert/cert_3x.png">
			</div>
			<div class="c-up-item">
				<h5>手持身份证示例</h5>
				<img src="/images/cert/cert_4x.png">
			</div>
		</div>
	{{/if}}
</div>
<input type="hidden" id="certFlag" value="{{$certFlag}}">

<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>

<script src="/assets/js/require.js"></script>
<script>
	requirejs(['/js/config.js?v=1.1.7'], function () {
		requirejs(['/js/cert2.js?v=1.4.10']);
	});
</script>
