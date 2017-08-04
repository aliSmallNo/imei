<div class="nav">
	<a href="single#sme">返回</a>
	<a href="#sme" style="display: none">个人中心</a>
</div>
<ul class="notice">
	{{foreach from=$items item=item}}
	<li>
		<a data-url="{{$item.url}}" data-id="{{$item.mId}}" data-readflag="{{$item.readflag}}" data-cat="{{$item.mCategory}}" class="notice-item notice-read">
			<div class="notice-avatar"><img src="{{$item.avatar}}"></div>
			<div class="notice-right">
				<div class="title">{{$item.text}}</div>
				<div class="dt">{{$item.dt}}</div>
			</div>
			{{if $item.readflag==0}}
			<span class="notice-read-flag"></span>
			{{/if}}
		</a>
	</li>
	{{/foreach}}
</ul>
<div class="spinner" style="display: none"></div>
<div class="no-more" style="display: none;">没有更多了~</div>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/notice.js?v=1.1.5" src="/assets/js/require.js"></script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script type="text/template" id="tpl_notice">
	{[#items]}
	<li>
		<a data-url="{[url]}" data-id="{[mId]}" data-readflag="{[readflag]}" data-cat="{[mCategory]}" class="notice-item notice-read">
			<div class="notice-avatar"><img src="{[avatar]}"></div>
			<div class="notice-right">
				<div class="title">{[text]}</div>
				<div class="dt">{[dt]}</div>
			</div>
			{[^readflag]}
			<span class="notice-read-flag"></span>
			{[/readflag]}
		</a>
	</li>
	{[/items]}
</script>