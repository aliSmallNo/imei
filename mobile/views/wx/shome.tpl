<div class="single-page">
	<div class="head">
		<img src="{{$uInfo.avatar}}" alt="">
	</div>
	<div class="baseinfo">
		<div class="title">
			<h4><em>{{$uInfo.name}}</em> <i class="icon female"></i></h4>
			<h5>{{$uInfo.location_t}}</h5>
		</div>
		<h6>34岁 . 162cm . 水瓶座 . 其他</h6>
	</div>
	<a href="javascript:;" class="album-row line-bottom2">
		<ul class="photos">
			<li class="title">
				相册(222)
			</li>
			<li>
				<img src="https://img.1meipo.com/731c5fb440f7f7bce155cf038b9a4e35.jpeg?x-oss-process=image/resize,m_fill,w_200,h_200,limit_0/auto-orient,0/quality,q_100">
			</li>
			<li>
				<img src="https://img.1meipo.com/b6954ef743549241b603e7a8a4337ea3.jpeg?x-oss-process=image/resize,m_fill,w_200,h_200,limit_0/auto-orient,0/quality,q_100">
			</li>
			<li>
				<img src="https://img.1meipo.com/b6954ef743549241b603e7a8a4337ea3.jpeg?x-oss-process=image/resize,m_fill,w_200,h_200,limit_0/auto-orient,0/quality,q_100">
			</li>
		</ul>
	</a>
	<div class="single-info">
		<a>
			<span class="title">基本资料</span>
			<ul class="clearfix">
				<li>162cm</li>
				<li>本科</li>
				<li>2k以下</li>
				<li>已购车</li>
			</ul>
		</a>
	</div>
	<div class="hnwords">
		<div class="hninfo">
			<a href="/hn/p?uid=3226a9a8fdbe475e9e0f22d7d5e65de1" class="">
				<div class="img">
					<img src="https://img.1meipo.com/78a38e5c680e577b7aa826a6615f4442.jpg?x-oss-process=image/resize,m_fill,w_200,h_200,limit_0/auto-orient,0/quality,q_100">
				</div>
			</a>
			<p class="name">贺宝茹</p>
			<p class="desc">珠宝</p>
		</div>
		<div class="wcontent">
			<p class="words">外表温柔内在坚强爱珠宝的(财女)才女</p>
		</div>
	</div>
	<div class="mywords">
		<span class="title">内心独白</span>
		<span class="words">臭味相投</span>
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
<script data-main="/js/mhome.js?v=1.1.1" src="/assets/js/require.js"></script>