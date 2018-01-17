
<link rel="stylesheet" href="/css/zp.min.css?v=1.3.6">

<div class="vip_top">
	{{if $vipFlag}}
	<div class="vip_top_1">
		<div class="vip_info">
			<div class="img"><img src="{{$avatar}}"></div>
			<div class="other">
				<h5>{{$name}}尼古拉斯 赵四</h5>
				<p>会员到期日：2018-06-06</p>
			</div>
		</div>
		<div class="vip_price">
			<div class="t">限时特价VIP: 99元/年</div>
			<div class="btn"><a href="javascript:;">续费</a></div>
		</div>
	</div>
	{{else}}
	<div class="vip_top_2">
		<div class="vip_price">
			<div class="t">限时特价VIP: 99元/年</div>
			<div class="btn"><a href="javascript:;">开通</a></div>
		</div>
	</div>
	{{/if}}
</div>

<div class="vip_content">
	<div class="vip_content_title">开通尊贵VIP 更快找到另一半</div>
	<div class="vip_progress_bar">
		<p>交友成功率</p>
		<div class="bar">
			<div class="bar_vip">尊贵VIP 提升300%</div>
			<div class="bar_normal">普通会员</div>
		</div>
	</div>
	<div class="vip_progress_bar">
		<p>收信次数</p>
		<div class="bar">
			<div class="bar_vip">尊贵VIP 提升300%</div>
			<div class="bar_normal">普通会员</div>
		</div>
	</div>

	<div class="vip_content_title">会员特权</div>
	<ul class="vip_privilege">
		<li class="vip_li_1">八倍显示特权</li>
		<li class="vip_li_2">查看高级资料</li>
		<li class="vip_li_3">会员专属图标</li>
		<li class="vip_li_4">VIP专属礼物</li>
		<li class="vip_li_5">5名异性微信号</li>
		<li class="vip_li_6">尊享高级筛选</li>
		<li class="vip_li_7">免费心动留言</li>
		<li class="vip_li_8">新功能优先体验</li>
	</ul>
	<div class="vip_mouth_gift">
		<a href="javascript:;">领取每月礼物</a>
	</div>
</div>

<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>
<input type="hidden" id="cWXUrl" value="{{$wxUrl}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js"></script>
<script>
	requirejs(['/js/config.js?v=1.2.5'], function () {
		requirejs(['/js/vip.js?v=1.2.4']);
	});
</script>

