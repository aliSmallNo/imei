
<link rel="stylesheet" href="/css/zp.min.css?v=1.3.7">

	<div class="sw_task_cash">
		<div class="sw_task_cash_des">
			<h5>今日获得现金</h5>
			<p>{{$data.today_amount}} <span>元</span></p>
		</div>
	</div>
	<div class="sw_task_total">
		<a  href="/wx/share28">
			<p>累计邀请人数</p>
			<p>{{$data.s28_reg}}</p>
		</a>
		<a href="/wx/swallet">
			<p>累计红包</p>
			<p>{{$data.total_amount}}</p>
		</a>
	</div>
	<div class="sw_task_title {{$newTaskShowFlag}}">
		新手任务
	</div>
	{{foreach from=$newTask item=item}}
	<div class="sw_task_item {{$newTaskShowFlag}}" >
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

	<div class="sw_task_title" style="">
		最新活动
	</div>
	{{foreach from=$currTask item=item}}
	<div class="sw_task_item"  style="">
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

