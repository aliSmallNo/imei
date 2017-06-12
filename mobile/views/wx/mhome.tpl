<div class="home-header">
	<div class="nickphoto">
			<span>
				<img src="{{$uInfo.thumb}}" alt="">
			</span>
	</div>
	<h4><em>{{$uInfo.name}}</em> {{if $uInfo.vip}}<span class="vip big"></span>{{/if}}</h4>
	<h5>{{$uInfo.intro}}</h5>
	<p class="desc"></p>
	<a href="javascript:;" class="follow">{{$followed}}</a>
	<div class="countInfo">
		<div><span>单身团</span><em>{{$stat.single}}</em></div>
		<div><span>牵线成功</span><em>{{$stat.link}}</em></div>
		<div><span>粉丝</span><em>{{$stat.fans}}</em></div>
	</div>
</div>
<div class="m-tab-wrap">
	<div class="m-tabs">
		<a href="javascript:;" {{if $prefer=="male"}}class="active"{{/if}} data-tag="male">
			<span>男生({{$stat.male}})</span>
		</a>
		<a href="javascript:;" {{if $prefer=="female"}}class="active"{{/if}} data-tag="female">
			<span>女生({{$stat.female}})</span>
		</a>
	</div>
	<div class="users2 clearfix">
		{{foreach from=$singles item=single}}
		<a href="/wx/sh?id={{$single.encryptId}}">
			<div class="img">
				<img src="{{$single.thumb}}" alt="">
				<span class="location">{{$single.location_t}}</span>
			</div>
			<p class="name"><em>{{$single.name}}</em> <i class="icon {{$single.gender_ico}}"></i></p>
			<p class="intro">
				{{foreach from=$single.notes item=item}}<em>{{$item}}</em>{{/foreach}}
			</p>
		</a>
		{{/foreach}}
	</div>
	<div class="spinner" style="display: none"></div>
	<div class="no-more" style="display: none;">没有更多了~</div>
</div>
<a href="/wx/rpt?id={{$hid}}" class="report">举报</a>
<a style="display: none" class="rcmd gradient-h"><span>推荐TA的单身团</span></a>
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
			{[#notes]}<em>{[.]}</em> {[/notes]}
		</p>
	</a>
	{[/items]}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/mhome.js?v=1.1.2" src="/assets/js/require.js"></script>