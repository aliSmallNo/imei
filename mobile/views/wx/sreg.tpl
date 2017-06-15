<div class="progress">
	<div style="width: 0%;"></div>
</div>
<div class="m-popup-shade"></div>
<section id="photo">
	<p class="m-header">请上传真人头像<i>否则不会审核通过</i></p>
	<div class="nick_name">
		<a href="javascript:;" class="photo photo-file">
			<img class="avatar" src="{{$avatar}}" localId="">
		</a>
		<input type="text" placeholder="昵称" class="input-s big nickname" value="{{$nickname}}">
		<div class="place-holder-s1"></div>
		<a href="javascript:;" class="btn-s s3" tag="avatar">下一步</a>
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
</section>
<section id="gender">
	<p class="m-header user">
		请问您是一位美女，还是一位帅哥？
		<i>性别注册成功不可以修改哦</i>
	</p>
	<div class="choice">
		<a href="javascript:;" class="gender-opt female"></a>
		<span>美女</span>
		<div class="line"></div>
		<a href="javascript:;" class="gender-opt male"></a>
		<span>帅哥</span>
	</div>
</section>
<section id="location">
	<p class="m-header s1">您的位置</p>
	<a href="javascript:;" class="action-row">
		<div class="location" data-tag="location">
			{{foreach from=$locInfo item=item}}
			<em data-key="{{$item.key}}">{{$item.text}}</em>
			{{/foreach}}
		</div>
	</a>
	<div class="btn-s-wrap">
		<a href="javascript:;" class="btn-s s3" tag="location">下一步</a>
	</div>
</section>
<section id="year">
	<p class="m-header s1">您是哪一年出生的？</p>
	<div class="cells col5 clearfix" data-tag="year">
		{{foreach from=$years key=key item=y}}
		<a href="javascript:;" data-key="{{$key}}">{{$y}}</a>
		{{/foreach}}
	</div>
</section>
<section id="horos">
	<p class="m-header s1">您的星座？</p>
	<div class="cells col2 clearfix" data-tag="sign">
		{{foreach from=$sign key=key item=y}}
		<a href="javascript:;" data-key="{{$key}}">{{$y}}</a>
		{{/foreach}}
	</div>
</section>
<section id="height">
	<p class="m-header s1">请问您的身高是多少？</p>
	<div class="cells col2 clearfix" data-tag="height">
		{{foreach from=$height key=key item=h}}
		<a href="javascript:;" data-key="{{$key}}">{{$h}}</a>
		{{/foreach}}
	</div>
</section>
<section id="weight">
	<p class="m-header s1">请问您的体重是多少？</p>
	<div class="cells col2 clearfix" data-tag="weight">
		{{foreach from=$weight key=key item=w}}
		<a href="javascript:;" data-key="{{$key}}">{{$w}}</a>
		{{/foreach}}
	</div>
</section>
<section id="income">
	<p class="m-header s1">请问您的年收入是多少？</p>
	<div class="cells col2 clearfix" data-tag="income">
		{{foreach from=$income key=key item=i}}
		<a href="javascript:;" data-key="{{$key}}">{{$i}}</a>
		{{/foreach}}
	</div>
</section>
<section id="edu">
	<p class="m-header s1">请问您的学历是什么？</p>
	<div class="cells col3 clearfix" data-tag="edu">
		{{foreach from=$edu key=key item=item}}
		<a href="javascript:;" data-key="{{$key}}">{{$item}}</a>
		{{/foreach}}
	</div>
</section>
<section id="intro">
	<p class="m-header s1">请描述一下你的内心独白</p>
	<div class="edit">
		<textarea placeholder="说说自己的性格特点、对另一半的期待，或对爱情的憧憬和理解等" data-tag="intro">{{if isset($uInfo['intro'])}}{{$uInfo['intro']}}{{/if}}</textarea>
		<span class="count" style="display: none">10/150</span>
	</div>
	<div class="btn-s-wrap">
		<a href="javascript:;" class="btn-s s3" tag="intro">下一步</a>
	</div>
</section>
<section id="scope">
	<p class="m-header s1">请问您的行业是什么？</p>
	<div class="cells col3 clearfix" data-tag="scope">
		{{foreach from=$scope key=key item=item}}
		<a href="javascript:;" data-key="{{$key}}">{{$item}}</a>
		{{/foreach}}
	</div>
</section>
<section id="job">
	<p class="m-header s1">请问您的职业是什么？</p>
	<div class="cells col3 clearfix professions" data-tag="job">
	</div>
</section>
<section id="house">
	<p class="m-header s1">请问您是否有住房？</p>
	<div class="cells col2 clearfix" data-tag="house">
		{{foreach from=$house key=key item=item}}
		<a href="javascript:;" data-key="{{$key}}">{{$item}}</a>
		{{/foreach}}
	</div>
</section>
<section id="car">
	<p class="m-header s1">请问您是否有车？</p>
	<div class="cells col2 clearfix" data-tag="car">
		{{foreach from=$car key=key item=item}}
		<a href="javascript:;" data-key="{{$key}}">{{$item}}</a>
		{{/foreach}}
	</div>
</section>
<section id="smoke">
	<p class="m-header s1">请问您是否吸烟？</p>
	<div class="cells col2 clearfix" data-tag="smoke">
		{{foreach from=$smoke key=key item=item}}
		<a href="javascript:;" data-key="{{$key}}">{{$item}}</a>
		{{/foreach}}
	</div>
</section>
<section id="drink">
	<p class="m-header s1">请问您是否饮酒？</p>
	<div class="cells col2 clearfix" data-tag="drink">
		{{foreach from=$drink key=key item=item}}
		<a href="javascript:;" data-key="{{$key}}">{{$item}}</a>
		{{/foreach}}
	</div>
</section>
<section id="belief">
	<p class="m-header s1">请问您是否有宗教信仰？</p>
	<div class="cells col2 clearfix" data-tag="belief">
		{{foreach from=$belief key=key item=item}}
		<a href="javascript:;" data-key="{{$key}}">{{$item}}</a>
		{{/foreach}}
	</div>
</section>
<section id="workout">
	<p class="m-header s1">请问您是否有健身的习惯？</p>
	<div class="cells col2 clearfix" data-tag="workout">
		{{foreach from=$workout key=key item=item}}
		<a href="javascript:;" data-key="{{$key}}">{{$item}}</a>
		{{/foreach}}
	</div>
</section>
<section id="diet">
	<p class="m-header s1">请问您的饮食习惯？</p>
	<div class="cells col2 clearfix" data-tag="diet">
		{{foreach from=$diet key=key item=item}}
		<a href="javascript:;" data-key="{{$key}}">{{$item}}</a>
		{{/foreach}}
	</div>
</section>
<section id="rest">
	<p class="m-header s1">请问您的作息习惯？</p>
	<div class="cells col2 clearfix" data-tag="rest">
		{{foreach from=$rest key=key item=item}}
		<a href="javascript:;" data-key="{{$key}}">{{$item}}</a>
		{{/foreach}}
	</div>
</section>
<section id="pet">
	<p class="m-header s1">请问您养宠物吗？</p>
	<div class="cells col2 clearfix" data-tag="pet">
		{{foreach from=$pet key=key item=item}}
		<a href="javascript:;" data-key="{{$key}}">{{$item}}</a>
		{{/foreach}}
	</div>
</section>
<section id="interest">
	<p class="m-header s1 intro">请介绍一下你的兴趣爱好</p>
	<div class="edit">
		<textarea placeholder="例如：健身、旅行、电影、音乐" data-tag="interest">{{if isset($uInfo['interest'])}}{{$uInfo['interest']}}{{/if}}</textarea>
		<span class="count" style="display: none">0/150</span>
	</div>
	<div class="btn-s-wrap">
		<a href="javascript:;" class="btn-s s3 btn-done" tag="interest">提交</a>
	</div>
</section>
<div class="m-footer-tip">
	{{if $switchRole}}
	<a href="/wx/match" class="action-sm action-matcher">返回媒婆角色</a>
	{{/if}}
	<a href="#interest" class="action-sm action-skip" style="display: none">跳过，以后再填</a>
</div>

<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>
<input type="hidden" id="cCoord">
<input type="hidden" id="cMaxYear" value="{{$maxYear}}">
<input type="hidden" id="cGender" value="{{if $uInfo.gender}}{{$uInfo.gender}}{{/if}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script>
	var mProvinces = {{$provinces}},
		mRoutes = {{$routes}},
		mProfessions = {{$professions}};
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/sreg.js?v=1.1.8" src="/assets/js/require.js"></script>