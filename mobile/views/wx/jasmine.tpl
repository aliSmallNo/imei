<link rel="stylesheet" href="/css/zp.min.css?v=1.3.6">

<div class="ev_container">
	<div class="ev_container_top">
		<div class="ev_container_top_grab">
			<h5>每天1000万现金免费领</h5>
			<p>每日登陆可领红包，可直接提现</p>
			<h4>今日还剩<span>58760000</span>元</h4>
			<a href="javascript:;" data-tag="grab" class="grab"><img src="/images/ev/ev_btn_grab.png" alt=""></a>
		</div>
		<div class="ev_container_top_grabed">
			<div class="avatar"><img src="{{$avatar}}" alt=""></div>
			<h5>{{$name}}</h5>
			<p><span>0.00</span>元</p>
			<a href="javascript:;" data-tag="withdraw" class="grabed"><img src="/images/ev/ev_btn_withdraw.png" alt=""></a>
		</div>
		<a href="javascript:;" data-tag="ipacket" class="ev_top_alert ipacket">我的红包</a>
		<a href="javascript:;" data-tag="rule" class="ev_top_alert rule">红包规则</a>
	</div>

	<div class="ev_container_content">
		<div class="ev_container_share">
			<div><a href="javascript:;" data-tag="share" ev_opt="share">分享给好友</a></div>
			<div><a href="javascript:;" data-tag="more" ev_opt="more">获取更多现金</a></div>
		</div>
		<ul>


		</ul>
		<div class="ev_container_footer">
			<a href="javascript:;" data-tag="reg">点击注册</a>
			<span>可以了解更多哦~</span>
		</div>
	</div>

</div>

<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none;">
	<div class="m-popup-wrap">
		<div class="m-popup-content animate-pop-in" style="left: 3rem;width: 27rem;background: initial">

		</div>
	</div>
</div>


<input type="hidden" id="cWXUrl" value="{{$wxUrl}}">

<script type="text/template" id="tpl_init">
	{[#data]}
	<li>
		<div class="avatar"><img src="{[thumb]}" alt=""></div>
		<div class="des">
			<h5>{[name]}</h5>
			<p>{[age]}|{[height_t]}|{[horos_t]}|{[income_t]}</p>
		</div>
		<a href="javascript:;" data-tag="chat" class="chat"><span>和TA聊天</span></a>
	</li>
	{[/data]}
</script>
<script type="text/template" id="tpl_qr">
	<div class="ev_tmp_alert">
		<div class="ev_tmp_qr">
			<div class="qr"><img src="/images/ev/ev_ico_qr.jpg" alt=""></div>
			<p>{[text]}</p>
		</div>
	</div>
</script>
<script type="text/template" id="tpl_grab">
	<div class="ev_tmp_alert">
		<div class="ev_tmp_grab">
			<div class="avatar"><img src="{{$avatar}}" alt=""></div>
			<p>{{$name}}</p>
			<h3>成功获得{[amt]}元</h3>
			<div class="btn">
				<a href="javascript:;">立即提现</a>
			</div>
		</div>
	</div>
</script>
<script type="text/template" id="tpl_more">
	<div class="ev_tmp_alert">
		<div class="ev_tmp_grab">
			<div class="avatar"><img src="{{$avatar}}" alt=""></div>
			<p>{{$name}}</p>
			<h3>分享好友即可再领取一次红包</h3>
			<div class="btn">
				<a href="javascript:;">立即分享</a>
			</div>
		</div>
	</div>
</script>
<script type="text/template" id="tpl_not_enough">
	<div class="ev_tmp_alert">
		<div class="ev_tmp_not_enough">
			<h3>提示</h3>
			<p>提现金额必须大于1元</p>
			<p>分享到朋友圈，可获再次领取红包机会</p>
			<div class="btn">
				<a href="javascript:;">我知道了</a>
			</div>
		</div>
	</div>
</script>
<script type="text/template" id="tpl_rule">
	<div class="ev_tmp_alert">
		<div class="ev_tmp_rule">
			<div class="rule">
				<h4>本活动截止于2018年2月22日</h4>
				<p>须知：根据微信企业公众号平台规定，微信红包没次发放只能发放1-200，因此手气不佳者抢到0.1-0.99的提现提示不成功，每人每天只可提现2次，请珍惜提现次数。</p>
				<p>1.分享到微信群和好友参与分钱；</p>
				<p>2.由于微信限制微信红包提现额度最小为1元；</p>
				<p>3.每人每日都可登陆进行抢红包：</p>
				<p>4.活动期间，在法律允许范围内，千寻恋恋有权对本活动规则进行变动或调整，相关变动或调整会公布在活动首页；</p>
				<p>5.如有发现通过网络攻击或者系统刷取等不正当方式欺诈参与活动者进行谋取利益行为（包括但不限制于作弊，机刷，恶意套取红包等），影响正常用户公平参与的情况下，千寻恋恋取消该用户的获得成功及活动资格；如遭遇自然，系统故障等不可抗因素导致活动无法正常进行时，千寻恋恋有权终止活动；</p>
				<p>6.千寻恋恋不会以任何形式和名义索取您的银行卡号，密码等信息，如有其他疑问情联系客服：meipo1001；</p>
				<p>7.在法律允许的范围内，千寻恋恋拥有本次活动最终解释权；</p>
			</div>
			<div class="btn">
				<a href="javascript:;">立即抢红包提现赚钱</a>
			</div>
		</div>
	</div>
</script>
<input type="hidden" id="LASTUID" value="">
<input type="hidden" id="UID" value="{{$uid}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js"></script>
<script>

	requirejs(['/js/config.js?v=1.2.5'], function () {
		requirejs(['/js/everyredpacket.js?v=1.2.7']);
	});
</script>


