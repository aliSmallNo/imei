<section id="swallet">
	<div class="account-header">
		<div class="item">
			<div class="amt">{{$stat['flower']}}</div>
			<span class="ico-rose">媒桂花</span>
		</div>
		{{foreach from=$cards item=card}}
		<div class="item">
			<div class="ico-card-{{$card.cat}}"></div>
			<span>剩余{{$card.left}}天</span>
		</div>
		{{/foreach}}
		<a href="#srecords" class="nav-right">账户记录 ></a>
	</div>
	<div>
		<ul class="recharge">
			<li class="th none">
				<div class="title">充值项目</div>
				<div class="action">价格</div>
			</li>
			<li class="border-none">
				<div class="title m-chat-card-m">
					月度畅聊卡<b>￥39.9</b>
					<div class="tip">包月密聊，有效期免费畅聊</div>
				</div>
				<div class="action"><a href="javascript:;" class="btn-recharge" data-id="9.9" data-cat="chat_month">9.9元</a></div>
			</li>
			<li>
				<div class="title m-chat-card-s">
					季度畅聊卡<b>￥99.9</b>
					<div class="tip">包季密聊，有效期免费畅聊</div>
				</div>
				<div class="action"><a href="javascript:;" class="btn-recharge" data-id="19.9" data-cat="chat_season">19.9元</a></div>
			</li>
			<li class="line"></li>
			{{foreach from=$prices key=k item=item}}
			<li {{if $k==0}}class="border-none" {{/if}}>
				<div class="title m-ico-rose">{{$item.num}} 媒桂花</div>
				<div class="action"><a href="javascript:;" class="btn-recharge" data-id="{{$item.price}}" data-cat="rose">{{$item.price}}元</a>
				</div>
			</li>
			{{/foreach}}
			<li class="line"></li>
			<li>
				<div class="title m-ico-member">单身俱乐部会员<b>￥299</b></div>
				<div class="action"><a href="javascript:;" class="btn-recharge" data-id="99" data-cat="member">99元</a>
				</div>
			</li>
		</ul>
		<p class="tip-block">媒桂花仅用于打赏，不能提现或退款</p>
	</div>
</section>
<section id="srecords">
	<ul class="charges"></ul>
	<div class="spinner" style="display: none"></div>
	<div class="no-more" style="display: none;">没有更多了~</div>
</section>
<input type="hidden" id="cUID" value="{{$hid}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script type="text/template" id="tpl_record">
	{[#items]}
	<li>
		<div class="title">
			<h4>{[title]}</h4>
			<h5>{[dt]}</h5>
		</div>
		<div class="content"><em class="{[unit]} amt{[prefix]}">{[prefix]}{[amt]}</em></div>
	</li>
	{[/items]}
</script>
<script src="/assets/js/require.js" data-main="/js/swallet.js?v=1.2.1"></script>