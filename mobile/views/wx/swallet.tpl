<section id="swallet">
	<div class="account-header">
		<div class="amt">{{$stat['flower']}}</div>
		<div>
			<span class="m-ico-rose">媒桂花</span>
		</div>
		<a href="#srecords">账户记录 ></a>
	</div>
	<div>
		<ul class="recharge">
			<li class="th">
				<div class="title">充值项目</div>
				<div class="action">价格</div>
			</li>
			{{foreach from=$prices key=k item=item}}
			<li>
				<div class="title m-ico-rose">{{$item.num}} 媒桂花</div>
				<div class="action"><a href="javascript:;" class="btn-recharge" data-id="{{$item.price}}" data-cat="rose">{{$item.price}}元</a>
				</div>
			</li>
			{{/foreach}}
			<li style="margin-top:1.5rem">
				<div class="title m-ico-member">单身俱乐部单身会员
					<a href="javascript:;">￥299</a>
				</div>
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
			<h5>{[date]}</h5>
		</div>
		<div class="content"><em class="{[unit]}">{[amt]}</em></div>
	</li>
	{[/items]}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/swallet.js?v=1.1.5" src="/assets/js/require.js"></script>