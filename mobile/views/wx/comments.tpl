<style>
	.comment-bg {
		background: #fff;
	}

	.comment {
		background: #fff;
		padding: 2rem 1rem;
	}

	.comment li {
		border-bottom: 1px solid #ddd;
		margin-bottom: .5rem;
		padding-bottom: .5rem;
	}

	.comment li p {
		font-size: 1.2rem;
	}

	.comment li span {
		font-size: 1rem;
		color: #777;
	}

	.comment div {
		text-align: center;
		font-size: 1.2rem;
	}

	.comment-no-more {
		font-size: 1.2rem;
		text-align: center;
		color: #777;
	}
</style>
<ul class="comment">
	{{if $items}}
	{{foreach from=$items item=item}}
	<li>
		<p>对我〖{{$item.cat}}〗的评价：{{$item.cComment}}</p>
		<span>{{$item.dt}}</span>
	</li>
	{{/foreach}}
	{{else}}
	<div>还没有人对我评价哦~</div>
	{{/if}}
</ul>
<div class="comment-no-more" style="display: {{$nomore}}">没有更多了~</div>

<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/comments.js?v=1.1.8" src="/assets/js/require.js"></script>
