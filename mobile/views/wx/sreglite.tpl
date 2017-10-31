<style>
	.m-popup-options a.cur {
		background: #fbd6e3 !important;
	}

	.sedit-avart {
	}

	.sedit-avart-p {
		text-align: center;
		padding: 2rem 0;
		position: relative;
		background: rgba(0, 0, 0, .1)
	}

	.sedit-avart-p .sedit-avart-material {
		display: block;
		color: #fff;
		font-size: 1.2rem;
		font-weight: 500;
		z-index: 99;
	}

	.sedit-avart-p .sedit-avart-material-perc {
		display: block;
		height: 3px;
		text-align: center;
		margin: 1rem 8rem;
		border: 1px solid #fff;
		border-radius: 2px;
		z-index: 99;
	}

	.sedit-avart-p .sedit-avart-material-perc span {
		background: #fff;
		height: 3px;
		display: block;
		border-radius: 2px;
	}

	.sreglite-tip {
		padding: 1rem;
	}

	.sreglite-tip h4 {
		font-size: 1.5rem;
	}

	.sreglite-tip span {
		font-size: 1rem;
		color: #777;
	}
</style>
<div class="m-popup-shade"></div>

<div class="sreglite-tip">
	<h4>微信为媒，真实100%</h4><span>40秒完成注册，本地婚恋交友尽在眼前</span>
</div>
<div class="sedit-avart">
	<img src="" class="bg-blur">
	<div class="sedit-avart-p">
		<a class="photo">
			<img src="" class="avatar">
		</a>
	</div>

	<div class="m-draw-wrap off">
		<div class="title">上传本人照片，头像居中，五官高清，上半身最佳，例如：</div>
		<ul class="images clearfix">
			<li><img src="/faces/face_1.jpg"></li>
			<li><img src="/faces/face_2.jpg"></li>
			<li><img src="/faces/face_4.jpg"></li>
		</ul>
		<div class="title">我们杜绝不严肃且敷衍的照片，比如:</div>
		<ul class="images clearfix">
			<li>
				<img src="/faces/face_5.jpg">
				<p>衣冠不整</p>
			</li>
			<li>
				<img src="/faces/face_6.jpg">
				<p>模糊不清</p>
			</li>
			<li>
				<img src="/faces/face_7.jpg">
				<p>刻意遮挡</p>
			</li>
			<li>
				<img src="/faces/face_8.jpg">
				<p>动物风景</p>
			</li>
		</ul>
		<ul class="images clearfix">
			<li>
				<img src="/faces/face_9.jpg">
				<p>明星</p>
			</li>
			<li>
				<img src="/faces/face_10.jpg">
				<p>合照</p>
			</li>
			<li>
				<img src="/faces/face_11.jpg">
				<p>丑化恶搞</p>
			</li>
		</ul>
		<a class="m-next btn-select-img">上传头像</a>
	</div>
</div>
<div class="sedit-title">基本资料</div>
<div class="sedit-input">
	<label>昵称</label>
	<input type="text" name="name" placeholder="填写您的昵称" value="">
</div>
<div class="sedit-input">
	<label>手机号</label>
	<input type="text" name="phone" placeholder="填写您的手机号" value="">
</div>
<style>
	.sedit-code {
		background: #fff;
		padding: 0 1rem;
		display: flex;
		border-bottom: 1px solid #eee;
		position: relative;
	}

	.sedit-code input {
		flex: 1;
		border: 0;
		font-size: 1.2rem;
		outline: 0;
		text-align: left;
	}

	.sedit-code a {
		flex: 0 0 7rem;
		padding: .5rem 1rem;
		font-size: 1.2rem;
		background: #f06292;
		color: #fff;
		margin: .5rem 0;
		border-radius: .5rem;
	}

	.sedit-code a.disabled {
		background: #eee;
		color: #aaa;
	}
</style>
<div class="sedit-code">
	<input type="text" name="code" placeholder="填写您的验证码" value="">
	<a href="javascript:;">获取验证码</a>
</div>
<a class="sedit-alert action-com" data-field="gender">
	<label>性别</label>
	<div class="sedit-alert-val action-val">
		<em data-key=""></em>
	</div>
</a>
<a class="sedit-alert action-com" data-field="marital">
	<label>婚姻状态</label>
	<div class="sedit-alert-val action-val">
		<em data-key=""></em>
	</div>
</a>
<a class="sedit-alert action-homeland" data-pos="homeland">
	<label>你的籍贯</label>
	<div class="sedit-alert-val homeland">
		<em data-key=""></em>
	</div>
</a>

<a class="sedit-alert action-location" data-pos="location">
	<label>所在城市</label>
	<div class="sedit-alert-val location">
		<em data-key=""></em>
	</div>
</a>

<a class="sedit-alert action-com" data-field="year">
	<label>出生年份</label>
	<div class="sedit-alert-val action-val">
		<em data-key=""></em>
	</div>
</a>
<a class="sedit-alert action-com" data-field="horos">
	<label>星座</label>
	<div class="sedit-alert-val action-val">
		<em data-key=""></em>
	</div>
</a>
<a class="sedit-alert action-com" data-field="height">
	<label>身高</label>
	<div class="sedit-alert-val action-val">
		<em data-key=""></em>
	</div>
</a>

<div style="height: 8rem"></div>
<a class="m-next sedit-btn-comfirm">保存</a>

<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>

<script id="genderTemp" type="text/html">
	<div class="cells col2 clearfix" data-tag="gender">
		{{foreach from=$gender key=key item=g}}
		<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$g}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="maritalTemp" type="text/html">
	<div class="cells col3 clearfix" data-tag="marital">
		{{foreach from=$marital key=key item=y}}
		<a href="javascript:;" style="width: 33.3%"><em data-key="{{$key}}">{{$y}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="yearTemp" type="text/html">
	<div class="cells col4 clearfix" data-tag="year">
		{{foreach from=$years key=key item=y}}
		<a href="javascript:;" style="width: 25%"><em data-key="{{$key}}">{{$y}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="horosTemp" type="text/html">
	<div class="cells col2 clearfix" data-tag="horos">
		{{foreach from=$horos key=key item=h}}
		<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$h}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="heightTemp" type="text/html">
	<div class="cells col6 clearfix">
		{{foreach from=$height key=key item=h}}
		<a href="javascript:;" style="width: 16.6%"><em data-key="{{$key}}">{{$h}}</em></a>
		{{/foreach}}
	</div>
</script>

<script type="text/template" id="tpl_greeting_users">
	<div class="m-greeting-wrap">
		<h4>千寻恋恋为你推荐</h4>
		<ul class="m-greeting-users clearfix">
			{[#items]}
			<li data-id="{[id]}">
				<img src="{[thumb]}" alt="">
				<div>{[age]}岁 {[horos]}</div>
			</li>
			{[/items]}
		</ul>
		<div class="btn-wrap">
			<a href="javascript:;" class="btn btn-main btn-greeting">一键打招呼</a>
		</div>
		<h5>精选优质会员，错过要自己找呦</h5>
	</div>
</script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script>
	var mProvinces = {{$provinces}};
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/sreglite.js?v=1.3.6" src="/assets/js/require.js"></script>