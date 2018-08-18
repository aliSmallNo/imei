<style>
	.cut_content {
		background: url('/images/cut_price/cut_bg.jpg?v=1.1.2') no-repeat center center;
		background-size: 100% 100%;
		width: 100%;
		padding-bottom: 3rem;
		padding-top: 2rem;
	}

	.cut_cart_title, .cut_card, .cut_price, .cut_one_dao, .cut_get_free {
		text-align: center;
	}

	.cut_cart_title img {
		width: 85%;
	}

	.cut_card {
		position: relative;
	}

	.cut_card img {
		width: 250px;
	}

	.cut_card .cut_price {
		color: #e7fb00;
		position: absolute;
		bottom: 1.8rem;
		left: 0;
		right: 0;
		text-align: center;
		font-size: 1rem;
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

	.cut-alert {
		background: #fff;
		border-radius: 1rem;
		padding: 2rem 1rem;
		position: relative;
	}

	.cut-alert .title {
		font-size: 1rem;
		text-align: center;
		padding-bottom: 1rem;
	}

	.cut-alert .imgs {
		display: flex;
		padding: 2rem;
	}

	.cut-alert .imgs div {
		flex: 1;
		text-align: center;
	}

	.cut-alert .imgs div img {
		width: 7rem;
		height: 7rem;
	}

	.cut-alert .avatar img {
		width: 6rem;
		height: 6rem;
		border-radius: 6rem;
	}
</style>

<div class="cut_content">
	<div class="cut_cart_title">
		<img src="/images/cut_price/cut_cart_title.png">
	</div>
	<div class="cut_card">
		<img src="/images/cut_price/cut_card.png?v=1.1.1">
		<div class="cut_price">价值￥19.90、邀请朋友点赞六次可获得</div>
	</div>
	{{if $is_share}}
	<div class="cut_one_dao">
		<div class="btn_one_dao"><span>帮好友点赞</span></div>
	</div>
	{{/if}}
	<div class="cut_get_free">
		<div class="cut_get_free"><span>我也要免费领</span></div>
	</div>
	<div class="cut_items">
		<div class="cut_title">点赞帮</div>
		<ul>

		</ul>
	</div>
	<div style="text-align: center;"><a href='https://wx.meipo100.com/wx/single'
					data-miniprogram-appid='wx866898cd348536a8'
					data-miniprogram-path='pages/index/index' style="font-size: 1rem">到家严选</a></div>
</div>


<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">

</div>

<input type="hidden" id="LAST_OPENID" value="{{$last_user_info.openid}}">
<input type="hidden" id="OPENID" value="{{$openid}}">
<input type="hidden" id="IS_SHARE" value="{{$is_share}}">
<script src="/assets/js/require.js"></script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>

<script type="text/html" id="tpl_qr">
	<div class="m-popup-wrap">
		<div class="m-popup-content" style="background: transparent;width: 25rem;left: 3.5rem;">
			<div class="cut-alert">
				{{if $last_user_info}}
					<div class="avatar"><img src="{{$last_user_info.uAvatar}}"></div>
				{{/if}}
				<div class="title">长按关注公众号、我们一起免费领</div>
				<div class="imgs">
					<div><img src="{{if $qr}}{{$qr}}{{else}}/images/cut_price/cut_qr.png{{/if}}"></div>
					<div><img src="/images/cut_price/cut_fingerprint.png"></div>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="tpl_item">
	{[#data]}
	<li>
		<div class="l"><img src="{[uThumb]}"></div>
		<div class="m"><span>{[uName]}</span></div>
		<div class="r"><span>帮你点赞了</span></div>
	</li>
	{[/data]}
</script>

<script>
	requirejs(['/js/config.js?v=1.1'], function () {
		requirejs(['/js/cut_price.js?v=1.1.4']);
	});
	console.log({{$last_user_info_json}})
</script>

