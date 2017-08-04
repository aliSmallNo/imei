{{include file="layouts/header.tpl"}}
<style>
	div.large {
		font-size: 22px;
	}

	.panel-heading small {
		color: #888;
	}
</style>
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-9">
			<p class="help-block">{{$today}}汇总数据(延迟10分钟左右)</p>
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
			<i class="fa fa-bar-chart-o fa-fw"></i> 累计注册
		</div>
		<div class="panel-body">
			<div id="amt-chart" class="chart-wrapper"></div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<i class="fa fa-bar-chart-o fa-fw"></i> 活跃用户
			<small>活跃度 = 活跃人数 / 累计会员数</small>
		</div>
		<div class="panel-body">
			<div id="active-chart" class="chart-wrapper"></div>
		</div>
	</div>
</div>
<script type="text/html" id="cTrendsTmp">
	{{$trends}}
</script>

<script src="/js/highcharts/highcharts.js"></script>
<script>

	var mTrends = $.parseJSON($('#cTrendsTmp').html());
	console.log(mTrends);
	var mTrend = null;

	function initCharts() {
		initChart('amt-chart', "amt");
		initChart('net-chart', "net");
		initChart('new-chart', "new");
		initChart('active-chart', "active");
	}

	$(document).on('click', 'button[tag]', function () {
		var self = $(this);
		var group = self.closest('div');
		group.find('button').removeClass('active');
		self.addClass('active');

		initCharts();
	});

	$(document).ready(function () {
		initCharts();
	});

	function initChart(pid, cat) {
		var btn = $('div.btn-group').find('.active');
		var tag = btn.attr('tag');
		mTrend = mTrends[tag];
		if (!mTrend || mTrend["dates"] == undefined || !mTrend["dates"]) {
			return;
		}

		var titles = mTrend["dates"];
		var items = [];
		if (cat == "new") {
			var names = ["到访", "路人", "会员", "关注", "取消关注", "转化率", "充值", "媒婆", "帅哥", "美女"];
			var fields = ["reg", "newvisitor", "newmember", "focus", "todayblur", "focusRate", "trans", "mps", "male", "female"];
			for (var i = 0; i < fields.length; i++) {
				items.push({
					name: names[i],
					data: mTrend[fields[i]]
				})
			}
		}
		if (cat == "net") {
			var names = ["心动", "牵线", "牵线成功"];
			var fields = ["favor", "getwxno", "pass"];
			for (var i = 0; i < fields.length; i++) {
				items.push({
					name: names[i],
					data: mTrend[fields[i]]
				})
			}
		}

		if (cat == "amt") {
			var names = ["累计到访", "累计会员", "累计路人", '累计关注', '累计媒婆', '累计单身男', '累计单身女'];
			var fields = ["amt", "member", "visitor", 'follows', 'meipos', 'boys', 'girls'];
			for (var i = 0; i < fields.length; i++) {
				items.push({
					name: names[i],
					data: mTrend[fields[i]]
				})
			}
		}

		if (cat == "active") {
			var names = ["活跃用户", "活跃度(%)", "活跃男", "活跃女", "活跃媒婆"];
			var fields = ["active", "activeRate", "activemale", "activefemale", "activemp"];
			for (var i = 0; i < fields.length; i++) {
				items.push({
					name: names[i],
					data: mTrend[fields[i]]
				})
			}
		}

		var colors = ['#F30', '#212121', '#208850', '#337ab7', '#b87b00', '#ab47cb', '#777', "#86c351", "#FF8800", "#996699"];
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
		console.log(items);

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


</script>

{{include file="layouts/footer.tpl"}}
