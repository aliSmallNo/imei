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
						{{$price.title}}{{if isset($price.pre_price) && $price.pre_price}}<b>
							￥{{$price.pre_price}}</b>{{/if}}
						{{if isset($price.tip) && $price.tip}}
							<div class="tip">{{$price.tip}}</div>{{/if}}
					</div>
					<div class="action"><a href="javascript:;" class="btn-recharge" data-id="{{$price.price}}"
					                       data-cat="{{$key}}">{{$price.price}}元</a></div>
				</li>
			{{/foreach}}
			<li>
				<div class="title row-share">
					分享收获媒桂花
					<div class="tip">分享拉新人，注册成功收获媒桂花</div>
				</div>
				<div class="action"><a href="/wx/expand" class="btn-share" data-id="0" data-cat="share">分享</a></div>
			</li>
		</ul>
		<p class="tip-block">媒桂花用于赠予、密聊、约会，不能提现或退款</p>
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
			<h4>{[title]}
				<small>{[note]}</small>
			</h4>
			<h5>{[dt]}</h5>
		</div>
		<div class="content"><em class="{[unit]} amt{[prefix]}">{[prefix]}{[amt]}</em></div>
	</li>
	{[/items]}
</script>
<script src="/assets/js/require.js"></script>
<script>
	requirejs(['/js/config.js'], function () {
		requirejs(['/js/swallet.js?v=1.4.0']);
	});
</script>