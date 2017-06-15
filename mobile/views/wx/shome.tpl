<div class="single-page main-page">
	<div class="head">
		<img src="{{$uInfo.avatar}}" alt="">
	</div>
	<div class="baseinfo">
		<div class="title">
			<h4><em>{{$uInfo.name}}</em> <i class="icon female"></i></h4>
			<h5>{{$uInfo.location_t}}</h5>
		</div>
		<h6>{{$brief}}</h6>
	</div>
	{{if $uInfo.album}}
	<a href="javascript:;" class="album-row line-bottom2" album-string='{{$uInfo.albumJson}}'>
		<ul class="photos">
			<li class="title">
				相册({{$uInfo.album_cnt}})
			</li>
			{{foreach from=$uInfo.album item=item name=foo}}
			{{if $smarty.foreach.foo.index <3}}
			<li>
				<img src="{{$item}}">
			</li>
			{{/if}}
			{{/foreach}}
		</ul>
	</a>
	{{/if}}
	<div class="single-info">
		<a href="/wx/sd?id={{$uInfo.encryptId}}">
			<span class="title">基本资料</span>
			<ul class="clearfix">

				{{foreach from=$baseInfo item=item}}
				<li>{{$item}}</li>
				{{/foreach}}
			</ul>
		</a>
	</div>
	<div class="hnwords">
		<div class="hninfo">
			<a href="/hn/p?uid={{$uInfo.encryptId}}" class="">
				<a href="/wx/mh?id={{$uInfo.mp_encrypt_id}}#shome" class="">
					<div class="img">
						<img src="{{$uInfo.mp_thumb}}">
					</div>
				</a>
				<p class="name">{{$uInfo.mp_name}}</p>
				<p class="desc">{{$uInfo.mp_scope}}</p>
		</div>
		<div class="wcontent">
			<p class="words">{{$uInfo.comment}}</p>
		</div>
	</div>
	<div class="mywords">
		<span class="title">内心独白</span>
		<span class="words">{{$uInfo.intro}}</span>
	</div>
	<a href="#sreport" class="report pushblack">举报拉黑</a>
	<div style="height: 6rem;"></div>
	<div class="m-bottom-bar">
		<p><a class="heart btn-like {{if $uInfo.favorFlag}}favor{{/if}}" data-id="{{$uInfo.encryptId}}">{{if $uInfo.favorFlag}}已心动{{else}}心动{{/if}}</a></p>
		<p><a class="weixin btn-apply">加微信聊聊</a></p>
	</div>
</div>
<section id="sreport">
	<div class="report_wrap">
		<h3>举报用户</h3>
		<label>用户昵称: <b>{{$uInfo.name}}</b></label>
		<div class="select">
			<span>请选择举报原因</span>
			<select class="report-reason">
				{{foreach from=$reasons item=reason}}
				<option value="{{$reason}}">{{$reason}}</option>
				{{/foreach}}
			</select>
		</div>
		<label>详细情况补充（选填）</label>
		<textarea placeholder="详细信息" class="report-text"></textarea>
		<a class="m-next btn-report">提交</a>
	</div>
</section>

<div class="app-cork" style="background-color: rgba(0,0,0,0.7);display: none"></div>
<div class="getWechat">
	<div class="getw-content">
		<a class="icon-alert icon-close" tag="close"></a>
		<div class="input">
			<input type="text" placeholder="请输入您的微信号">
		</div>
		<div class="getw-about">
			<p>1、微信号仅用于双方同意后，发送给彼此，请放心填写</p>
			<p>2、为了确保对方可以搜到您，请确保"微信-我-隐私"中"通过微信号搜到我"选项处于打开状态</p>
			<p>3、填写虚假微信号，会被平台封禁处理</p>
		</div>
		<a href="javascript:;" class="btn" tag="btn-confirm">确认</a>
	</div>
</div>
<div class="pay-mp">
	<p class="pmp-title">申请加微信</p>
	<p class="pmp-title-des">若对方拒绝，媒瑰花退回</p>
	<a class="close" tag="close"></a>
	<ul class="options">
		<li>
			<a href="javascript:;" num="50" tag="choose">
				<div class="img"><img src="/images/ico_rose.png"></div>
				<div class="des">
					<div class="t">x 50<span>朵</span></div>
					<div class="b">有一点心动</div>
				</div>
			</a>
		</li>
		<li>
			<a href="javascript:;" num="100" tag="choose">
				<div class="img"><img src="/images/ico_rose.png"></div>
				<div class="des">
					<div class="t">x 100<span>朵</span></div>
					<div class="b">来电了</div>
				</div>
			</a>
		</li>
		<li>
			<a href="javascript:;" num="500" tag="choose">
				<div class="img"><img src="/images/ico_rose.png"></div>
				<div class="des">
					<div class="t">x 500<span>朵</span></div>
					<div class="b">喜欢你</div>
				</div>
			</a>
		</li>
		<li>
			<a href="javascript:;" num="1000" tag="choose">
				<div class="img"><img src="/images/ico_rose.png"></div>
				<div class="des">
					<div class="t">x 1000<span>朵</span></div>
					<div class="b">诚意满满</div>
				</div>
			</a>
		</li>
	</ul>
	<div class="pmp-pay">
		<a href="javascript:;" tag="pay">打赏媒婆</a>
	</div>
	<div class="pmp-bot">
		<a tag="des">感谢对方媒婆推荐了这么好的人</a>
		<ol>
			<li>对方拒绝了给微信号，媒瑰花全部返还</li>
			<li>对方同意了给微信号，媒瑰花将打给对方媒婆</li>
			<li>对方若无回应，7天后媒瑰花如数返还</li>
		</ol>
	</div>
</div>
<div class="not-enough-rose">
	<p>您的媒瑰花余额：<span class="rose-num">30</span>朵</p>
	<p>不够打赏？马上去充值!</p>
	<div class="btns">
		<a href="javascript:;" tag="cancel">取消</a>
		<a href="javascript:;" tag="recharge">去充值</a>
	</div>
	<p></p>
</div>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>

>>>>>>> e2fb52650249de12696105db2e8500b751f82432
<input type="hidden" id="cUID" value="{{$hid}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script type="text/template" id="tpl_single">
	{[#items]}
	<a href="/wx/sh?id={[encryptId]}">
		<div class="img">
			<img src="{[thumb]}" alt="">
			<span class="location">{[location_t]}</span>
		</div>
		<p class="name"><em>{[name]}</em> <i class="icon {[gender_ico]}"></i></p>
		<p class="intro">
			{[#notes]}<span>{[.]}</span> {[/notes]}
			<span>26岁</span> <span>.</span> <span>168cm</span> <span>.</span> <span>1w~1.5w</span> <span>.</span> <span>处女座</span>
		</p>
	</a>
	{[/items]}
</script>
<script>
	var mItems = {{$items}};
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/shome.js?v=1.1.2" src="/assets/js/require.js"></script>