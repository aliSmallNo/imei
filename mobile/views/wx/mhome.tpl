<div class="home-header">
	<div class="nickphoto">
			<span>
				<img src="{{$uInfo.thumb}}" alt="">
			</span>
	</div>
	<h4><em>{{$uInfo.name}}</em> {{if $uInfo.vip}}<span class="vip big"></span>{{/if}}</h4>
	<h5>{{$uInfo.intro}}</h5>
	<p class="desc"></p>
	<a href="javascript:;" class="follow">关注TA</a>
	<div class="countInfo">
		<div><span>单身团</span><em>12</em></div>
		<div><span>牵线成功</span><em>61</em></div>
		<div><span>粉丝</span><em>405</em></div>
	</div>
</div>
<div class="m-tab-wrap">
	<div class="m-tabs">
		<a href="javascript:;" {{if $prefer=="male"}}class="active"{{/if}} data-tag="male">
			<span>男生(10)</span>
		</a>
		<a href="javascript:;" {{if $prefer=="female"}}class="active"{{/if}} data-tag="female">
			<span>女生(6)</span>
		</a>
	</div>
	<div class="users2 clearfix"></div>
	<div class="spinner" style="display: none"></div>
	<div class="no-more" style="display: none;">没有更多了~</div>
</div>

<a href="/report?uid=350c88afa7d64c52b444735fafc7373c&amp;name=%E5%B0%B9%E5%B3%B0" class="report">举报</a>

<a class="rcmd gradient-h"><span>推荐TA的单身团</span></a>
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
<script data-main="/js/mhome.js?v=1.1.1" src="/assets/js/require.js"></script>