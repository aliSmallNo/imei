<link rel="stylesheet" href="/css/zp.min.css">
<section id="index">
	<ul class="sw_bar">
		<li>
			<a href="javascript:;" data-page="swallet">
				<img src="/images/sw/fl.png" alt="">
				<p>{{$stat['flower']}}</p>
				<p>媒瑰花</p>
			</a>
		</li>
		<li>
			<a href="javascript:;" data-page="cash">
				<img src="/images/sw/cash.png" alt="">
				<p>{{$stat.coin_y}}</p>
				<p>现金</p>
			</a>
		</li>
		<li>
			<a href="javascript:;" data-page="card">
				<img src="/images/sw/card.png" alt="">
				<p>&nbsp;&nbsp;&nbsp;</p>
				<p>畅聊卡</p>
			</a>
		</li>
	</ul>
	<div class="sw_bar_text">
		<div>充值</div>
		<div>提现</div>
		<div>续费</div>
	</div>
	<div class="sw_pages">
		<ul>
			<li>
				<div class="img"><img src="/images/sw/cash_rule.png"></div>
				<a href="javascript:;" data-page="rule">提现规则</a>
			</li>
			<li style="display: none">
				<div class="img"><img src="/images/sw/cash_task.png"></div>
				<a href="javascript:;" data-page="task">做任务赚赏金</a>
			</li>
			<li>
				<div class="img"><img src="/images/sw/cash_share.png"></div>
				<a href="javascript:;" data-page="share">晒收入</a>
			</li>
		</ul>
	</div>
</section>
<section id="task">
	<div class="sw_task_cash">
		<div class="sw_task_cash_des">
			<h5>今日获得现金</h5>
			<p>0.00 <span>元</span></p>
		</div>
	</div>
	<div class="sw_task_total">
		<div>
			<p>累计邀请人数</p>
			<p>0</p>
		</div>
		<div>
			<p>累计可提现红包</p>
			<p>0.00</p>
		</div>
	</div>
	<div class="sw_task_title">
		新手任务
	</div>
	{{foreach from=$newTask item=item}}
	<div class="sw_task_item">
		<a href="javascript:;" class="sw_task_item_btn active">
			<h5>{{$item.title}}</h5>
			<div>
				<em>+{{$item.num}}</em>
				<img src="/images/sw/red_task.png">
			</div>
		</a>
		<div class="sw_task_item_des">
			<p>{{$item.des}}</p>
			<div class="btn">
				<a href="javascript:;">{{$item.utext}}</a>
			</div>
		</div>
	</div>
	{{/foreach}}
	<div class="sw_task_title">
		日常任务
	</div>
	{{foreach from=$everyTask item=item}}
	<div class="sw_task_item">
		<a href="javascript:;" class="sw_task_item_btn active">
			<h5>{{$item.title}}</h5>
			<div>
				<em>+{{$item.num}}</em>
				<img src="/images/sw/red_task.png">
			</div>
		</a>
		<div class="sw_task_item_des">
			<p>{{$item.des}}</p>
			<div class="btn">
				<a href="javascript:;">{{$item.utext}}</a>
			</div>
		</div>
	</div>
	{{/foreach}}
	<div class="sw_task_title">
		挑战任务
	</div>
	{{foreach from=$hardTask item=item}}
	<div class="sw_task_item">
		<a href="javascript:;" class="sw_task_item_btn active">
			<h5>{{$item.title}}</h5>
			<div>
				<em>+{{$item.num}}</em>
				<img src="/images/sw/red_task.png">
			</div>
		</a>
		<div class="sw_task_item_des">
			<p>{{$item.des}}</p>
			<div class="btn">
				<a href="javascript:;">{{$item.utext}}</a>
			</div>
		</div>
	</div>
	{{/foreach}}

</section>
<section id="cash">
	<div class="sw_cash_items" style="display: none">
		<p>可提现余额: <span>{{$stat.coin_y}}</span>元</p>
		<ul>
			{{foreach from=$cash item=item}}
			<li  class="{{$item.cls}}" data-num="{{$item.amt}}"><a href="javascript:;">{{$item.amt}}元</a></li>
			{{/foreach}}
		</ul>
		<div class="sw_cash_btn_comfirm">
			<a href="javascript:;">立即提现</a>
		</div>
	</div>
	<div>
		<h5 class="sw_exchange_cash">平台兑换红包: <span>{{$stat.coin_y}}</span>元</h5>
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
<section id="card">
	<div class="account-header">
		<div class="item" style="display: none">
			<div class="amt">{{$stat['flower']}}</div>
			<span class="ico-rose">媒桂花</span>
		</div>
		{{foreach from=$cards item=card}}
		<div class="item">
			<div class="ico-card-{{$card.cat}}"></div>
			<span>剩余{{$card.left}}天</span>
		</div>
		{{/foreach}}
	</div>
	<div>
		<h5 class="sw_exchange_cash">平台兑换红包: <span>{{$stat.coin_y}}</span>元</h5>
		<ul class="recharge">
			{{foreach from=$prices key=key item=price}}
			<li class="{{if isset($price.ln) && $price.ln}}{{$price.ln}}{{/if}}">
				<div class="title row-{{$price.cat}}">
					{{$price.title}}
					{{if isset($price.pre_price) && $price.pre_price}}<b>￥{{$price.pre_price}}</b>{{/if}}
					{{if isset($price.tip) && $price.tip}}<div class="tip">{{$price.tip}}</div>{{/if}}
				</div>
				<div class="action">
					<a href="javascript:;" class="btn-recharge" data-id="{{$price.price}}" data-cat="{{$key}}">{{$price.price}}
						元</a>
				</div>
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
<section id="rule">
	<div class="sw_rule">
		<h4>可提现现金</h4>
		<p>可提现现金为用户做任务得来的基础红包金额。你可以进入“我的”-“账户”-“现金”中查看您可提现的现金。并将其提现到微信。</p>
		<h4>千寻币（平台兑换红包）</h4>
		<p>平台兑换红包为活动或着特殊任务中获得。平台简称千寻币只限制用于平台内商品的购买。如：媒桂花、月度畅聊卡与商城内的礼物等待。平台兑换红包每次均已红包形式发放。</p>
		<h4>提现规则</h4>
		<p>平台可提现红包为您做任务积累10元及可立即提现到微信红包。由于微信近期的提现规则调整。您需先添加客服微信：meipo1001。向客服提供您账户余额截图，待客服审核完毕后将在24小时内发红包给您。</p>
		<h4>任务奖励</h4>
		<p>任务分为新手任务和日常任务两种形式。新手任务任务只能完成一次，日常任务可重复完成，但每日有一定的次数限制，每天凌晨05:00次数将会充值。关于任务的详细介绍与对应奖励，可以进入“我的”-“任务”中查看。</p>
	</div>
</section>


<section id="swallet">
	<div class="account-header">
		<div class="item">
			<div class="amt">{{$stat['flower']}}</div>
			<span class="ico-rose">媒桂花</span>
		</div>
		{{foreach from=$cards item=card}}
		<div class="item" style="display: none">
			<div class="ico-card-{{$card.cat}}"></div>
			<span>剩余{{$card.left}}天</span>
		</div>
		{{/foreach}}
		<a href="#srecords" class="nav-right">账户记录 ></a>
	</div>
	<div>
		<h5 class="sw_exchange_cash">平台兑换红包: <span>{{$stat.coin_y}}</span>元</h5>
		<ul class="recharge">
			{{foreach from=$prices key=key item=price}}
			<li class="{{if isset($price.ln) && $price.ln}}{{$price.ln}}{{/if}}">
				<div class="title row-{{$price.cat}}">
					{{$price.title}}
					{{if isset($price.pre_price) && $price.pre_price}}<b>￥{{$price.pre_price}}</b>{{/if}}
					{{if isset($price.tip) && $price.tip}}<div class="tip">{{$price.tip}}</div>{{/if}}
				</div>
				<div class="action">
					<a href="javascript:;" class="btn-recharge" data-id="{{$price.price}}" data-cat="{{$key}}">{{$price.price}}
						元</a>
				</div>
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
	if (document.location.hash === "" || document.location.hash === "#") {
		document.location.hash = "#index";
	}
	requirejs(['/js/config.js'], function () {
		requirejs(['/js/sw.js?v=1.4.3']);
	});
</script>