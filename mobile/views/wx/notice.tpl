<style>
	.notice {
		padding: 1rem;
	}

	.notice-item {
		display: flex;
		padding: 1rem 0;
		position: relative;
		border-bottom: 1px solid #eee;
	}

	.notice-item:after{
		content: " ";
		display: inline-block;
		height: .8rem;
		width: .8rem;
		border-width: 1px 1px 0 0;
		border-style: solid;
		-webkit-transform: matrix(0.71,0.71,-0.71,0.71,0,0);
		transform: matrix(0.71,0.71,-0.71,0.71,0,0);
		position: absolute;
		top: 50%;
		margin-top: -0.5rem;
		border-color: #999;
		right: 1rem;
	}

	.notice-item .title {
		flex: 2;
		font-weight: 500;
		font-size: 1.2rem;
	}

	.notice-item .dt {
		flex: 1;
		font-size: 1rem;
		font-weight: 200;
		text-align: right;
		margin-right: 3rem;
	}
</style>
<div class="nav">
	<a href="single#sme">返回</a>
	<a href="#sme" style="display: none">个人中心</a>
</div>
<ul class="notice">
	{{foreach from=$items item=item}}
	<li>
		<a href="/wx/{{$item.url}}?id={{$item.secretId}}" class="notice-item">
			<div class="title">{{$item.text}}</div>
			<div class="dt">{{$item.dt}}</div>
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
		<a href="/wx/{[url]}?id={[secretId]}" class="notice-item">
			<div class="title">{[text]}</div>
			<div class="dt">{[dt]}</div>
		</a>
	</li>
	{[/items]}
</script>