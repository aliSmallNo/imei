
<link rel="stylesheet" href="/css/zp.min.css?v=1.3.6">

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
				<a href="{{$item.url}}" data-key="{{$item.key}}"  class="{{$item.cls}}">{{$item.utext}}</a>
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
				<a href="{{$item.url}}"  data-key="{{$item.key}}"  class="{{$item.cls}}">{{$item.utext}}</a>
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
				<a href="{{$item.url}}"  data-key="{{$item.key}}" class="{{$item.cls}}">{{$item.utext}}</a>
			</div>
		</div>
	</div>
	{{/foreach}}


<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
</div>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/require.js"></script>
<script>
	requirejs(['/js/config.js?v=1.2.1'], function () {
		requirejs(['/js/task.js?v=1.2.2']);
	});
</script>

