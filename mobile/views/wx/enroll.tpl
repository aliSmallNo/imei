<link rel="stylesheet" href="/css/dev.min.css?v=1.1">
<div class="m-popup-shade"></div>
<div style="height: 4rem"></div>
<div class="enroll-title">
	<img src="/images/enroll/word01.png" alt="">
</div>
<div class="enroll-form">
	<ul>
		<li>
			<em>姓名</em>
			<input type="text" name="name">
		</li>
		<li>
			<em>手机号</em>
			<input type="text" name="phone">
		</li>
		<li>
			<em>验证码</em>
			<input type="text" name="code">
			<a href="javascript:;" class="j-sms">获取验证码</a>
		</li>
		<li>
			<em>性别</em>
			<label><input type="radio" name="gender" value="11">男性</label>
			<span class="space2"></span>
			<label><input type="radio" name="gender" value="10">女性</label>
		</li>
		<li>
			<em>婚姻状态</em>
			<a href="javascript:;" data-field="marital"></a>
		</li>
		<li>
			<em>所在城市</em>
			<a href="javascript:;" data-field="location"></a>
		</li>
		<li>
			<em>出生年份</em>
			<a href="javascript:;" data-field="year"></a>
		</li>
	</ul>
	<div style="height: 3rem"></div>
	<a href="javascript:;" class="j-next">下一步</a>
	<div style="height: 6rem"></div>
</div>

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
<script src="/assets/js/require.js" data-main="/js/enroll.js?v=1.3.7"></script>