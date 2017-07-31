<style>
	.notice {
		padding: 1rem;
	}

	.notice-item {
		display: flex;
		padding: .5rem 0;
		position: relative;
		border-bottom: 1px solid #eee;
	}

	.notice-item .notice-read-flag {
		position: absolute;
		width: .5rem;
		height: .5rem;
		border-radius: .5rem;
		background: red;
		left: 0;
		top: 0;
	}

	.notice-item:after {
		content: " ";
		display: inline-block;
		height: .8rem;
		width: .8rem;
		border-width: 1px 1px 0 0;
		border-style: solid;
		-webkit-transform: matrix(0.71, 0.71, -0.71, 0.71, 0, 0);
		transform: matrix(0.71, 0.71, -0.71, 0.71, 0, 0);
		position: absolute;
		top: 50%;
		margin-top: -0.5rem;
		border-color: #999;
		right: 1rem;
	}

	.notice-item .notice-avatar {
		flex: 0 0 3rem;
	}

	.notice-item .notice-avatar img {
		width: 3rem;
		height: 3rem;
		border-radius: 3rem;
		border: 1px solid #fbd2e0;
	}

	.notice-item .notice-right {
		flex: 1;
		margin: 0 3rem 0 1rem;
	}

	.notice-item .notice-right .title {
		font-weight: 400;
		font-size: 1.2rem;
	}

	.notice-item .dt {
		font-size: 1rem;
		font-weight: 200;
		text-align: left;
	}


</style>
<div class="nav">
	<a href="single#sme">返回</a>
	<a href="#sme" style="display: none">个人中心</a>
</div>
<ul class="notice">
	{{foreach from=$items item=item}}
	<li>
		<a data-url="/wx/{{$item.url}}?id={{$item.secretId}}" data-id="{{$item.mId}}" data-readflag="{{$item.readflag}}" class="notice-item notice-read">
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
		<a data-url="/wx/{[url]}?id={[secretId]}" data-id="{[mId]}" data-readflag="{[readflag]}" class="notice-item notice-read">
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