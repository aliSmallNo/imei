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

<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-2">
			<h4>用户分析</h4>
		</div>
		<div class="col-lg-10">
			<div class="row form-inline">
				<input type="text" class="my-date-input form-control beginDate" name="beginDate" value="{{$beginDate}}"
							 placeholder="开始时间">
				<label class="control-label  ">至</label>
				<input type="text" class="my-date-input form-control endDate" name="endDate" value="{{$endDate}}"
							 placeholder="结束时间">
				<button class="btn btn-primary btnQuery">查询</button>
			</div>
		</div>
	</div>
	<div class="row-divider"></div>
	<div class="row">
		<div class="col-lg-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-bar-chart-o fa-fw"></i> 年龄统计

				</div>
				<div class="panel-body">
					<div id="age-chart" class="chart-wrapper"></div>
				</div>
			</div>

			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-bar-chart-o fa-fw"></i> 性别统计

				</div>
				<div class="panel-body">
					<div id="gender-chart" class="chart-wrapper"></div>
				</div>
			</div>
		</div>

		<div class="col-lg-6">

			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-bar-chart-o fa-fw"></i> 身高统计

				</div>
				<div class="panel-body">
					<div id="height-chart" class="chart-wrapper"></div>
				</div>
			</div>

			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-bar-chart-o fa-fw"></i> 收入统计
				</div>
				<div class="panel-body">
					<div id="income-chart" class="chart-wrapper"></div>
				</div>
			</div>

		</div>

	</div>
	<div class="row-divider2"></div>
</div>
<script src="/js/highcharts/highcharts.js"></script>
<script src="/js/highcharts/funnel.js"></script>
<script>
	var mBtnQuery = $('.btnQuery');
	var mBeginDate = $('.beginDate');
	var mEndDate = $('.endDate');

	var mAgeAmt = $('#age-chart');
	var mHeightAmt = $('#height-chart');
	var mIncomeAmt = $('#income-chart');
	var mGenderAmt = $('#gender-chart');

	function reloadData() {
		mAgeAmt.html("");
		mHeightAmt.html("");
		mGenderAmt.html("");
		mIncomeAmt.html("");
		layer.load();
		$.post("/api/userchart", {
			tag: "stat",
			beginDate: mBeginDate.val(),
			endDate: mEndDate.val(),
		}, function (resp) {
			layer.closeAll();
			if (resp.code == 0) {
				console.log(resp.data);
				initPie(resp.data.height, "height-chart");
				initPie(resp.data.income, "income-chart");
				initPie(resp.data.age, "age-chart");
				initPie(resp.data.gender, "gender-chart");

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

	function initPie(cData, pid) {
		setTheme();
		if (cData && pid != "age-chart") {
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

	$(document).ready(function () {
		reloadData();
	});

	mBtnQuery.on('click', function () {
		reloadData();
	});
</script>

{{include file="layouts/footer.tpl"}}
