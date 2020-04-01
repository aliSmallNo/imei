{{include file="layouts/header.tpl"}}
<style>
  .high {
    height: 400px;
  }
</style>
<div class="row">
  <div class="col-sm-6">
    <h4>市净率图
    </h4>
  </div>
</div>

<div class="row">
    <div class="panel panel-default ">
        <div class="panel-heading">
            <i class="fa fa-bar-chart-o fa-fw"></i> 破1市净率

        </div>
        <div class="panel-body">
            <div id="container_chart" class="height"></div>
        </div>
    </div>
</div>

<script src="/js/highstock/highstock.js"></script>
<script src="/js/highstock/exporting.js"></script>
<script src="/js/highstock/highcharts-zh_CN.js"></script>
<script>
    var pb_rates = {{$pb_rates}},
        pb_cos = {{$pb_cos}},
        data = {{$data}};
    // console.log('pb_rates', pb_rates);
    // console.log('pb_cos', pb_cos);
    // console.log('data', data);
    init_chart();
    // 设置不使用 UTC
    Highcharts.setOptions({
        global: {
            useUTC: false
        }
    });

    function init_chart() {
        Highcharts.stockChart('container_chart', {
            rangeSelector: {
                selected: 4
            },
            xAxis: {
                tickInterval: 1,
                gridLineColor: '#e8e8e8',//纵向网格线颜色
                gridLineWidth: 1,//纵向网格线宽度
                gridLineDashStyle: 'ShortDash',
            },
            yAxis: [
                {
                    title: {
                        text: '占比'
                    },
                    plotLines: [
                        {
                            value: 0,
                            width: 1,
                            color: '#808080'
                        }],
                    gridLineDashStyle: 'ShortDash',
                },
                {
                    title: {
                        text: '数量'
                    },
                    plotLines: [
                        {
                            value: 0,
                            width: 1,
                            color: '#808080',
                        }],
                    gridLineDashStyle: 'ShortDash',
                    opposite: false
                }
            ],
            plotOptions: {
                series: {
                    //compare: 'percent'
                }
            },
            tooltip: {
                shared: true,
                pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> <br/>',
                //valueDecimals: 2
            },
            series: [
                {
                    name: '破净股比例',
                    data: pb_rates,
                    tooltip: {  // 为当前数据列指定特定的 tooltip 选项
                        //valuePrefix: '$',
                        valueSuffix: '%'
                    },
                },
                {
                    name: '破净股数量',
                    data: pb_cos,
                    yAxis: 1
                }
            ]
        });
    }
</script>

{{include file="layouts/footer.tpl"}}