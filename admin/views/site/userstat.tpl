{{include file="layouts/header.tpl"}}
<style>
	.chart-wrapper {
		height: 250px;
	}

	.col-sm-4, .col-sm-8 {
		padding-left: 5px;
		padding-right: 5px;
	}

	.panel-body {
		padding: 10px;
	}

	.form-inline .form-control {
		width: 9em;
	}

	#clues {
		outline: none;
		border: 1px solid #999;
	}
</style>

<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-2">
			<h4>用户分析</h4>
		</div>
		<div class="col-lg-10">
			<div class="row form-inline">
				<input class="my-date-input form-control beginDate" name="beginDate" value="{{$beginDate}}" placeholder="开始时间">
				<label class="control-label">至</label>
				<input class="my-date-input form-control endDate" name="endDate" value="{{$endDate}}" placeholder="结束时间">
				<select class="form-control gender" style="display: none">
					<option value="">-=全部=-</option>
					<option value="10">只看女生</option>
					<option value="11">只看男生</option>
				</select>
				<button class="btn btn-primary btnQuery">查询</button>
			</div>
		</div>
	</div>
	<div class="row-divider"></div>
	<div class="row">
		<div class="col-sm-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-bar-chart-o fa-fw"></i> 性别统计
				</div>
				<div class="panel-body">
					<div id="chart_gender" class="chart-wrapper"></div>
				</div>
			</div>
		</div>
		<div class="col-sm-8">
			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-bar-chart-o fa-fw"></i> 每个时段操作人数统计
				</div>
				<div class="panel-body">
					<div id="chart_times" class="chart-wrapper"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-bar-chart-o fa-fw"></i> 全员统计
				</div>
				<div class="panel-body">
					<div id="chart_age" class="chart-wrapper"></div>
					<div id="chart_income" class="chart-wrapper"></div>
					<div id="chart_height" class="chart-wrapper"></div>
				</div>
			</div>

		</div>
		<div class="col-sm-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-bar-chart-o fa-fw"></i> 男生统计
				</div>
				<div class="panel-body">
					<div id="chart_age_m" class="chart-wrapper"></div>
					<div id="chart_income_m" class="chart-wrapper"></div>
					<div id="chart_height_m" class="chart-wrapper"></div>
				</div>
			</div>
		</div>
		<div class="col-sm-4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-bar-chart-o fa-fw"></i> 女生统计
				</div>
				<div class="panel-body">
					<div id="chart_age_f" class="chart-wrapper"></div>
					<div id="chart_income_f" class="chart-wrapper"></div>
					<div id="chart_height_f" class="chart-wrapper"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="/js/highcharts/highcharts.js"></script>
<script src="/js/highcharts/funnel.js"></script>
<script>
	var mBtnQuery = $('.btnQuery');
	var mBeginDate = $('.beginDate');
	var mEndDate = $('.endDate');
	var mGender = $('.gender');

	function reloadData() {
		$('.chart-wrapper').html('');
		layer.load();
		$.post("/api/userchart", {
			tag: "stat",
			beginDate: mBeginDate.val(),
			endDate: mEndDate.val()
		}, function (resp) {
			layer.closeAll();
			if (resp.code == 0) {
				initPie(resp.data.height.all, "chart_height", '全员身高');
				initPie(resp.data.height.male, "chart_height_m", '男生身高');
				initPie(resp.data.height.female, "chart_height_f", '女生身高');
				initPie(resp.data.income.all, "chart_income", '全员收入');
				initPie(resp.data.income.male, "chart_income_m", '男生收入');
				initPie(resp.data.income.female, "chart_income_f", '女生收入');
				initPie(resp.data.age.all, "chart_age", '全员年龄');
				initPie(resp.data.age.male, "chart_age_m", '男生年龄');
				initPie(resp.data.age.female, "chart_age_f", '女生年龄');
				initPie(resp.data.gender, "chart_gender", '');
				initChart(resp.data.times, "chart_times", '');
			} else {
				layer.msg(resp.msg);
			}
		}, "json");
	}

	function setTheme() {
		Highcharts.theme = {
			colors: {{$colors}}
		};
		Highcharts.setOptions(Highcharts.theme);
	}

	function initPie(cData, pid, title) {
		setTheme();

		Highcharts.chart(pid, {
			chart: {
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false,
				type: 'pie'
			},
			title: {
				text: title
			},
			/*tooltip: {
			 pointFormat: '{series.name}: {point.percentage:.1f}%'
             },*/
			plotOptions: {
				pie: {
					shadow: false,
					center: ['50%', '50%'],
					allowPointSelect: true,
					cursor: 'pointer',
					dataLabels: {
						enabled: true,
						format: '{point.name}: {point.percentage:.1f} %',
						style: {
							fontWeight: 300,
							color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
						}
					}
				}
			},
			series: [{
				name: '客户线索',
				size: '32%',
				colorByPoint: true,
				data: cData
			}]
		});
	}

	function initChart(data, pid, title) {
		var dates = [], mainData = [], subData = [];
		for (var i = 0; i < data.length; i++) {
			dates[i] = data[i]['date'];
			mainData.push([
				data[i]['date'],
				data[i]['男生']
			]);
			subData.push([
				data[i]['date'],
				data[i]['女生']
			]);
		}

		$('#' + pid).highcharts({
			chart: {
				type: 'spline',
				marginTop: 25
			},
			title: {
				text: null
			},
			colors: ['#f06292', '#1976D2'],
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
			series: [
				{
					name: '女生',
					data: subData
				},
				{
					name: '男生',
					data: mainData,
					marker: {
						states: {
							hover: {
								lineColor: '#44b549'
							}
						}
					}
				}
			]
		});
	}

	$(document).ready(function () {
		reloadData();
	});

	mBtnQuery.on('click', function () {
		reloadData();
	});
</script>

{{include file="layouts/footer.tpl"}}
