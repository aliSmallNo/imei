{{include file="layouts/header.tpl"}}
<style>
	.chart-wrapper {
		height: 360px;
	}

	.form-inline .form-control {
		width: 9em;
	}

	#clues {
		outline: none;
		border: 1px solid #999;
	}
</style>

<div class="row">
	<div class="col-lg-2">
		<h4>CRM跟进统计</h4>
	</div>
	<div class="col-lg-10">
		<div class="row form-inline">
			<input type="text" class="my-date-input form-control beginDate" name="beginDate" value="{{$beginDate}}"
						 placeholder="开始时间">
			<label class="control-label  ">至</label>
			<input type="text" class="my-date-input form-control endDate" name="endDate" value="{{$endDate}}"
						 placeholder="结束时间">
			<select class="form-control bdassign" name="bdassign">
				<option value="">请选择BD</option>
				{{foreach from=$staff item=bd}}
					<option value="{{$bd.id}}">{{$bd.name}}</option>
				{{/foreach}}
			</select>
			<button class="btn btn-primary btnQuery">查询</button>
		</div>
	</div>
</div>
<div class="row-divider"></div>
<div class="row">
	<div class="col-lg-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-bar-chart-o fa-fw"></i> 跟进记录统计

			</div>
			<div class="panel-body">
				<div id="track-chart" class="chart-wrapper"></div>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-bar-chart-o fa-fw"></i> 新线索开发

			</div>
			<div class="panel-body">
				<div id="new-chart" class="chart-wrapper"></div>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-bar-chart-o fa-fw"></i> 跟进状态饼图
				<select name="clue" id="clues">
					<option value="0">-全部状态-</option>
					{{foreach from=$options key=k item=option}}
						<option value="{{$k}}">{{$option}}</option>
					{{/foreach}}
				</select>
			</div>
			<div class="panel-body">
				<div id="src-chart" class="chart-wrapper"></div>
			</div>
		</div>
	</div>
	<div class="col-lg-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-bar-chart-o fa-fw"></i> 漏斗图

			</div>
			<div class="panel-body">
				<div id="funnel-chart" class="chart-wrapper"></div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-bar-chart-o fa-fw"></i> CRM分布

			</div>
			<div class="panel-body">
				<div id="client-chart" class="chart-wrapper"></div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-bar-chart-o fa-fw"></i> 跟进状态甜甜圈

			</div>
			<div class="panel-body">
				<div id="donut-chart" class="chart-wrapper"></div>
			</div>
		</div>
	</div>
</div>
<div class="row-divider2"></div>
<script src="/js/highcharts/highcharts.js"></script>
<script src="/js/highcharts/funnel.js"></script>
<script>
	var mBtnQuery = $('.btnQuery');
	var mBeginDate = $('.beginDate');
	var mBD = $('.bdassign');
	var mEndDate = $('.endDate');
	var mChartAmt = $('#track-chart');
	var mChartCnt = $('#funnel-chart');
	var mChartNew = $('#new-chart');
	var mChartClient = $('#client-chart');
	var mChartSrc = $('#src-chart');
	var clue = $('#clues');
	var clueVal;

	function reloadData() {
		mChartAmt.html("");
		mChartCnt.html("");
		mChartNew.html("");
		mChartClient.html("");
		layer.load();
		$.post("/api/stock_chart", {
			tag: "stat",
			beginDate: mBeginDate.val(),
			endDate: mEndDate.val(),
			id: mBD.val()
		}, function (resp) {
			layer.closeAll();
			if (resp.code == 0) {
				if (mBD.val()) {
					//新线索,跟进客户数--每个BD
					initChartDetail(resp.data.track, resp.data.track_titles, "track-chart");
					initChartDetail(resp.data.new, resp.data.new_titles, "new-chart");
				} else {
					//新线索,跟进客户数--整体
					initChart(resp.data.track, "track-chart", "跟进客户数");
					initChart(resp.data.new, "new-chart", "新线索");
				}
				// CRM分布
				initClient(resp.data.series, resp.data.titles, "client-chart");
				// 漏斗图
				initFunnel(resp.data.funnel, "funnel-chart");
				// 新线索饼图
				initPie(resp.data.sources, "src-chart");

				//甜甜圈
				initDonut(resp.data.inners, resp.data.outers, "donut-chart");
			} else {
				layer.msg(resp.msg);
			}
		}, "json");
	}

	function reloadClueData() {
		mChartSrc.html('');
		layer.load();
		$.post("/api/stock_clue", {
			tag: "stat",
			beginDate: mBeginDate.val(),
			endDate: mEndDate.val(),
			id: mBD.val(),
			status: clueVal,
		}, function (resp) {
			layer.closeAll();
			if (resp.code == 0) {
				//新线索饼图
				initPie(resp.data.sources, "src-chart");
			} else {
				// layer.msg(resp.msg);
				mChartSrc.html(resp.msg)
			}
		}, "json");
	}

	function setTheme() {
		Highcharts.theme = {
			colors: {{$colors}}
		};
		Highcharts.setOptions(Highcharts.theme);
	}

	function initFunnel(cData, pid) {
//	setTheme();
		var colors = [];
		for (var k in cData) {
			var item = cData[k];
			colors.push(item[2]);
		}
		Highcharts.theme = {
			colors: colors
		};
		Highcharts.setOptions(Highcharts.theme);
		Highcharts.chart(pid, {
			chart: {
				type: 'funnel',
				marginRight: 100
			},
			title: {
				text: null
			},
			plotOptions: {
				series: {
					dataLabels: {
						enabled: true,
						format: '{point.name} ({point.y:,.0f})',
						color: '#414141',
						softConnector: true
					},
					neckWidth: '10%',
					neckHeight: '30%'
				}
			},
			legend: {
				enabled: false
			},
			series: [{
				name: '客户线索',
				data: cData
			}]
		});
	}

	function initClient(cData, titles, pid) {
		setTheme();
		Highcharts.chart(pid, {
			chart: {
				type: 'bar'
			},
			title: {
				text: null
			},
			xAxis: {
				categories: titles
			},
			yAxis: {
				min: 0,
				title: {
					text: null
				}
			},
			legend: {
				reversed: true
			},
			tooltip: {
				shared: true,
				crosshairs: false
			},
			plotOptions: {
				series: {
					stacking: 'normal'
				}
			},
			series: cData
		});
	}

	function initChartDetail(cData, titles, pid) {
		Highcharts.theme = {
			colors: ["#6891d4"]
		};
		Highcharts.setOptions(Highcharts.theme);
		var interval = parseInt(titles.length / 8.0);
		Highcharts.chart(pid, {
			title: {
				text: null
			},
			xAxis: {
				tickInterval: interval,
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
			legend: {
				enabled: false
			},
			plotOptions: {
				series: {
					marker: {
						states: {
							hover: {
								enabled: true,
								lineWidthPlus: 0,
								radiusPlus: 2,
								//radius: 4,
								fillColor: '#fff',
								lineColor: '#6891d4',
								lineWidth: 1,
							},
						},
						radius: 2,  //曲线点半径，默认是4
						//symbol: 'circle'//'url(/img/logo_zp.jpg)' //曲线点类型："circle", "square", "diamond", "triangle","triangle-down"，默认是"circle"
					},
					lineWidth: 2
				}
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
			series: [{
				name: '客户数量',
				data: cData
			}]
		});
	}

	function initChart(cData, pid, title) {
		Highcharts.theme = {
			colors: ["#6891d4"]
		};
		Highcharts.setOptions(Highcharts.theme);
		Highcharts.chart(pid, {
			chart: {
				type: 'bar'
			},
			title: {
				text: null
			},
			xAxis: {
				type: 'category',
				title: {
					text: null
				},
				gridLineColor: '#e8e8e8',//纵向网格线颜色
				gridLineWidth: 1,//纵向网格线宽度
				gridLineDashStyle: 'ShortDash'
			},
			yAxis: {
				min: 0,
				title: {
					text: null,
					align: 'high'
				},
				labels: {
					overflow: 'justify'
				},
				gridLineDashStyle: 'ShortDash',
			},
			tooltip: {
				valueSuffix: null
			},
			plotOptions: {
				bar: {
					dataLabels: {
						enabled: true
					}
				}
			},
			legend: {
				enabled: false
			},
			credits: {
				enabled: false
			},
			series: [{
				name: title,
				data: cData
			}]

		});
	}

	function initPie(cData, pid) {
		setTheme();
		if (cData) {
			cData[0]["sliced"] = true;
			cData[0]["selected"] = true;
		}
		Highcharts.chart(pid, {
			chart: {
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false,
				type: 'pie'
			},
			title: {
				text: null
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

	function initDonut(inners, outers, pid) {
		Highcharts.chart(pid, {
			chart: {
				type: 'pie'
			},
			title: {
				text: null
			},
			plotOptions: {
				pie: {
					shadow: false,
					center: ['50%', '50%']
				}
			},
			tooltip: {
				valueSuffix: '%'
			},
			series: [{
				name: '跟进状态',
				data: inners,
				size: '50%',
				dataLabels: {
					formatter: function () {
						return this.y > 5 ? this.point.name : null;
					},
					color: '#ffffff',
					distance: -40
				}
			}, {
				name: '客户来源',
				data: outers,
				size: '65%',
				innerSize: '50%',
				dataLabels: {
					formatter: function () {
						// display only if larger than 1
						return this.y > 1 ? '<b>' + this.point.name + ':</b> ' + this.y + '%' : null;
					}
				}
			}]
		});
	}

	$(document).on('click', 'button[tag]', function () {
		var self = $(this);
		var group = self.closest('div');
		group.find('button').removeClass('active');
		self.addClass('active');
		reloadData();
	});

	$(document).ready(function () {
		reloadData();
	});

	clue.on('change', function () {
		clueVal = $(this).val();
		reloadClueData();
	});

	mBtnQuery.on('click', function () {
		reloadData();
	});
</script>

{{include file="layouts/footer.tpl"}}
