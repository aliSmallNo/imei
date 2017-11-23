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
			{{foreach from=$prices key=key item=price}}
			<li class="{{if isset($price.ln) && $price.ln}}{{$price.ln}}{{/if}}">
				<div class="title row-{{$price.cat}}">
					{{$price.title}}{{if isset($price.pre_price) && $price.pre_price}}<b>￥{{$price.pre_price}}</b>{{/if}}
					{{if isset($price.tip) && $price.tip}}
					<div class="tip">{{$price.tip}}</div>{{/if}}
				</div>
				<div class="action"><a href="javascript:;" class="btn-recharge" data-id="{{$price.price}}" data-cat="{{$key}}">{{$price.price}}元</a></div>
			</li>
			{{/foreach}}
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
<script src="/assets/js/require.js" data-main="/js/swallet.js?v=1.2.2"></script>