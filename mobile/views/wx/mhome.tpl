<section id="shome">
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
			<a href="/wx/sh?id={{$single.encryptId}}" data-id="{{$single.encryptId}}">
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
</section>
<section id="sprofile">
	<div class="sprofile-top">
		<div class="img">
			<img src="" alt="">
		</div>
		<div class="sprofile-top-des">
			<p>Caroline<em class="icon-female"></em></p>
			<i>30岁 . 165cm . 处女座 . 金融 </i>
			<span>北京 大兴</span>
		</div>
	</div>
	<div class="sprofile-album">
		<a class="title" tag="album">相册(0)</a>
		<ul></ul>
	</div>
	<div class="sprofile-base">
		<div class="title">基本资料</div>
		<a class="content" tag="baseInfo">
			<span>165cm</span>
			<span>本科</span>
			<span>2w-3w</span>
			<span>计划购房</span>
			<span>已购车</span>
		</a>
	</div>
	<div class="sprofile-mp">
		<div class="left">
			<div class="img">
				<img src="" alt="">
			</div>
			<p>思彬</p>
			<i>渠道拓展</i>
		</div>
		<div class="right">
			这个女的很棒！！
		</div>
	</div>
	<div class="sprofile-condtion">
		<div class="title">择偶条件</div>
		<div class="content">
			<span>25-40岁</span>
			<span>172-198cm</span>
			<span>收入不限</span>
			<span>本科及以上</span>
		</div>
	</div>
	<div class="sprofile-intro">
		<div class="title">内心独白</div>
		<div class="content">
			梦想周游世界，希望有人陪我一起
		</div>
	</div>
	<div class="sprofile-forbid">
		<a href="javascript:;" tag="forbid"><span class="icon-l icon-forbid"></span>举报拉黑</a>
	</div>
	<div class="sprofile-bottom">
		<a href="javascript:;" tag="love"><span class="icon-l icon-love"></span>心动</a>
		<a href="javascript:;" tag="wechat">加微信聊聊</a>
	</div>
</section>
<a href="/wx/rpt?id={{$hid}}" class="report">举报</a>
<a style="display: none" class="rcmd gradient-h"><span>推荐TA的单身团</span></a>
<input type="hidden" id="cUID" value="{{$hid}}">
<input type="hidden" id="avatarID" value="{{$uInfo.thumb}}">
<input type="hidden" id="secretId" value="{{$secretId}}">
<input type="hidden" id="nameId" value="{{$uInfo.name}}">

<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script type="text/template" id="tpl_single">
	{[#items]}
	<a href="/wx/sh?id={[encryptId]}" data-id="{[encryptId]}">
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
<script data-main="/js/mhome.js?v=1.1.4" src="/assets/js/require.js"></script>