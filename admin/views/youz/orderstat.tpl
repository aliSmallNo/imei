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
		font-weight: 300;
		font-size: 13px;
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

	.type_1, .type_3 {
		font-size: 12px;
		font-weight: 800;
		color: #777;
		padding: 0;
		background: initial
	}

	.type_3 {
		color: #0f6cf2;
		cursor: pointer;
	}
</style>
<div class="row">
	<h4>下单统计</h4>
</div>
<form action="/youz/orderstat" class="form-inline">
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
	<div class="col-sm-12">
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-bar-chart-o fa-fw"></i> 下单用户按时段统计
				</div>
				<div class="panel-body">
					<div id="amt_times" class="chart-wrapper"></div>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-bar-chart-o fa-fw"></i> 关闭订单用户按时段统计
				</div>
				<div class="panel-body">
					<div id="closed_times" class="chart-wrapper"></div>
				</div>
			</div>
		</div>

	</div>
	<div class="col-sm-12">
		<table class="table table-striped table-bordered">
			<thead>
			<tr>
				<th class="col-sm-4">
					用户
				</th>
				<th>
					下单总数
				</th>
				<th>待付款</th>
				<th>
					买家已签收|订单成功
				</th>
				<th>
					待发货
				</th>
				<th>
					交易关闭
				</th>
				<th>
					实付金额
				</th>

			</tr>
			</thead>
			<tbody>
			{{foreach from=$scanStat item=stat}}
				<tr data-id="{{$stat.fans_id}}" data-type="{{$stat.uType}}" data-name="{{$stat.name}}({{$stat.phone}})">
					<td class="person">
						{{if $stat.fans_id}}
							<div class="avatar">
								<img src="{{$stat.thumb}}">
							</div>
							<div class="title">
								<div>{{$stat.name}}(<span class="user_chain type_{{$stat.uType}}">{{$stat.type_str}}</span>)
									<span class="tip">{{$stat.phone}}</span>
								</div>
								<div><span>收货人: </span>{{$stat.o_receiver_name}}<span class="tip">{{$stat.o_receiver_tel}}</span></div>
							</div>
						{{else}}
							合计
						{{/if}}
					</td>
					<td align="right">
						{{$stat.amt}}
					</td>
					<td align="right">
						{{$stat.wait_pay_amt}}
					</td>
					<td align="right">
						{{$stat.success_amt}}
					</td>
					<td align="right">
						{{$stat.wait_send_goods_amt}}
					</td>
					<td align="right">
						{{$stat.closed_amt}}
					</td>
					<td align="right">
						{{$stat.pay_amt|string_format:'%.2f'}}
					</td>

					<!--
				['amt','wait_pay_amt', 'wait_comfirm_amt', 'wait_send_goods_amt', 'wait_buyer_comfirm_goods_amt', 'success_amt', 'closed_amt'];
				-->
				</tr>
			{{/foreach}}
			<tr>
				<td colspan="7" class="tip">
					1.下单数(包括未支付，支付，关闭等状态的订单)；

				</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
<script src="/js/highcharts/highcharts.js"></script>
<script>
	var mAmtTimes ={{$timesAmt}};
	var mClosedTimes ={{$timesClosed}};
	var mBeginDate = $('.beginDate');
	var mEndDate = $('.endDate');
	$('.j-scope').click(function () {
		var self = $(this);
		var sdate = self.attr('data-from');
		var edate = self.attr('data-to');
		mBeginDate.val(sdate);
		mEndDate.val(edate);
		location.href = "/youz/orderstat?sdate=" + sdate + "&edate=" + edate;
	});

	$(function () {
		initChart('amt_times', mAmtTimes);
		initChart('closed_times', mClosedTimes);
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


	var loadflag = 0;
	$(document).on('click', ".user_chain", function () {
		var self = $(this);
		var fans_id = self.closest("tr").attr('data-id');
		var type = self.closest("tr").attr('data-type');
		var name = self.closest("tr").attr('data-name');
		if (type == 3) {
			if (loadflag) {
				return;
			}
			loadflag = 1;
			$.post("/api/youz", {
				tag: 'last_user_chain',
				fans_id: fans_id
			}, function (resp) {
				loadflag = 0;
				if (resp.code == 0) {
					self.popover({
						placement: 'top',
						title: '用户链',
						content: resp.data.data + '>' + name,
					})
				} else {
					lay.msg(resp.msg);
				}
			});
		}


	});
</script>
{{include file="layouts/footer.tpl"}}