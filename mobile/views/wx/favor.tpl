
<style>
	.rank-tab {
		display: flex;
		padding: 1rem 1rem 0 1rem;
		border-bottom: 1px solid #d8d8d8;
	}

	.rank-tab a {
		flex: 1;
		text-align: center;
		font-size: 1.2rem;
		color: #6a6572;
		padding-bottom: 1.5rem;
		position: relative;
	}

	.rank-tab a.active {
		color: #f06292;
	}
	.rank-tab a.active:after{
		content: "";
		position: absolute;
		bottom: 0;
		left: 3rem;
		right: 3rem;
		height: 3px;
		background: #f487ac;
	}

</style>

<div class="nav">
	<a href="#sme">返回</a>
</div>
<div class="rank-tab">
	<a href="javascript:;" class="active" rank-tag="favor-all">总排行榜</a>
	<a href="javascript:;" rank-tag="favor-week">周排行榜</a>
</div>

<div class="favor-item favor-top">
	<div class="favor-key"></div>
	<div class="favor-avatar"><img src="{{$mInfo.avatar}}"></div>
	<div class="favor-right">
		<div class="title" style="top: 0">{{$mInfo.uname}}</div>
		<div class="dt" style="color: #999">{{if $mInfo.no}}第{{$mInfo.no}}名{{else}}还没有排名{{/if}}</div>
	</div>
	<div class="favor-wAmt">
		{{if $mInfo.co}}
		{{$mInfo.co}}
		{{if $mInfo.todayFavor}} <span>+{{$mInfo.todayFavor}}</span>{{/if}}
		{{/if}}
	</div>
</div>
<ul class="favor-rank">
	{{foreach from=$items item=item}}
	<li>
		<a href="/wx/sh?id={{$item.secretId}}" class="favor-item favor-read">
			<div class="favor-key">{{$item.key}}</div>
			<div class="favor-avatar"><img src="{{$item.avatar}}"></div>
			<div class="favor-right">
				<div class="title">{{$item.uname}}</div>
			</div>
			<div class="favor-wAmt">{{$item.co}}{{if $item.todayFavor}} <span>+{{$item.todayFavor}}</span>{{/if}}</div>
		</a>
	</li>
	{{/foreach}}
</ul>
<div class="spinner" style="display: none"></div>
<div class="no-more" style="display: none;">没有更多了~</div>
<div class="place-holder"></div>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/favor.js?v=1.1.8" src="/assets/js/require.js"></script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script type="text/template" id="tpl_favor">
	{[#items]}
	<li>
		<a href="/wx/sh?id={[$item.secretId]}" class="favor-item favor-read">
			<div class="favor-key">{[key]}</div>
			<div class="favor-avatar"><img src="{[avatar]}"></div>
			<div class="favor-right">
				<div class="title">{[uname]}</div>
			</div>
			<div class="favor-wAmt">{[co]} {[#todayFavor]}<span>+{[todayFavor]}</span>{[/todayFavor]}</div>
		</a>
	</li>
	{[/items]}
</script>

<script type="text/html" id="tpl_favor_top">
	{[#mInfo]}
	<div class="favor-key"></div>
	<div class="favor-avatar"><img src="{[avatar]}"></div>
	<div class="favor-right">
		<div class="title" style="top: 0">{[uname]}</div>
		<div class="dt" style="color: #999">{[#no]}第{[no]}名{[/no]}{[^no]}还没有排名{[/no]}</div>
	</div>
	<div class="favor-wAmt">
		{[#co]}
		{[co]}
		{[#todayFavor]} <span>+{[todayFavor]}</span>{[/todayFavor]}
		{[/co]}
	</div>
	{[/mInfo]}
</script>