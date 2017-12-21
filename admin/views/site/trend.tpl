{{include file="layouts/header.tpl"}}
<style>
	div.large {
		font-size: 22px;
	}

	.panel-heading small {
		color: #888;
	}
</style>
<div class="row">
	<p class="help-block">{{$today}}汇总数据(延迟10分钟左右)</p>
</div>
<div class="row">
	<div class="col-lg-7"></div>
	<div class="col-lg-2">
		<div class="input-group custom-search-form">
			<input type="text" class="my-date-input form-control queryDate" name="queryDate" value="{{$date}}"
			       placeholder="下单时间">
			<span class="input-group-btn">
							<button class="btn btn-default btnQuery" type="button">
								<span class="glyphicon glyphicon-search" style="color: #999;"></span>
							</button>
						</span>
		</div>
	</div>
	<div class="col-lg-3">
		<div class="btn-group" role="group">
			<button type="button" class="btn btn-default active" tag="0">日</button>
			<button type="button" class="btn btn-default" tag="1">周</button>
			<button type="button" class="btn btn-default" tag="2">月</button>
		</div>
	</div>
</div>
<div class="row-divider"></div>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-bar-chart-o fa-fw"></i> 用户数据
		<small>转化率 = 关注 / 注册</small>
	</div>
	<div class="panel-body">
		<div id="new-chart" class="chart-wrapper"></div>
	</div>
</div>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-bar-chart-o fa-fw"></i> 用户关系
	</div>
	<div class="panel-body">
		<div id="net-chart" class="chart-wrapper"></div>
	</div>
</div>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-bar-chart-o fa-fw"></i> 累计数据
	</div>
	<div class="panel-body">
		<div id="amt-chart" class="chart-wrapper"></div>
	</div>
</div>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-bar-chart-o fa-fw"></i> 活跃数据
		<small>活跃度 = 活跃人数 / 累计会员数</small>
	</div>
	<div class="panel-body">
		<div id="active-chart" class="chart-wrapper"></div>
	</div>
</div>
<script type="text/html" id="cTrendsTmp">
	{{$trends}}
</script>
<script src="/js/highcharts/highcharts.js"></script>
<script>

	var mTrends = $.parseJSON($('#cTrendsTmp').html());
	var mTrend = null;
	var mDate = $('.queryDate');

	function initCharts() {
		initChart('amt-chart', "amt");
		initChart('net-chart', "net");
		initChart('new-chart', "new");
		initChart('active-chart', "active");
	}

	$(document).on('click', '.btnQuery', function () {
		location.href = '/site/trend?dt=' + mDate.val();
	});

	$(document).on('click', 'button[tag]', function () {
		var self = $(this);
		var group = self.closest('div');
		group.find('button').removeClass('active');
		self.addClass('active');
		initCharts();
	});

	function initChart(pid, cat) {
		var btn = $('div.btn-group').find('.active');
		var tag = btn.attr('tag');
		mTrend = mTrends[tag];
		if (!mTrend || !mTrend["dates"]) {
			return;
		}
		var titles = mTrend["dates"];
		var items = [], names = [], fields = [];
		switch (cat) {
			case 'new':
				names = ["用户+授权", "游客", "关注", "取关", "转化率", "会员", "媒婆", "帅哥", "美女"];
				fields = ["added_total", "added_viewer", "added_subscribe", "added_unsubscribe",
					"added_subscribe_ratio", "added_member", "added_meipo", "added_male", "added_female"];
				break;
			case 'net':
				names = ["心动数", "聊天数", "送媒桂花", "充值金额"];
				fields = ["act_favor", "act_chat", "act_gift", 'act_pay'];
				break;
			case 'amt':
				names = ["累计用户+授权", "累计游客", '累计关注', "累计会员", '累计单身男', '累计单身女', '累计媒婆'];
				fields = ["accum_total", "accum_viewer", 'accum_subscribe', "accum_member", 'accum_male', 'accum_female', 'accum_meipo'];
				break;
			case 'active':
				names = ["活跃用户", "活跃度(%)", "活跃男", "活跃女", "活跃媒婆"];
				fields = ["active_total", "active_ratio", "active_male", "active_female", "active_meipo"];
				break;
		}
		if (names) {
			for (var i in fields) {
				items.push({
					name: names[i],
					data: mTrend[fields[i]]
				});
			}
		}

		var colors = ['#e91e63', '#2196F3', '#4CAF50', '#FF8A80', '#47639E', '#00bcd4', "#ff5722", "#9c27b0", "#86c351", '#795548'];
		Highcharts.theme = {
			colors: colors
		};
		Highcharts.setOptions(Highcharts.theme);

		var marker = {
			states: {
				hover: {
					lineColor: ""
				}
			}
		};
		for (var k in items) {
			var item = items[k];
			item["marker"] = {
				states: {
					hover: {
						lineColor: colors[k]
					}
				}
			};
		}
//		console.log(items);

		Highcharts.chart(pid, {
			chart: {
				type: 'spline'
			},
			title: {
				text: null
			},
			xAxis: {
				tickInterval: 2,
				categories: titles,
				gridLineColor: '#e8e8e8',//纵向网格线颜色
				gridLineWidth: 1,//纵向网格线宽度
				gridLineDashStyle: 'ShortDash'
			},
			yAxis: {
				title: {
					text: null
				},
				plotLines: [{
					value: 0,
					width: 1,
					color: '#808080'
				}],
				gridLineDashStyle: 'ShortDash',
			},
			tooltip: {
				shared: true,
				//crosshairs: true
				crosshairs: {
					width: 1,
					color: '#bbb',
					dashStyle: 'Solid'//Solid,shortdot
				}
			},
			plotOptions: {
				series: {
					marker: {
						states: {
							hover: {
								enabled: true,
								lineWidthPlus: 0,
								radiusPlus: 2,
								radius: 4,
								fillColor: '#fff',
								lineColor: '#b8b8b8',
								lineWidth: 1
							},
						},
						radius: 2,  //曲线点半径，默认是4
						//symbol: 'circle'//'url(/img/logo_zp.jpg)' //曲线点类型："circle", "square", "diamond", "triangle","triangle-down"，默认是"circle"
					},
					lineWidth: 2
				}
			},
			legend: {
				layout: 'vertical',
				align: 'right',
				verticalAlign: 'middle',
				borderWidth: 0
			},
			exporting: {
				enabled: false
			},
			series: items
		});
	}

	$(function () {
		setTimeout(function () {
			initCharts();
		}, 550);
	});
</script>

{{include file="layouts/footer.tpl"}}
