<div class="progress">
	<div style="width: 0%;"></div>
</div>
<div class="progress-tip">
	<p class="title"></p>
	<a href="javascript:;" class="action-sm action-skip" style="display: none">跳过，以后再填</a>
</div>
<div class="m-popup-shade"></div>
<section id="photo">
	<p class="m-header">请上传真人头像<i>否则不会审核通过</i></p>
	<div class="nick_name">
		<a href="javascript:;" class="photo photo-file">
			<img class="avatar" src="{{$avatar}}" localId="">
		</a>
		<label class="f-inline">
			<em>昵称：</em>
			<input type="text" placeholder="昵称" class="input-s big nickname" value="{{$nickname}}">
		</label>
		<div class="place-holder-s2">
			<a href="javascript:;" class="btn-s s3" tag="avatar">下一步</a>
		</div>
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
<section id="homeland">
	<p class="m-header s1">您的籍贯</p>
	<a href="javascript:;" class="action-row">
		<div class="homeland homeland-row" data-tag="homeland">
			{{foreach from=$locInfo item=item}}
			<em data-key="{{$item.key}}">{{$item.text}}</em>
			{{/foreach}}
		</div>
	</a>
	<div class="btn-s-wrap">
		<a href="javascript:;" class="btn-s s3" tag="homeland">下一步</a>
	</div>
</section>
<section id="location">
	<p class="m-header s1">您的位置</p>
	<a href="javascript:;" class="action-row">
		<div class="location location-row" data-tag="location">
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
	<div class="cells col2 clearfix" data-tag="horos">
		{{foreach from=$horos key=key item=y}}
		<a href="javascript:;" data-key="{{$key}}">{{$y}}</a>
		{{/foreach}}
	</div>
</section>
<section id="marital">
	<p class="m-header s1">您的婚姻状态？</p>
	<div class="cells col3 clearfix" data-tag="marital">
		{{foreach from=$marital key=key item=y}}
		<a href="javascript:;" data-key="{{$key}}">{{$y}}</a>
		{{/foreach}}
	</div>
</section>
<section id="height">
	<p class="m-header s1">请问您的身高是多少？</p>
	<div class="cells col5 clearfix" data-tag="height">
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
<section id="album">
	<p class="m-header s1">请上传2张生活照片吧</p>
	<ul class="j-album clearfix">
		{{foreach from=$uInfo.album item=item}}
		<li>
			<a href="javascript:;" class="{{if $item}}active{{/if}}">
				<img src="{{if $item}}{{$item}}{{/if}}" alt="">
				<div class="j-img-shade"></div>
			</a>
		</li>
		{{/foreach}}
	</ul>
	<div class="place-holder-s2">
		<a href="javascript:;" class="btn-s s3" tag="album">下一步</a>
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
<section id="profession">
	<p class="m-header s1">请问您的职业是什么？</p>
	<div class="cells col3 clearfix professions" data-tag="profession">
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
<section id="cert">
	<p class="m-header s1">请做实名认证吧</p>
	<div class="cert-content">
		<div class="cert-header">
			上传您手持身份证的照片，审核通过后，会给你加V哦，大大提高您的信誉和牵手成功率哦~
		</div>
		<div class="cert-img">
			<div class="cert-bg" style="background: url('{{$certImage}}') no-repeat center center;background-size: 100% 100%;">
				<div></div>
			</div>
			<a href="javascript:;" class="choose-img"></a>
		</div>
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
	{{if $switchRole && 0}}
	<a href="/wx/match" class="action-sm action-matcher">返回媒婆角色</a>
	{{/if}}

</div>

<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>

<input type="hidden" id="cCoord">
<input type="hidden" id="cSkipIndex" value="{{$skipIndex}}">
<input type="hidden" id="cMaxYear" value="{{$maxYear}}">
<input type="hidden" id="cGender" value="{{if $uInfo.gender}}{{$uInfo.gender}}{{/if}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script type="text/template" id="tpl_skip">
	<div class="m-greeting-wrap">
		<h4>
			资料不全，将会被限制使用部分功能<br>
			你是否确定要跳过填写剩下的资料？
		</h4>
		<div class="btn-wrap btn-multi">
			<a href="javascript:;" class="btn btn-warn btn-skip-yes">我先进去看看</a>
			<a href="javascript:;" class="btn btn-main btn-skip-no">不，我要填完</a>
		</div>
	</div>
</script>
<script type="text/template" id="tpl_greeting_users">
	<div class="m-greeting-wrap">
		<h4>微媒100为你推荐</h4>
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
<script>
	var mProvinces = {{$provinces}},
		mRoutes = {{$routes}},
		mProfessions = {{$professions}};
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script src="//webapi.amap.com/maps?v=1.3&key=8dcdd1499361b46052bb94a1dfafbe49&plugin=AMap.Geocoder"></script>
<script data-main="/js/sreg.js?v=1.4.2" src="/assets/js/require.js"></script>