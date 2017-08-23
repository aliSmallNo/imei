<style>

	.vote-title {
		padding: 2rem 2rem 0 2rem;
	}

	.vote-title p {
		font-size: 1.2rem;
		line-height: 2rem;
		letter-spacing: .1rem;
	}

	.vote {
		background: #f8f8f8;
		margin: 1rem;
		padding: 2rem;
		border: 1px solid #eee;
	}

	.vote-item {

	}

	.vote-item h4 {
		/*height: 4rem;
		line-height: 4rem;*/
		font-size: 1.3rem;
		margin-bottom: 1rem;
	}

	.vote-item .opt-res {
		padding: 1rem 0;
	}

	.opt-res-list {
		display: flex;
		padding: .3rem 0 .8rem 0;
	}

	.opt-res-list div.pro {
		flex: 1;
		background: #eee;
		margin-right: 1rem;
		height: .3rem;
		align-items: center;
		align-self: center;
		border-radius: .5rem;
	}

	.opt-res-list div.pro div {
		background: #f06292;
		height: .3rem;
		border-radius: .5rem;
	}

	.opt-res-list div.opt-res-list-r {
		flex: 0 0 4rem;
		font-size: 1rem;
		color: #777;
	}


	.vote-btn {
		margin: 0 1rem 3rem 1rem;
		background: #f8f8f8;
		text-align: center;
		height: 4rem;
		line-height: 4rem;
		position: relative;
		top: -1rem;
		border: 1px solid #eee;
		border-top: 0;
	}

	.vote-btn a {
		display: block;
		font-size: 1.2rem;
		color: #777;
	}
</style>
<div class="vote-title">
	<p>小微要组织一场活动，不知各位帅哥美女喜欢什么样的，那就一起来投票吧（投票有惊喜哦）。我们会根据大家的喜好，组织线下活动哦，欢迎参加</p>
</div>
<div class="vote">
	{{foreach from=$voteStat key=key item=item}}
	<div class="vote-item">
		<h4>{{$key+1}}.{{$item.qTitle}}</h4>
		<div class="opt-res">
			{{foreach from=$item.options item=opt}}
			<h5>{{$opt.text}}{{if $opt.choose}}(已选){{/if}}</h5>
			<div class="opt-res-list">
				<div class="pro">
					<div style="width: {{if $item.amt>0}}{{(($opt.co/$item.amt)|string_format:"%.2f")*100}}%{{else}}0%{{/if}};"></div>
				</div>
				<div class="opt-res-list-r">{{$opt.co}}票</div>
				<div class="opt-res-list-r">{{if $item.amt>0}}{{(($opt.co/$item.amt)|string_format:"%.2f")*100}}%{{else}}0%{{/if}}</div>
			</div>
			{{/foreach}}
		</div>
	</div>
	{{/foreach}}

</div>
<div class="vote-btn">
	<a>已投票</a>
</div>

<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/voted.js?v=1.4.10" src="/assets/js/require.js"></script>

