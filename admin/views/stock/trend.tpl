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
		<i class="fa fa-bar-chart-o fa-fw"></i> 总借款
	</div>
	<div class="panel-body">
		<div id="load-chart" class="chart-wrapper"></div>
	</div>
</div>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-bar-chart-o fa-fw"></i> 借款用户数
	</div>
	<div class="panel-body">
		<div id="users-chart" class="chart-wrapper"></div>
	</div>
</div>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-bar-chart-o fa-fw"></i> 新增用户数
	</div>
	<div class="panel-body">
		<div id="new-chart" class="chart-wrapper"></div>
	</div>
</div>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-bar-chart-o fa-fw"></i> 跟进统计
	</div>
	<div class="panel-body">
		<div id="follow-chart" class="chart-wrapper"></div>
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
	var mDate = $('.queryDate');

	function initCharts() {
		initChart('load-chart', "load");
		initChart('users-chart', "users");
		initChart('follow-chart', "follow");
		initChart('new-chart', "new");
	}

	$(document).on('click', '.btnQuery', function () {
		location.href = '/stock/trend?dt=' + mDate.val();
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
			case 'load':
				names = ["小刀", "于辉", "金志新", "徐方", "王毅龙", '冯林', '陈明', '宋富城', '冯小强', '查俊'];// "谢硕硕", "生佳豪", "周攀", "罗文燕", "曹嘉义",
				fields = [
					"sum_loan_ZuoDanLei_18513655687", "sum_loan_YuHui_13910838033", "sum_loan_JinZhiXin_18600649492",
					"sum_loan_XuFang_13910838055", "sum_loan_WangYiLong_18622112893", "sum_loan_FengLin_18131243333", "sum_loan_ChenMing_18931357765"
					, "sum_loan_SongFuCheng_18611794484", 'sum_loan_FengXiaoQiang_13643225272', "sum_loan_ChaJun_13381081778",
					//"sum_loan_XieShuoShuo_18101390540", "sum_loan_ShengJiaHao_17777857755", "sum_loan_ZhouPan_17611629667",
					//"sum_loan_LuoWenYan_18801235947", "sum_loan_CaoJiaYi_13520364895",
				];
				break;
			case "users":
				names = ["小刀", "于辉", "金志新", "徐方", "王毅龙", '冯林', '陈明', '宋富城', '冯小强', '查俊'
				];// "谢硕硕", "生佳豪", "周攀", "罗文燕", "曹嘉义",
				fields = [
					"sum_loan_users_ZuoDanLei_18513655687", "sum_loan_users_YuHui_13910838033", "sum_loan_users_JinZhiXin_18600649492",
					"sum_loan_users_XuFang_13910838055", "sum_loan_users_WangYiLong_18622112893", "sum_loan_users_FengLin_18131243333",
					"sum_loan_users_ChenMing_18931357765", "sum_loan_users_SongFuCheng_18611794484", 'sum_loan_users_FengXiaoQiang_13643225272',
					"sum_loan_users_ChaJun_13381081778",
					//  "sum_loan_users_XieShuoShuo_18101390540", "sum_loan_users_ShengJiaHao_17777857755","sum_loan_users_ZhouPan_17611629667",
					// "sum_loan_users_LuoWenYan_18801235947", "sum_loan_users_CaoJiaYi_13520364895",
				];
				break;
			case "follow":
				names = ["小刀", "金志新", "邱聚兴", "于辉", "张梦莹", "徐方", '陈明', '宋富城'];
				fields = [
					"follow_xiaodao", "follow_jinzhixin",
					"follow_qiujuxing", "follow_yuhui", "follow_zhangmengying",
					"follow_xufang", 'chenming', 'songfucheng'
				];
				break;
			case "new":
				names = ["新增"];
				fields = ["new_user"];
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
