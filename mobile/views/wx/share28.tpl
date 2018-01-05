<link rel="stylesheet" href="/css/zp.min.css">
<section id="share">
	<div class="s28_content">
		<div class="s28_content_items">
			<ul class="s28_share_stat">
				<li><h5>{{$ret.share}}</h5>
					<p>分享次数</p></li>
				<li><h5>{{$ret.reg}}</h5>
					<p>邀请成功</p></li>
				<li><h5>{{$ret.money}}</h5>
					<p>预计奖励</p></li>
			</ul>
			<div class="s28_line">
				<div class="s28_line_head">
					<p>本梯次奖励：<span>{{$ret.curr_money}}</span>元</p>
				</div>
				<div class="s28_line_repeat">
					<div class="s28_line_level">
						<div class="s28_line_level_title">正在第{{$ret.curr_level}}梯队：{{$ret.curr_money}}元</div>
						<p class="s28_line_perc_bar"><em style="width: {{$ret.curr_percent}}%"></em></p>
						<div class="s28_line_level_ico">
							<img src="/images/s28/s28_lever_count.png">
							<p>{{$ret.curr_p}}/{{$ret.curr_total}}</p>
						</div>
					</div>
					<ul class="s28_line_list">
						{{foreach from=$list item=item}}
						<li class="{{$item.cls}}">
							<div class="{{$item.dir}}">
								<h5>{{$item.num}}元</h5>
								<p>邀请{{$item.p}}人</p>
								<span>{{$item.k}}</span>
							</div>
						</li>
						{{/foreach}}
					</ul>
				</div>
				<div class="s28_line_footer"></div>
			</div>

			<div class="s28_bottom">
				<div class="s28_share_btn">
					<a href="javascript:;" class="btn-share">立即分享得现金</a>
				</div>
				<p class="s28_bottom_tip">每天连续来分享，福利奖励会更高</p>
			</div>
		</div>
	</div>
</section>
<section id="shared">
	<div class="s28_shared_content">
		<div class="s28_shared_des">
			<a href="javascript:;" class="s28_shared_red">

			</a>
			<div class="s28_shared_qr">
				<div class="img"><img src="{{$url}}"></div>
				<div class="img"><img src="/images/s28/s28_finger_mark.png"></div>
			</div>
			<div class="s28_shared_tip">长按识别二维码</div>
		</div>
	</div>
</section>

<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content" style="background: transparent;width: 25rem;left: 3.5rem;">
			<div class="s28-alert">

			</div>
		</div>
	</div>
</div>
<input type="hidden" value="{{$sid}}" id="SUID">
<input type="hidden" value="{{$uid}}" id="UID">
<script src="/assets/js/require.js"></script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>

<script type="text/html" id="tpl_bag">
	<ul>
		{[#data]}
		<li>
			<div class="bag_img"><img src="{[img]}" alt=""></div>
			<p>X<span>{[num]}</span></p>
			<p class="name">{[name]}</p>
		</li>
		{[/data]}
	</ul>
	<div class="btn">
		<a href="javascript:;" data-tag="bag">确定</a>
	</div>
</script>
<script>
	if (document.location.hash === "" || document.location.hash === "#") {
		document.location.hash = "#share";
	}
	requirejs(['/js/config.js?v=1.1'], function () {
		requirejs(['/js/share28.js?v=1.8.7']);
	});
</script>

