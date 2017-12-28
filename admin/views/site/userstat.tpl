{{include file="layouts/header.tpl"}}
<style>
	.chart-wrapper {
		height: 250px;
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

	.j-scope {
		font-size: 13px;
		text-decoration: none;
		margin: 0 2px;
	}
</style>
<div class="row">
	<div class="col-lg-2">
		<h4>用户分析</h4>
	</div>
	<div class="col-lg-10 form-inline">
		<input class="my-date-input form-control beginDate" name="beginDate" value="{{$beginDate}}" placeholder="开始时间">
		至
		<input class="my-date-input form-control endDate" name="endDate" value="{{$endDate}}" placeholder="结束时间">
		<select class="form-control gender" style="display: none">
			<option value="">-=全部=-</option>
			<option value="10">只看女生</option>
			<option value="11">只看男生</option>
		</select>
		<a href="javascript:;" class="btn btn-primary btnQuery">查询</a>
		<a href="/site/pins" class="btn btn-primary" target="_blank">用户地图分布</a>
		<span class="space"></span>
		<a href="javascript:;" class="j-scope" data-from="{{$today}}" data-to="{{$today}}">今天</a>
		<a href="javascript:;" class="j-scope" data-from="{{$yesterday}}" data-to="{{$yesterday}}">昨天</a>
		<a href="javascript:;" class="j-scope" data-from="{{$monday}}" data-to="{{$sunday}}">本周</a>
		<a href="javascript:;" class="j-scope" data-from="{{$firstDay}}" data-to="{{$endDay}}">本月</a>
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
				<i class="fa fa-bar-chart-o fa-fw"></i> 每个时段活跃统计
			</div>
			<div class="panel-body">
				<div id="chart_times" class="chart-wrapper"></div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="panel panel-default">
		<div class="panel-heading">
			<i class="fa fa-bar-chart-o fa-fw"></i> 在线时长统计（秒）
		</div>
		<div class="panel-body">
			<div id="chart_session" class="chart-wrapper"></div>
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
				<div id="chart_edu" class="chart-wrapper"></div>
				<div id="chart_height" class="chart-wrapper"></div>
				<div id="chart_marry" class="chart-wrapper"></div>
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
				<div id="chart_edu_m" class="chart-wrapper"></div>
				<div id="chart_height_m" class="chart-wrapper"></div>
				<div id="chart_marry_m" class="chart-wrapper"></div>
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
				<div id="chart_edu_f" class="chart-wrapper"></div>
				<div id="chart_height_f" class="chart-wrapper"></div>
				<div id="chart_marry_f" class="chart-wrapper"></div>
			</div>
		</div>
	</div>
</div>
<script src="/js/highcharts/highcharts.js"></script>
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
				initPie(resp.data.mar.all, "chart_marry", '全员婚姻');
				initPie(resp.data.mar.male, "chart_marry_m", '男生婚姻');
				initPie(resp.data.mar.female, "chart_marry_f", '女生婚姻');
				initPie(resp.data.height.all, "chart_height", '全员身高');
				initPie(resp.data.height.male, "chart_height_m", '男生身高');
				initPie(resp.data.height.female, "chart_height_f", '女生身高');
				initPie(resp.data.income.all, "chart_income", '全员收入');
				initPie(resp.data.income.male, "chart_income_m", '男生收入');
				initPie(resp.data.income.female, "chart_income_f", '女生收入');
				initPie(resp.data.age.all, "chart_age", '全员年龄');
				initPie(resp.data.age.male, "chart_age_m", '男生年龄');
				initPie(resp.data.age.female, "chart_age_f", '女生年龄');
				initPie(resp.data.edu.all, "chart_edu", '全员学历');
				initPie(resp.data.edu.male, "chart_edu_m", '男生学历');
				initPie(resp.data.edu.female, "chart_edu_f", '女生学历');
				initPie(resp.data.gender, "chart_gender", '');
				initChart(resp.data.times, "chart_times", ['男生', '女生']);
				initChart(resp.data.session, "chart_session", ['男生', '女生']);
				console.log(resp.data);
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
				name: '用户数',
				size: '32%',
				colorByPoint: true,
				data: cData
			}]
		});
	}

	function initChart(data, pid, groups) {
		var seriesData = [];
		for (var k = 0; k < groups.length; k++) {
			var groupName = groups[k];
			var groupData = [];
			for (var m = 0; m < data.length; m++) {
				groupData.push([
					data[m]['date'],
					data[m][groupName]
				]);
			}
			seriesData.push({
				name: groupName,
				data: groupData
			});
		}
		$('#' + pid).highcharts({
			chart: {
				type: 'spline',
				marginTop: 25
			},
			title: {
				text: null
			},
			colors: ['#1976D2', '#f06292', '#51c332'],
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
			series: seriesData
		});
	}

	mBtnQuery.on('click', function () {
		reloadData();
	});

	$('.j-scope').click(function () {
		var self = $(this);
		mBeginDate.val(self.attr('data-from'));
		mEndDate.val(self.attr('data-to'));
		reloadData();
		self.blur();
	});

	$(function () {
		setTimeout(function () {
			reloadData();
		}, 400);
	});
</script>

{{include file="layouts/footer.tpl"}}
