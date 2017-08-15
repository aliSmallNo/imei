<div class="nav">
	<a href="single#sme">返回</a>
	<a href="#sme" style="display: none">个人中心</a>
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
			<div class="favor-right">{{$item.uname}}</div>
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
<script data-main="/js/fans-rank.js?v=1.1.6" src="/assets/js/require.js"></script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script type="text/template" id="tpl_favor">
	{[#items]}
	<li>
		<a href="/wx/sh?id={[secretId]}" class="favor-item favor-read">
			<div class="favor-key">{[key]}</div>
			<div class="favor-avatar"><img src="{[avatar]}"></div>
			<div class="favor-right">
				<div class="title">{[uname]}</div>
			</div>
			<div class="favor-wAmt">{[co]} <span>+{[todayFavor]}</span></div>
		</a>
	</li>
	{[/items]}
</script>