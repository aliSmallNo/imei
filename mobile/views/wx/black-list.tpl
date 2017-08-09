<style>
	.black-list {
		padding: 1rem;
	}

	.black-list .black-item {
		display: flex;
		border-bottom: 1px solid #eee;
	}

	.black-list .black-item .black-avatar {
		flex: 0 0 4rem;
	}

	.black-list .black-item .black-avatar img {
		width: 4rem;
		height: 4rem;
		border-radius: 4rem;
		border: #ee6e73;
	}

	.black-list .black-item .black-right {
		font-size: 1.2rem;
		padding: 1rem;
		flex: 2;
	}

	.black-list .black-item .black-cancel {
		flex: 0 0 8rem;
		display: block;
		outline: 0;
		border: 0;
		color: #fff;
		font-size: 1.2rem;
		background: #4da2ff;
		width: 7rem;
		height: 3rem;
		border-radius: 1.5rem;
		line-height: 3rem;
		text-align: center;
		padding: 0 .8rem;
	}

</style>
<div class="nav">
	<a href="single#sme">返回</a>
	<a href="#sme" style="display: none">个人中心</a>
</div>
<ul class="black-list">
	{{if $items}}
	{{foreach from=$items item=item}}
	<li>
		<a data-id="{{$item.secretId}}" class="black-item">
			<div class="black-avatar"><img src="{{$item.avatar}}"></div>
			<div class="black-right">
				{{$item.uname}}
			</div>
			<button class="black-cancel" data-nid="{{$item.nid}}">取消拉黑</button>
		</a>
	</li>
	{{/foreach}}
	{{else}}

	{{/if}}
</ul>
<div class="spinner" style="display: none"></div>
<div class="no-more" style="display: none;">没有更多了~</div>
<input type="hidden" id="pageId" value="{{$nextpage}}">
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/black-list.js?v=1.1.5" src="/assets/js/require.js"></script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script type="text/template" id="tpl_black">
	{[#items]}
	<li>
		<a data-id="{[secretId]}" class="black-item">
			<div class="black-avatar"><img src="{[avatar]}"></div>
			<div class="black-right">
				{[uname]}
			</div>
			<button class="black-cancel" data-nid="{[nid]}">取消拉黑</button>
		</a>
	</li>
	{[/items]}
</script>