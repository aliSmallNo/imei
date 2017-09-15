<div class="row">
	<div class="signinpanel col sform">
		<div class="row">
			<br>
		</div>
		<form action="/site/login" method="post">
			<h4 class="g-title"><img src="/images/i_brand.png" alt="">&nbsp;</h4>
			<div class="row">
				<div class="input-field col s12">
					<input name="name" id="name" type="text" placeholder="请输入用户账号" autofocus>
					<label class="active" for="name">账号</label>
				</div>
			</div>
			<div class="row">
				<div class="input-field col s12">
					<input name="pass" id="pass" type="password" placeholder="请输入用户密码">
					<label class="active" for="pass">密码</label>
				</div>
			</div>
			<a href="javascript:;" class="a-btn btn-3d">登 录</a>
			{{if isset($tip) && $tip}}
			<p class="help-block" style="font-size: 14px;color: #f33">{{$tip}}</p>
			{{/if}}
		</form>
	</div>
</div>
<script>
	$('.a-btn').on('click', function () {
		$('form').submit();
	});
</script>