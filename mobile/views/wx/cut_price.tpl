<style>
	.cut_content {
		background: url('/images/cut_price/cut_bg.jpg') no-repeat center center;
		background-size: 100% 100%;
		width: 100%;
		padding-bottom: 3rem;
	}

	.cut_card, .cut_price, .cut_one_dao, .cut_get_free {
		text-align: center;
	}

	.cut_card {
		padding-top: 17rem;
		position: relative;
	}

	.cut_card img {
		width: 250px;
	}

	.cut_card .cut_price {
		color: #fff;
		position: absolute;
		bottom: 1rem;
		left: 0;
		right: 0;
		text-align: center;
		font-size: 1.8rem;
	}

	.cut_one_dao div {
		background: url('/images/cut_price/cut_invite.png') no-repeat center center;
	}

	.cut_get_free div {
		background: url('/images/cut_price/cut_get.png') no-repeat center center;
	}

	.cut_one_dao div,
	.cut_get_free div {
		background-size: 100% 100%;
		width: 24rem;
		height: 5rem;
		display: inline-block;
	}

	.cut_one_dao div span,
	.cut_get_free div span {
		position: relative;
		top: 1rem;
	}

	.cut_items {
		background: #d0d3fe;
		margin: 2rem;
		border-radius: 1rem;
		padding-bottom: 2rem;
	}

	.cut_items .cut_title {
		font-size: 1.5rem;
		text-align: center;
		padding: 1rem;
	}

	.cut_items ul {
	}

	.cut_items ul li {
		display: flex;
		padding: .5rem 1rem;
		border-bottom: 1px solid #b9befb;
	}

	.cut_items ul li .l {
		flex: 0 0 3.5rem;
	}

	.cut_items ul li .l img {
		width: 3rem;
		height: 3rem;
		border-radius: 3rem;
	}

	.cut_items ul li .m,
	.cut_items ul li .r {
		flex: 1;
		font-size: 1rem;
		align-self: center;
	}

	.cut_items ul li .r {
		text-align: right;
	}
</style>
{{if !$is_share}}
	<div class="cut_content">
		<div class="cut_card">
			<img src="/images/cut_price/cut_card.png">
			<div class="cut_price">￥59.90</div>
		</div>
		<div class="cut_one_dao">
			<div class="btn_one_dao"><span>帮好友砍一刀</span></div>
		</div>
		<div class="cut_get_free">
			<div><span>我也要免费领</span></div>
		</div>
		<div class="cut_items">
			<div class="cut_title">砍价帮</div>
			<ul>
				<li>
					<div class="l"><img src="{{$avatar}}"></div>
					<div class="m"><span>你愁啥</span></div>
					<div class="r"><span>帮你砍了一刀</span></div>
				</li>
				<li>
					<div class="l"><img src="{{$avatar}}"></div>
					<div class="m"><span>你愁啥</span></div>
					<div class="r"><span>帮你砍了一刀</span></div>
				</li>
				<li>
					<div class="l"><img src="{{$avatar}}"></div>
					<div class="m"><span>你愁啥</span></div>
					<div class="r"><span>帮你砍了一刀</span></div>
				</li>
				<li>
					<div class="l"><img src="{{$avatar}}"></div>
					<div class="m"><span>你愁啥</span></div>
					<div class="r"><span>帮你砍了一刀</span></div>
				</li>
				<li>
					<div class="l"><img src="{{$avatar}}"></div>
					<div class="m"><span>你愁啥</span></div>
					<div class="r"><span>帮你砍了一刀</span></div>
				</li>
				<li>
					<div class="l"><img src="{{$avatar}}"></div>
					<div class="m"><span>你愁啥</span></div>
					<div class="r"><span>帮你砍了一刀</span></div>
				</li>
			</ul>
		</div>
	</div>
{{else}}

{{/if}}

<style>
	.cut-alert {
		background: #fff;
		border-radius: 1rem;
		padding: 2rem 1rem;
		position: relative;
	}

	.cut-alert .title {
		font-size: 1rem;
		text-align: center;
		padding-bottom:1rem;
	}

	.cut-alert .imgs {
		display: flex;
		padding: 2rem;
	}

	.cut-alert .imgs div {
		flex: 1;
		text-align: center;
	}
	.cut-alert .imgs div img{
		width: 7rem;
		height: 7rem;
	}
	.cut-alert .avatar img{
		width: 6rem;
		height: 6rem;
		border-radius: 6rem;
	}
</style>
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content" style="background: transparent;width: 25rem;left: 3.5rem;">
			<div class="cut-alert">
				<div class="avatar"><img src="{{$avatar}}"></div>
				<div class="title">长按关注公众号、我们一起免费领</div>
				<div class="imgs">
					<div><img src="/images/cut_price/cut_qr.png"></div>
					<div><img src="/images/cut_price/cut_fingerprint.png"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="/assets/js/require.js"></script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script type="text/html" id="tpl_tool">
	<div class="image">
		<img src="/images/santa/prop_hat.png">
	</div>
	<div class="text">每日主动发起聊天后获得</div>
	<div class="btn">
		<a href="javascript:;" data-tag="tool">发起聊天</a>
	</div>
</script>

<script>
	requirejs(['/js/config.js?v=1.1'], function () {
		requirejs(['/js/cut_price.js?v=1.8.7']);
	});
</script>

