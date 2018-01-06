<link rel="stylesheet" href="/css/zp.min.css">
<section id="index">
	<ul class="sw_bar">
		<li>
			<a href="javascript:;" data-page="swallet">
				<img src="/images/sw/fl.png" alt="">
				<p>1999</p>
				<p>媒瑰花</p>
			</a>
		</li>
		<li>
			<a href="javascript:;" data-page="cash">
				<img src="/images/sw/cash.png" alt="">
				<p>201.8</p>
				<p>现金</p>
			</a>
		</li>
		<li>
			<a href="javascript:;" data-page="card">
				<img src="/images/sw/card.png" alt="">
				<p>2018天</p>
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
			<li>
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
			<p>9.78 <span>元</span></p>
		</div>
	</div>
	<div class="sw_task_total">
		<div>
			<p>累计邀请人数</p>
			<p>3</p>
		</div>
		<div>
			<p>累计可提现红包</p>
			<p>201.8</p>
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
	<div class="sw_cash_items">
		<p>可提现余额: <span>18</span>元</p>
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
		<h5 class="sw_exchange_cash">千寻币: <span>{{$stat.coin}}</span></h5>
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