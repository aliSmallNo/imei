<section id="swallet">
	<div class="account-header">
		<div class="amt">{{$stat['flower']}}</div>
		<div>
			<span class="m-ico-rose">媒桂花</span>
		</div>
		<a href="#srecords">消费记录 ></a>
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
				<div class="action"><a href="javascript:;" class="btn-recharge" data-id="{{$item.price}}">{{$item.price}}元</a>
				</div>
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
	<li>
		<div class="title">
			<em>新人奖励</em>
			2017-06-01 12:33:06
		</div>
		<div class="m-ico-rose">20</div>
	</li>
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>
<script data-main="/js/swallet.js?v=1.1.3" src="/assets/js/require.js"></script>