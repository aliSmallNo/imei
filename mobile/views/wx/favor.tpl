<style>
	.notice-key {
		flex: 0 0 2rem;
		font-size: 1.5rem;
		position: relative;
		top: .5rem;
	}

	.notice-right {
		flex: 2 !important;
	}

	.notice-right .title {
		position: relative;
		top: .5rem;
	}

	.notice-wAmt {
		flex: 1;
		font-size: 2rem;
		color: #f7a4c0;
	}

	.notice-wAmt span {
		font-size: 1.2rem;
		color: #fa6799;
	}

	.favor-top {
		padding: 1rem;
		border-bottom: .2rem solid #eee;
	}

	.favor-top:after {
		border: none;
	}
</style>
<div class="nav">
	<a href="single#sme">返回</a>
	<a href="#sme" style="display: none">个人中心</a>
</div>
<div class="notice-item favor-top">
	<div class="notice-key"></div>
	<div class="notice-avatar"><img src="{{$mInfo.avatar}}"></div>
	<div class="notice-right">
		<div class="title" style="top: 0">{{$mInfo.uname}}</div>
		<div class="dt" style="color: #999">{{if $mInfo.no}}第{{$mInfo.no}}名{{else}}还没有排名{{/if}}</div>
	</div>
	<div class="notice-wAmt">
		{{if $mInfo.co}}{{$mInfo.co}}</span>
		{{else}}

		{{/if}}
	</div>
</div>
<ul class="notice">
	{{foreach from=$items item=item}}
	<li>
		<a href="/wx/sh?id={{$item.secretId}}" class="notice-item notice-read">
			<div class="notice-key">{{$item.key}}</div>
			<div class="notice-avatar"><img src="{{$item.avatar}}"></div>
			<div class="notice-right">
				<div class="title">{{$item.uname}}</div>
			</div>
			<div class="notice-wAmt">{{$item.co}} <span>+{{$item.todayFavor}}</span></div>
		</a>
	</li>
	{{/foreach}}
</ul>
<div class="spinner" style="display: none"></div>
<div class="no-more" style="display: none;">没有更多了~</div>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/favor.js?v=1.1.5" src="/assets/js/require.js"></script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script type="text/template" id="tpl_favor">
	{[#items]}
	<li>
		<a href="/wx/sh?id={[$item.secretId]}" class="notice-item notice-read">
			<div class="notice-key">{[key]}</div>
			<div class="notice-avatar"><img src="{[avatar]}"></div>
			<div class="notice-right">
				<div class="title">{[uname]}</div>
			</div>
			<div class="notice-wAmt">{[co]} <span>+{[todayFavor]}</span></div>
		</a>
	</li>
	{[/items]}
</script>