<link rel="stylesheet" href="/css/dev.min.css">
<div class="santa-header">
	<ul class="props clearfix">
		<li data-text="每日签到可获得" data-btn-text="去签到" data-url="/wx/sign">
			<div class="prop prop-sugar"></div>
			<h4>10个</h4>
			<a href="javascript:;">去收集</a>
		</li>
		<li data-text="每日主动发起聊天后获得" data-btn-text="去聊天" data-url="/wx/single#slook">
			<div class="prop prop-hat"></div>
			<h4>10个</h4>
			<a href="javascript:;">去收集</a>
		</li>
		<li data-text="分享好友后可获得" data-btn-text="去分享" data-url="/wx/share">
			<div class="prop prop-sock"></div>
			<h4>10个</h4>
			<a href="javascript:;">去收集</a>
		</li>
		<li data-text="每日主动发起聊天后获得" data-btn-text="去聊天" data-url="/wx/sign">
			<div class="prop prop-olaf"></div>
			<h4>10个</h4>
			<a href="javascript:;">去收集</a>
		</li>
		<li data-text="每日主动发起聊天后获得" data-btn-text="去聊天" data-url="/wx/sign">
			<div class="prop prop-tree"></div>
			<h4>10个</h4>
			<a href="javascript:;">去收集</a>
		</li>
	</ul>
</div>
<div style="height: 2rem"></div>
<div class="santa-body">
	<div class="bg-title">

	</div>
	<div class="content">
		<ul class="bags">
			<li>
				<div class="bag-wrap">
					<div class="bag bag-yuand"></div>
					<ol class="desc">
						<li>集满12个圣诞糖果</li>
						<li>集满12个圣诞帽子</li>
						<li>集满3个圣诞袜子</li>
						<li>集满3个圣诞雪人</li>
						<li>集满1个圣诞树</li>
					</ol>
				</div>
				<a href="javascript:;" class="btn-yuand"></a>
			</li>
			<li>
				<div class="bag-wrap">
					<div class="bag bag-shengd"></div>
					<ol class="desc">
						<li>集满6个圣诞糖果</li>
						<li>集满6个圣诞帽子</li>
						<li>集满1个圣诞袜子</li>
						<li>集满1个圣诞雪人</li>
					</ol>
				</div>
				<a href="javascript:;" class="btn-shengd"></a>
			</li>
			<li>
				<div class="bag-wrap">
					<div class="bag bag-lianl"></div>
					<ol class="desc">
						<li>集满3个圣诞糖果</li>
						<li>集满3个圣诞帽子</li>
						<li>集满1个圣诞袜子</li>
						<li>集满1个圣诞雪人</li>
					</ol>
				</div>
				<a href="javascript:;" class="btn-lianl"></a>
			</li>
		</ul>
	</div>
	<div class="bg-tail">

	</div>
</div>

<div class="santa-footer">
	<div class="content">
		<h4>活动规则</h4>
		<ol>
			<li>活动时间：2017年12月23日-2018年1月6日</li>
			<li>礼包兑换时间：2018年1月2日-2018年1月6日</li>
			<li>活动期间任务所得的圣诞道具均可互相赠送，赠送后将不统计到总体礼物之内。</li>
			<li>关于奖励发放：所有礼包将在元旦节可打开。打开后将实时发放到背包内。如有延迟可添加客服微信号：<b>meipo100</b>咨询</li>
		</ol>
	</div>
</div>
<div class="santa-footer-padding"></div>
<div class="m-popup-shade"></div>
<style>
	.santa-alert {
		background: url(/images/santa/bg_popup_prop.png) no-repeat center center;
		height: 35rem;
		background-size: 100% 35rem;
		position: relative;
	}

	.santa-alert .btn-close {
		position: absolute;
		height: 3rem;
		width: 3rem;
		background: url(/images/santa/btn_close.png) no-repeat center center;
		background-size: 100% 100%;
		right: 0;
		top: 0;
		opacity: .7
	}

	.santa-alert .image {
		height: 8rem;
		padding-top: 10rem;
	}

	.santa-alert .image img {
		height: 8rem;
		width: 8rem;
	}

	.santa-alert .text {
		color: #fff;
		font-size: 1.1rem;
		text-align: center;
	}
	.santa-alert .btn {
		height: 15rem;
	}

	.santa-alert .btn a{
		background: url(../images/santa/btn01.png) no-repeat center center;
		background-size: 60% 4.5rem;
		display: block;
		height: 15rem;
		color: #e74542;
		line-height: 15rem;
		font-size: 1.8rem;
	}


</style>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content" style="background: transparent;width: 25rem;left: 3.5rem;">
			<div class="santa-alert">
				<div class="image">
					<img src="/images/santa/prop_hat.png">
				</div>
				<div class="text">每日主动发起聊天后获得</div>
				<div class="btn">
					<a href="javascript:;">发起聊天</a>
				</div>
				<a href="javascript:;" class="btn-close"></a>
			</div>
		</div>
	</div>
</div>
<script src="/assets/js/require.js"></script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script>
	requirejs(['/js/config.js?v=1.1'], function () {
		requirejs(['/js/santa.js?v=1.8.3']);
	});
</script>

