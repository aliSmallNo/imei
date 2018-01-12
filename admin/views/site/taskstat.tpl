{{include file="layouts/header.tpl"}}
<style>
	.note {
		font-size: 14px;
		font-weight: 300;
	}

	.note b {
		padding-left: 2px;
		padding-right: 2px;
		font-size: 15px;
		font-weight: 500;
	}

	td img {
		width: 64px;
		height: 64px;
	}

	.tip {
		font-weight: 400;
		font-size: 11px;
	}

	.person {
		display: -ms-flexbox;
		display: flex;
		border: none;
	}

	.person .avatar {
		-ms-flex: 0 0 40px;
		flex: 0 0 40px;
		text-align: left;
	}

	.person .avatar img {
		width: 90%;
		height: auto;
		border-radius: 3px;
	}

	.person .title {
		-webkit-box-flex: 1
		-webkit-flex: 1
		-ms-flex: 1
		flex: 1
		padding-left: 10px;
	}
	.co1{
		color: #00bd1b;
	}
	.co2{
		color: #f80;
	}
</style>
<div class="row">
	<h4>任务统计</h4>
</div>
<form action="/site/taskstat" class="form-inline">
	<input class="form-control beginDate my-date-input" placeholder="开始时间" name="sdate"
				 value="{{if isset($getInfo['sdate'])}}{{$getInfo['sdate']}}{{/if}}">
	至
	<input class="form-control endDate my-date-input" placeholder="截止时间" name="edate"
				 value="{{if isset($getInfo['edate'])}}{{$getInfo['edate']}}{{/if}}">
	<button class="btn btn-primary">查询</button>
	<span class="space"></span>
	<a href="javascript:;" class="j-scope" data-from="{{$today}}" data-to="{{$today}}">今天</a>
	<a href="javascript:;" class="j-scope" data-from="{{$yesterday}}" data-to="{{$yesterday}}">昨天</a>
	<a href="javascript:;" class="j-scope" data-from="{{$monday}}" data-to="{{$sunday}}">本周</a>
	<a href="javascript:;" class="j-scope" data-from="{{$firstDay}}" data-to="{{$endDay}}">本月</a>
</form>
<div class="row-divider"></div>
<div class="row">
	<div class="col-sm-5" style="display: none">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-bar-chart-o fa-fw"></i> 关注用户按时段统计
			</div>
			<div class="panel-body">
				<div id="sub_times" class="chart-wrapper"></div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-bar-chart-o fa-fw"></i> 注册用户按时段统计
			</div>
			<div class="panel-body">
				<div id="reg_times" class="chart-wrapper"></div>
			</div>
		</div>
	</div>
	<div class="col-sm-12">
		<table class="table table-striped table-bordered">
			<thead>
			<tr>
				<th class="col-sm-2">用户</th>
				<th>首次注册：</th>
				<th>资料达80%：</th>
				<th>实名认证：</th>

				<th>发起聊天3次：</th>
				<th>回复一次聊天：</th>
				<th>秀红包金额：</th>
				<th>收到礼物：</th>
				<th>签到：</th>
				<th>成功邀请：</th>
				<th>28888活动：</th>
			</tr>
			</thead>
			<tbody>
			{{foreach from=$scanStat item=stat}}
			<tr>
				<td class="person">
					<div class="avatar"><img src="{{$stat.thumb}}"></div>
					<div class="tip">
						<div>{{$stat.name}} ({{$stat.id}})</div>
						<div class="tip">{{$stat.phone}}</div>
						<div class="tip">累计: <span class="co1">￥{{$stat.amt/100|string_format:'%.2f'}}</span></div>
						<div class="tip">消费: <span class="co2">￥{{$stat.reduce/100}}</span></div>
					</div>

				</td>
				<td class="col-sm-1 tip">数量￥{{$stat.reg_amt/100}} <br> 次数: {{$stat.reg_count}}</td>
				<td class="col-sm-1 tip">数量￥{{$stat.percent80_amt/100}} <br>次数:  {{$stat.percent80_count}}</td>
				<td class="col-sm-1 tip">数量￥{{$stat.cert_amt/100}} <br>次数:  {{$stat.cert_count}}</td>
				<td class="col-sm-1 tip">数量￥{{$stat.chat_3times_amt/100}} <br>次数:  {{$stat.chat_3times_count}}</td>
				<td class="col-sm-1 tip">数量￥{{$stat.chat_reply_amt/100}} <br>次数:  {{$stat.chat_reply_count}}</td>
				<td class="col-sm-1 tip">数量￥{{$stat.show_coin_amt/100}} <br>次数:  {{$stat.show_coin_count}}</td>
				<td class="col-sm-1 tip">数量￥{{$stat.receive_gift_amt/100}} <br>次数:  {{$stat.receive_gift_count}}</td>
				<td class="col-sm-1 tip">数量￥{{$stat.sign_amt/100}} <br>次数:  {{$stat.sign_count}}</td>
				<td class="col-sm-1 tip">数量￥{{$stat.share_reg_amt/100}} <br>次数:  {{$stat.share_reg_count}}</td>
				<td class="col-sm-1 tip">数量￥{{$stat.share28_amt/100}} <br>次数: {{$stat.share28_count}} <br>分享数:{{$stat.s28_share}}<br>注册数:{{$stat.s28_reg}}</td>

			{{/foreach}}
			</tbody>
		</table>
		{{$pagination}}
	</div>
</div>
<script src="/js/highcharts/highcharts.js"></script>
<script>
	//var mSubTimes =timesSub;
	//var mRegTimes =timesReg;

	var mBeginDate = $('.beginDate');
	var mEndDate = $('.endDate');
	$('.j-scope').click(function () {
		var self = $(this);
		var sdate = self.attr('data-from');
		var edate = self.attr('data-to');
		mBeginDate.val(sdate);
		mEndDate.val(edate);
		location.href = "/site/taskstat?sdate=" + sdate + "&edate=" + edate;
	});

	$(function () {
		//initChart('sub_times', mSubTimes);
		//initChart('reg_times', mRegTimes);
	});

	function initChart(pid, chatData) {

		$('#' + pid).highcharts({
			chart: {
				type: 'spline',
				marginTop: 25,
			},
			title: {
				text: null
			},
			tooltip: {
				shared: true,
				crosshairs: {
					width: 1,
					color: '#b8b8b8',
					dashStyle: 'Solid'
				}
			},
			xAxis: {
				type: 'category',
				tickInterval: 1,
				tickWidth: 0,
				labels: {
					rotation: -45,
					style: {
						fontSize: '10px'
					}
				},
				gridLineColor: '#e8e8e8',
				gridLineWidth: 1,
				gridLineDashStyle: 'ShortDash'
			},
			yAxis: {
				min: 0,
				title: {
					text: null
				},
				gridLineDashStyle: 'ShortDash'
			},
			plotOptions: {
				series: {
					marker: {
						states: {
							hover: {
								enabled: true,
								lineWidthPlus: 1,
								radiusPlus: 4,
								//radius: 4,
								fillColor: '#fff',
								lineColor: '#b8b8b8',
								lineWidth: 1
							}
						},
						radius: 1,
						symbol: 'circle'
					},
					lineWidth: 2
				}
			},
			legend: {
				enabled: true,
				align: 'center'
				//verticalAlign:'middle'
			},
			series: chatData
		});
	}
</script>
{{include file="layouts/footer.tpl"}}