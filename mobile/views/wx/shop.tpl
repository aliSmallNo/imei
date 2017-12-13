<section id="sec_home">
	<div>
		<ul class="recharge">
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
<section id="sec_list">
	<ul class="charges"></ul>
	<div class="spinner none"></div>
	<div class="no-more none">没有更多了~</div>
</section>
<input type="hidden" id="cUID" value="{{$uid}}">
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
	if (document.location.hash === "" || document.location.hash === "#") {
		document.location.hash = "#home";
	}
	requirejs(['/js/config.js'], function () {
		requirejs(['/js/shop.js?v=1.4.1']);
	});
</script>