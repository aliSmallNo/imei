<div class="nav">
	<a href="single#sme">返回</a>
	<a href="#sme" style="display: none">个人中心</a>
</div>
<ul class="notices"></ul>
<div class="spinner" style="display: none"></div>
<div class="no-more" style="display: none;">没有更多了~</div>
<script type="text/template" id="tpl_notice">
	{[#items]}
	<li >
		<a data-url="{[url]}" data-id="{[mId]}" data-readflag="{[readflag]}" data-cat="{[mCategory]}"
			 class="cell-subtitle {[#readflag]}read{[/readflag]}">
			<div class="avatar"><img src="{[avatar]}"></div>
			<div class="content">
				<h5>{[text]}</h5>
				<h6>{[dt]}</h6>
			</div>
		</a>
	</li>
	{[/items]}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/notice.js?v=1.1.5" src="/assets/js/require.js"></script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
