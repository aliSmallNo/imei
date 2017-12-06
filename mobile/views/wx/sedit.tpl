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

	.sedit-textarea {
		display: flex;
		background: #fff;
		padding: 1rem;
		border-bottom: 1px solid #eee;
		position: relative;
	}

	.sedit-textarea label {
		flex: 0 0 8rem;
		font-size: 1.2rem;
		color: #777;
	}

	.sedit-textarea div {
		flex: 1;
		background: #fff;
		display: flex;
		position: relative
	}

	.sedit-textarea div textarea {
		width: 100%;
		background: #fff;
		border: none;
		font-size: 1.2rem;
	}

	.sedit-textarea input[type="text"] {
		-webkit-appearance: none;
		-moz-appearance: none;
		appearance: none;
		border: none;
		width: 100%;
		background: #fff;
		font-size: 1.2rem;
	}

	.action-mult em {
		display: block;
	}

	.sedit_hide {
		display: none;
	}
</style>
<div class="m-popup-shade"></div>

<div class="nav">
	<a href="/wx/single#sme">返回</a>
</div>
<div class="sedit-avart">
	<img src="{{$avatar}}" class="bg-blur">
	<div class="sedit-avart-p">
		<a class="photo">
			<img src="{{$avatar}}" class="avatar">
		</a>
		<span class="sedit-avart-material">资料完整度 <span>{{$uInfo.percent}}</span>%</span>
		<span class="sedit-avart-material-perc">
			<span style="width: {{$uInfo.percent}}%;"></span>
		</span>
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
	<input type="text" name="name" placeholder="填写您的昵称" value="{{$uInfo.name}}">
</div>
<div class="sedit-input">
	<label>性别</label>
	<input type="text" placeholder="帅哥" value="{{$uInfo.gender_t}}" readonly>
</div>

<!-- data-field="marital" -->
<a class="sedit-alert {{if $uInfo.marital_t}}fixed{{else}}action-com{{/if}}" data-field="marital">
	<label>婚姻状态</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.marital}}">{{$uInfo.marital_t}}</em>
	</div>
</a>

<a class="sedit-alert action-location" data-pos="location">
	<label>所在城市</label>
	<div class="sedit-alert-val location">
		{{if $uInfo.location}}
			{{foreach from=$uInfo.location item=item}}
				<em data-key="{{if isset($item.key)}}{{$item.key}}{{/if}}">{{if isset($item.text)}}{{$item.text}}{{/if}}</em>
			{{/foreach}}
		{{/if}}
	</div>
</a>
<a class="sedit-alert action-homeland" data-pos="homeland">
	<label>你的籍贯</label>
	<div class="sedit-alert-val homeland">
		{{if $uInfo.homeland}}
			{{foreach from=$uInfo.homeland item=item}}
				<em data-key="{{if isset($item.key)}}{{$item.key}}{{/if}}">{{if isset($item.text)}}{{$item.text}}{{/if}}</em>
			{{/foreach}}
		{{/if}}
	</div>
</a>
<a class="sedit-alert action-com" data-field="year">
	<label>出生年份</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.birthyear}}">{{$uInfo.birthyear_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com" data-field="height">
	<label>身高</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.height}}">{{$uInfo.height_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com" data-field="weight">
	<label>体重</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.weight}}">{{$uInfo.weight_t}}</em>
	</div>
</a>

<a class="sedit-alert action-com" data-field="sign">
	<label>星座</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.horos}}">{{$uInfo.horos_t}}</em>
	</div>
</a>
<!-- =========== 个人背景 =============== -->
<div class="sedit-title">个人背景</div>
<a class="sedit-alert action-com" data-field="edu">
	<label>学历</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.education}}">{{$uInfo.education_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com " data-field="parent">
	<label>父母情况</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.parent}}">{{$uInfo.parent_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com " data-field="sibling">
	<label>家中排行</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.sibling}}">{{$uInfo.sibling_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com " data-field="dwelling">
	<label>居住情况</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.dwelling}}">{{$uInfo.dwelling_t}}</em>
	</div>
</a>
<a class="sedit-textarea ">
	<label>曾读高中名字</label>
	<div class="">
		<input type="text" name="highschool" value="{{$uInfo.highschool}}" placeholder="有利于匹配到校友哦~">
	</div>
</a>
<a class="sedit-textarea ">
	<label>曾读大学名字</label>
	<div class="">
		<input type="text" name="university" value="{{$uInfo.university}}" placeholder="有利于匹配到校友哦~">
	</div>
</a>

<!-- =========== 工作和资产 =============== -->
<div class="sedit-title ">工作和资产</div>
<a class="sedit-alert action-com action-mult" data-field="house">
	<label>房屋属性</label>
	<div class="sedit-alert-val action-val">
		{{foreach from=$uInfo.estate_t key=key item=iteme}}
			<em data-key="{{$key}}">{{$iteme}}</em>
		{{/foreach}}
	</div>
</a>

<a class="sedit-alert action-com" data-field="car">
	<label>购车情况</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.car}}">{{$uInfo.car_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com" data-field="scope">
	<label>行业</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.scope}}">{{$uInfo.scope_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com" data-field="job">
	<label>职业</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.profession}}">{{$uInfo.profession_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com " data-field="worktype">
	<label>职业属性</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.worktype}}">{{$uInfo.worktype_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com" data-field="income">
	<label>年薪</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.income}}">{{$uInfo.income_t}}</em>
	</div>
</a>
<a class="sedit-textarea">
	<label>现在单位</label>
	<div class="">
		<input type="text" name="employer" placeholder="不显示给任何第三方" value="{{$uInfo.employer}}">
	</div>
</a>
<!-- =========== 兴趣爱好 =============== -->
<div class="sedit-title">兴趣爱好</div>
<a class="sedit-alert action-com" data-field="drink">
	<label>饮酒情况</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.alcohol}}">{{$uInfo.alcohol_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com" data-field="smoke">
	<label>抽烟情况</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.smoke}}">{{$uInfo.smoke_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com" data-field="rest">
	<label>作息习惯</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.rest}}">{{$uInfo.rest_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com" data-field="belief">
	<label>宗教信仰</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.belief}}">{{$uInfo.belief_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com" data-field="pet">
	<label>关于宠物</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.pet}}">{{$uInfo.pet_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com" data-field="workout">
	<label>健身习惯</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.fitness}}">{{$uInfo.fitness_t}}</em>
	</div>
</a>
<a class="sedit-alert action-com" data-field="diet">
	<label>喜欢的食物</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$uInfo.diet}}">{{$uInfo.diet_t}}</em>
	</div>
</a>
<a class="sedit-textarea">
	<label>喜欢音乐</label>
	<div>
		<input type="text" name="music" value="{{$uInfo.music}}">
	</div>
</a>
<a class="sedit-textarea ">
	<label>喜欢书籍</label>
	<div>
		<input type="text" name="book" value="{{$uInfo.book}}">
	</div>
</a>
<a class="sedit-textarea ">
	<label>喜欢电影</label>
	<div>
		<input type="text" name="movie" value="{{$uInfo.movie}}">
	</div>
</a>
<!-- =========== 婚恋倾向	 =============== -->

<div class="sedit-title">自我评价</div>
<div class="sedit-input">
	<textarea rows="4" name="intro">{{$uInfo.intro}}</textarea>
</div>
<div class="sedit-title">兴趣爱好</div>
<div class="sedit-input">
	<textarea rows="4" name="interest">{{$uInfo.interest}}</textarea>
</div>

<!--div class="sedit-title">择偶条件</div>
<a class="sedit-alert action-cond" data-field="cage">
	<label>年龄</label>
	<div class="sedit-alert-val action-val">
		{{foreach from=$filter.age key=key item=item}}
		{{if key==1}}~{{/if}}<em data-key="{{$item.key}}">{{$item.name}}</em>
		{{/foreach}}
	</div>
</a>
<a class="sedit-alert action-cond" data-field="cheight">
	<label>身高</label>
	<div class="sedit-alert-val action-val">
		{{foreach from=$filter.height key=key item=item}}
		{{if key==1}}~{{/if}}<em data-key="{{$item.key}}">{{$item.name}}</em>
		{{/foreach}}
	</div>
</a>
<a class="sedit-alert action-cond" data-field="cincome">
	<label>年薪</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$filter.income.key}}">{{$filter.income.name}}</em>
	</div>
</a>
<a class="sedit-alert action-cond" data-field="cedu">
	<label>学历</label>
	<div class="sedit-alert-val action-val">
		<em data-key="{{$filter.edu.key}}">{{$filter.edu.name}}</em>
	</div>
</a-->
<div style="height: 8rem"></div>
<a class="m-next sedit-btn-comfirm">保存</a>

<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>

<!-- consdition start -->
<script type="text/html" id="cheightCondTemp">
	<div class="m-popup-options col3 clearfix">
		<div class="m-popup-options-top">
			<div class="start"></div>
			<div class="mid">至</div>
			<div class="end"></div>
		</div>
		{{foreach from=$heightF key=key item=h}}
			<a href="javascript:;" data-tag="height" data-key="{{$key}}">{{$h}}</a>
		{{/foreach}}
	</div>
</script>
<script type="text/html" id="cageCondTemp">
	<div class="m-popup-options col3 clearfix" data-tag="age">
		<div class="m-popup-options-top">
			<div class="start"></div>
			<div class="mid">至</div>
			<div class="end"></div>
		</div>
		{{foreach from=$ageF key=key item=h}}
			<a href="javascript:;" data-tag="age" data-key="{{$key}}">{{$h}}</a>
		{{/foreach}}
	</div>
</script>
<script type="text/html" id="cincomeCondTemp">
	<div class="m-popup-options col3 clearfix" data-tag="income">
		{{foreach from=$incomeF key=key item=h}}
			<a href="javascript:;" data-tag="income" data-key="{{$key}}">{{$h}}</a>
		{{/foreach}}
	</div>
</script>
<script type="text/html" id="ceduCondTemp">
	<div class="m-popup-options col3 clearfix" data-tag="edu">
		{{foreach from=$eduF key=key item=h}}
			<a href="javascript:;" data-tag="edu" data-key="{{$key}}">{{$h}}</a>
		{{/foreach}}
	</div>
</script>
<!-- consdition end -->

<script id="maritalTemp" type="text/html">
	<div class="cells col1 clearfix" data-tag="marital">
		{{foreach from=$marital key=key item=y}}
			<a href="javascript:;"><em data-key="{{$key}}">{{$y}}</em></a>
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

<script id="heightTemp" type="text/html">
	<div class="cells col6 clearfix">
		{{foreach from=$height key=key item=h}}
			<a href="javascript:;" style="width: 16.6%"><em data-key="{{$key}}">{{$h}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="incomeTemp" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$income key=key item=i}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$i}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="eduTemp" type="text/html">
	<div class="cells col3 clearfix">
		{{foreach from=$edu key=key item=item}}
			<a href="javascript:;" style="width: 33.3%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="signTemp" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$sign key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="weightTemp" type="text/html">
	<div class="cells col4 clearfix">
		{{foreach from=$weight key=key item=item}}
			<a href="javascript:;" style="width: 25%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="houseTemp1111" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$house key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script type="text/template" id="houseTemp">
	<div class="date-wrap sedit_mult_wrap" data-tag="house">
		<h4></h4>
		<div class="date-cancel-opt sedit_mult_options">
			{{foreach from=$house key=key item=item}}
				<a href="javascript:;" style=""><em data-key="{{$key}}">{{$item}}</em></a>
			{{/foreach}}
		</div>
		<div class="date-cancel">
			<a href="javascript:;" class="btn-date-cancel sedit_mult_options_btn">确定</a>
		</div>
	</div>
</script>
<script id="carTemp" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$car key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="scopeTemp" type="text/html">
	<div class="cells col2 clearfix" data-tag="scope">
		{{foreach from=$scope key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="drinkTemp" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$drink key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="smokeTemp" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$smoke key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="beliefTemp" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$belief key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="workoutTemp" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$workout key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="dietTemp" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$diet key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="restTemp" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$rest key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="petTemp" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$pet key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="worktypeTemp" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$worktype key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="parentTemp" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$parent key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="siblingTemp" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$sibling key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>
<script id="dwellingTemp" type="text/html">
	<div class="cells col2 clearfix">
		{{foreach from=$dwelling key=key item=item}}
			<a href="javascript:;" style="width: 50%"><em data-key="{{$key}}">{{$item}}</em></a>
		{{/foreach}}
	</div>
</script>

<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script>
	var mProvinces = {{$provinces}},
		mRoutes = {{$routes}},
		mProfessions = {{$professions}},
		mjob = {{$job}};
</script>

<script src="/assets/js/require.js"></script>
<script>
	requirejs(['/js/config.js'], function () {
		requirejs(['/js/sedit.js?v=1.4.4']);
	});
</script>