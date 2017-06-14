<div class="single-page">
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
	<a href="javascript:;" class="album-row line-bottom2">
		<ul class="photos">
			<li class="title">
				相册({{$uInfo.album_cnt}})
			</li>
			{{foreach from=$uInfo.album item=item name=foo}}
			<li>
				<img src="{{$item}}">
			</li>
			{{/foreach}}
		</ul>
	</a>
	{{/if}}
	<div class="single-info">
		<a>
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
	<span class="report pushblack">举报拉黑</span>
	<div style="height: 50px;"></div>
	<div class="m-bottom-bar">
		<p><a class="heart">心动</a></p>
		<p><a class="weixin">加微信聊聊</a></p>
	</div>
</div>
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
<script data-main="/js/shome.js?v=1.1.1" src="/assets/js/require.js"></script>