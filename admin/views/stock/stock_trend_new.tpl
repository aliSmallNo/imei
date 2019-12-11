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
    <i class="fa fa-bar-chart-o fa-fw"></i> 新用户借款金额(指首次买股在30天以内的客户的借款金额)
  </div>
  <div class="panel-body">
    <div id="new_loan-chart" class="chart-wrapper"></div>
  </div>
</div>
<div class="panel panel-default">
  <div class="panel-heading">
    <i class="fa fa-bar-chart-o fa-fw"></i> 新用户借款金额(新用户当月借款总数)
  </div>
  <div class="panel-body">
    <div id="new_curr_month_loan-chart" class="chart-wrapper"></div>
  </div>
</div>
<div class="panel panel-default">
  <div class="panel-heading">
    <i class="fa fa-bar-chart-o fa-fw"></i> 流失用户（之前交易，这月不交易了）
  </div>
  <div class="panel-body">
    <div id="lose_curr_last_month_user-chart" class="chart-wrapper"></div>
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
<script type="text/html" id="cPartner">
  {{$partners}}
</script>

<script src="/js/highcharts/highcharts.js"></script>
<script>

    var mTrends = $.parseJSON($('#cTrendsTmp').html());
    var cPartner = $.parseJSON($('#cPartner').html());
    console.log('mTrends: ', mTrends);
    console.log('cPartner:', cPartner);
    var mTrend = null;
    var mDate = $('.queryDate');

    function initCharts() {
        initChart('load-chart', 'load');
        initChart('users-chart', 'users');
        initChart('follow-chart', 'follow');
        initChart('new-chart', 'new');
        initChart('new_loan-chart', 'new_loan');
        initChart('new_curr_month_loan-chart', 'new_curr_month_loan');
        initChart('lose_curr_last_month_user-chart', 'lose_curr_last_month_user');
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
        if (!mTrend || !mTrend['dates']) {
            return;
        }
        var titles = mTrend['dates'];
        var items = [], names = [], fields = [];
        var items2 = [], names2 = [], fields2 = [];
        switch (cat) {
            case 'load':
                names = [
                    '合计', '小刀', '金志新', '徐方', '冯林', '宋富城', '冯小强', '查俊', '孙庆海',
                    '于辉', '吴淑霞',
                    '孙庆海2',
                    '达林太',
                    '孙鹏',
                    '梁颢珊',
                    '王丽',
                ];// "谢硕硕", "生佳豪", "周攀", "罗文燕", "曹嘉义","王毅龙","于辉","吴淑霞"
                fields = [
                    'sum_loan_total',
                    'sum_loan_ZuoDanLei_18513655687', 'sum_loan_JinZhiXin_18600649492',
                    'sum_loan_XuFang_13910838055', 'sum_loan_FengLin_18131243333',
                    'sum_loan_SongFuCheng_18611794484', 'sum_loan_FengXiaoQiang_13643225272',
                    'sum_loan_ChaJun_13381081778', 'sum_loan_SunQingHai_13701269919',
                    'sum_loan_YuHui_13910838033', 'sum_loan_WuShuXia_18911781586',
                    'sum_loan_SunQingHai_13501299927',
                    'sum_loan_DaLinTai_13910502331',
                    'sum_loan_SunPeng_13838067113',
                    'sum_loan_LiangShan_13533578570',
                    'sum_loan_WangLi_13601088874',
                ];
                var data = get_items(mTrend, 'sum_loan_', 2);
                fields = data[0];
                names = data[1];
                break;
            case 'users':
                names = [
                    '合计', '小刀', '金志新', '徐方', '冯林', '宋富城', '冯小强', '查俊', '孙庆海',
                    '于辉', '吴淑霞',
                    '孙庆海2',
                    '达林太',
                    '孙鹏',
                    '梁颢珊',
                    '王丽',
                    //'冯卓刚', '高考',
                ];// "谢硕硕", "生佳豪", "周攀", "罗文燕", "曹嘉义","王毅龙","于辉",
                fields = [
                    'sum_loan_users_total',
                    'sum_loan_users_ZuoDanLei_18513655687',
                    'sum_loan_users_JinZhiXin_18600649492',
                    'sum_loan_users_XuFang_13910838055',
                    'sum_loan_users_FengLin_18131243333',
                    'sum_loan_users_SongFuCheng_18611794484',
                    'sum_loan_users_FengXiaoQiang_13643225272',
                    'sum_loan_users_ChaJun_13381081778',
                    'sum_loan_users_SunQingHai_13701269919',
                    'sum_loan_users_YuHui_13910838033',
                    'sum_loan_users_WuShuXia_18911781586',
                    'sum_loan_users_SunQingHai_13501299927',
                    'sum_loan_users_DaLinTai_13910502331',
                    'sum_loan_users_SunPeng_13838067113',
                    'sum_loan_users_LiangShan_13533578570',
                    'sum_loan_users_WangLi_13601088874',
                ];

                var data = get_items(mTrend, 'sum_loan_users_', 3);
                fields = data[0];
                names = data[1];
                break;
            case 'follow':
                names = [
                    '小刀',
                    //'金志新', '邱聚兴', '张梦莹', '徐方', '宋富城',
                    //'李士琪',
                ];
                fields = [
                    'follow_xiaodao',
                    //'follow_jinzhixin', 'follow_qiujuxing', 'follow_zhangmengying', 'follow_xufang',
                    //"follow_lishiqi",
                ];
                break;
            case 'new':
                names = [
                    '合计', '小刀', '金志新', '徐方', '冯林', '宋富城', '冯小强', '查俊', '孙庆海',
                    '于辉', '吴淑霞',
                    '孙庆海2',
                    '达林太',
                    '孙鹏',
                    '梁颢珊',
                    '王丽',
                ];

                fields = [
                    'new_users_total',
                    'new_users_ZuoDanLei_18513655687', 'new_users_JinZhiXin_18600649492',
                    'new_users_XuFang_13910838055', 'new_users_FengLin_18131243333',
                    'new_users_SongFuCheng_18611794484', 'new_users_FengXiaoQiang_13643225272',
                    'new_users_ChaJun_13381081778', 'new_users_SunQingHai_13701269919',
                    'new_users_YuHui_13910838033', 'new_users_WuShuXia_18911781586',
                    'new_users_SunQingHai_13501299927',
                    'new_users_DaLinTai_13910502331',
                    'new_users_SunPeng_13838067113',
                    'new_users_LiangShan_13533578570',
                    'new_users_WangLi_13601088874',
                ];
                var data = get_items(mTrend, 'new_users_', 2);
                fields = data[0];
                names = data[1];
                break;
            case 'new_loan':
                names = [
                    '合计', '小刀', '金志新', '徐方', '冯林', '宋富城', '冯小强', '查俊', '孙庆海',
                    '于辉', '吴淑霞',
                    '孙庆海2',
                    '达林太',
                    '孙鹏',
                    '梁颢珊',
                    '王丽',
                ];
                fields = [
                    'new_loan_total', 'new_loan_ZuoDanLei_18513655687', 'new_loan_JinZhiXin_18600649492',
                    'new_loan_XuFang_13910838055', 'new_loan_FengLin_18131243333',
                    'new_loan_SongFuCheng_18611794484', 'new_loan_FengXiaoQiang_13643225272',
                    'new_loan_ChaJun_13381081778', 'new_loan_SunQingHai_13701269919',
                    'new_loan_YuHui_13910838033', 'new_loan_WuShuXia_18911781586',
                    'new_loan_SunQingHai_13501299927',
                    'new_loan_DaLinTai_13910502331',
                    'new_loan_SunPeng_13838067113',
                    'new_loan_LiangShan_13533578570',
                    'new_loan_WangLi_13601088874',
                ];
                var data = get_items(mTrend, 'new_loan_', 2);
                fields = data[0];
                names = data[1];
                break;
            case 'new_curr_month_loan':
                names = [
                    '合计', '小刀', '金志新', '徐方', '冯林', '宋富城', '冯小强', '查俊', '孙庆海',
                    '于辉', '吴淑霞',
                    '孙庆海2',
                    '达林太',
                    '孙鹏',
                    '梁颢珊',
                    '王丽',
                ];
                fields = [
                    'new_curr_month_loan_total',
                    'new_curr_month_loan_ZuoDanLei_18513655687',
                    'new_curr_month_loan_JinZhiXin_18600649492',
                    'new_curr_month_loan_XuFang_13910838055',
                    'new_curr_month_loan_FengLin_18131243333',
                    'new_curr_month_loan_SongFuCheng_18611794484',
                    'new_curr_month_loan_FengXiaoQiang_13643225272',
                    'new_curr_month_loan_ChaJun_13381081778',
                    'new_curr_month_loan_SunQingHai_13701269919',
                    'new_curr_month_loan_YuHui_13910838033',
                    'new_curr_month_loan_WuShuXia_18911781586',
                    'new_curr_month_loan_SunQingHai_13501299927',
                    'new_curr_month_loan_DaLinTai_13910502331',
                    'new_curr_month_loan_SunPeng_13838067113',
                    'new_curr_month_loan_LiangShan_13533578570',
                    'new_curr_month_loan_WangLi_13601088874',
                ];
                var data = get_items(mTrend, 'new_curr_month_loan_', 4);
                fields = data[0];
                names = data[1];
                break;
            case "lose_curr_last_month_user":
                names = [
                    '合计', '小刀', '金志新', '徐方', '冯林', '宋富城', '冯小强', '查俊', '孙庆海',
                    '于辉', '吴淑霞',
                    '孙庆海2',
                    '达林太',
                    '孙鹏',
                    '梁颢珊',
                    '王丽',
                ];
                fields = [
                    'lose_curr_last_month_user_total',
                    'lose_curr_last_month_user_ZuoDanLei_18513655687',
                    'lose_curr_last_month_user_JinZhiXin_18600649492',
                    'lose_curr_last_month_user_XuFang_13910838055',
                    'lose_curr_last_month_user_FengLin_18131243333',
                    'lose_curr_last_month_user_SongFuCheng_18611794484',
                    'lose_curr_last_month_user_FengXiaoQiang_13643225272',
                    'lose_curr_last_month_user_ChaJun_13381081778',
                    'lose_curr_last_month_user_SunQingHai_13701269919',
                    'lose_curr_last_month_user_YuHui_13910838033',
                    'lose_curr_last_month_user_WuShuXia_18911781586',
                    'lose_curr_last_month_user_SunQingHai_13501299927',
                    'lose_curr_last_month_user_DaLinTai_13910502331',
                    'lose_curr_last_month_user_SunPeng_13838067113',
                    'lose_curr_last_month_user_LiangShan_13533578570',
                    'lose_curr_last_month_user_WangLi_13601088874',
                ];
                var data = get_items(mTrend, 'lose_curr_last_month_user_', 5);
                fields = data[0];
                names = data[1];
                break;
        }
        if (names) {
            for (var i in fields) {
                items.push({
                    name: names[i],
                    data: mTrend[fields[i]],
                });
            }
        }

        var colors = [
            '#e91e63',
            '#2196F3',
            '#4CAF50',
            '#FF8A80',
            '#47639E',
            '#00bcd4',
            '#ff5722',
            '#9c27b0',
            '#86c351',
            '#795548'];
        Highcharts.theme = {
            colors: colors,
        };
        Highcharts.setOptions(Highcharts.theme);

        var marker = {
            states: {
                hover: {
                    lineColor: '',
                },
            },
        };
        for (var k in items) {
            var item = items[k];
            item['marker'] = {
                states: {
                    hover: {
                        lineColor: colors[k],
                    },
                },
            };
        }

        Highcharts.chart(pid, {
            chart: {
                type: 'spline',
            },
            title: {
                text: null,
            },
            xAxis: {
                tickInterval: 2,
                categories: titles,
                gridLineColor: '#e8e8e8',//纵向网格线颜色
                gridLineWidth: 1,//纵向网格线宽度
                gridLineDashStyle: 'ShortDash',
            },
            yAxis: {
                title: {
                    text: null,
                },
                plotLines: [
                    {
                        value: 0,
                        width: 1,
                        color: '#808080',
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
                },
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
                                lineWidth: 1,
                            },
                        },
                        radius: 2,  //曲线点半径，默认是4
                        //symbol: 'circle'//'url(/img/logo_zp.jpg)' //曲线点类型："circle", "square", "diamond", "triangle","triangle-down"，默认是"circle"
                    },
                    lineWidth: 2,
                },
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0,
            },
            exporting: {
                enabled: false,
            },
            series: items,
        });
    }

    function get_items(mTrend, cat, index) {
        var fields = [];
        var names = [];
        for (var key1 in mTrend) {
            if (key1.indexOf(cat) == 0) {
                fields.push(key1);
            }
        }
        for (var key2 in fields) {
            var name = fields[key2].split('_')[index];
            if (cPartner[name] == undefined) {
                names.push(name);
            } else {
                names.push(cPartner[name]);
            }
        }
        console.log(cat, [fields, names]);
        return [fields, names];
    }

    $(function () {
        setTimeout(function () {
            initCharts();
        }, 550);
    });
</script>

{{include file="layouts/footer.tpl"}}
