{{include file="layouts/header.tpl"}}
<style>
    .autoW {
        width: auto;
        display: inline-block;
    }
</style>
<div class="row">
    <div class="col-sm-6">
        <h4>订单列表
            {{if $is_stock_leader || $is_xiaodao}}
                <a href="javascript:;" class="opImport btn btn-outline btn-primary btn-xs">导入</a>
                <a href="javascript:;" class="opDelete btn btn-outline btn-danger btn-xs">删除</a>
                <a href="javascript:;" class="opCalSold btn btn-outline btn-primary btn-xs">计算今日卖出</a>
                <a href="javascript:;" class="opIncome btn btn-outline btn-warning btn-xs">导出今日盈亏</a>
                <a href="javascript:;" class="opHoldDays btn btn-outline btn-primary btn-xs">计算今日股票持有天数</a>
            {{/if}}
        </h4>
    </div>
    <div class="col-sm-6">
        {{if $success}}
            <div class="alert alert-success alert-dismissable">
                <button type="button" class="close close-alert" data-dismiss="alert" aria-hidden="true">×</button>
                {{$success}}
            </div>
        {{/if}}
        {{if $error}}
            <div class="alert alert-danger alert-dismissable">
                <button type="button" class="close close-alert" data-dismiss="alert" aria-hidden="true">×</button>
                {{$error}}
            </div>
        {{/if}}
    </div>
</div>
<div class="row">
    <form action="/stock/stock_order" method="get" class="form-inline">
        <div class="form-group">
            <input class="form-control" placeholder="用户名" type="text" name="name"
                   value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
            <input class="form-control" placeholder="用户手机" type="text" name="phone"
                   value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
            <input class="form-control" placeholder="股票代码" type="text" name="stock_id"
                   value="{{if isset($getInfo['stock_id'])}}{{$getInfo['stock_id']}}{{/if}}"/>
            <select class="form-control" name="status">
                <option value="">-=状态=-</option>
                {{foreach from=$stDict key=key item=item}}
                    <option value="{{$key}}" {{if $key==$status}}selected{{/if}}>{{$item}}</option>
                {{/foreach}}
            </select>
            {{if $is_staff}}{{/if}}
                <select class="form-control" name="bdphone">
                    <option value="">-=BD=-</option>
                    {{foreach from=$bds key=key item=item}}
                        <option value="{{$key}}" {{if $key==$bdphone}}selected{{/if}} >{{$item}}</option>
                    {{/foreach}}
                </select>

        </div>
        <button class="btn btn-primary">查询</button>
        <span class="space"></span>
    </form>
    <div class="row-divider"></div>
    <input class="form-control autoW beginDate my-date-input" placeholder="开始时间" name="sdate">
    至
    <input class="form-control autoW endDate my-date-input" placeholder="截止时间" name="edate">

    <select class="form-control autoW" name="st">
        <option value="">-=状态=-</option>
        {{foreach from=$stDict key=key item=item}}
            <option value="{{$key}}">{{$item}}</option>
        {{/foreach}}
    </select>
    <button class="btn btn-primary opExcel">导出我的客户</button>
</div>

<div class="row-divider"></div>
<div class="row">
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            {{if $is_staff}}{{/if}}
            <th>BD</th>

            <th>用户名|手机</th>
            <th>股票名称|股票代码</th>
            <th>股数|初期借款</th>
            {{if $is_staff}}{{/if}}
            <th>状态</th>
            <th>今日价格</th>
            <th>收益</th>
            <th>持股天数</th>

            <th>时间</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$list item=item}}
            <tr>
                {{if $is_staff}}{{/if}}
                <td>{{$item.uPtName}}
                    <br>{{$item.uPtPhone}}
                </td>

                <td>{{$item.oName}}({{$item.oPhone}})</td>
                <td>
                    {{$item.oStockName}}<br>
                    {{$item.oStockId}}
                </td>
                <td>
                    {{$item.oStockAmt}}<br>
                    {{$item.oLoan}}<br>
                    成本：{{$item.oCostPrice}}
                </td>
                {{if $is_staff}}{{/if}}
                <td>{{$item.st_t}}</td>
                <td>
                    开盘：{{$item.oOpenPrice}}<br>
                    收盘：{{$item.oClosePrice}}<br>
                    均价：{{$item.oAvgPrice}}
                </td>
                <td>
                    收益：{{$item.oIncome}}<br>
                    收益率：{{$item.oRate}}
                </td>
                <td>{{$item.oHoldDays}}</td>


                <td>{{$item.dt}}</td>
            </tr>
        {{/foreach}}
        </tbody>
    </table>
    {{$pagination}}
</div>

<div class="modal fade" id="modModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">上传订单汇总数据Excel</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" action="/stock/upload_excel" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="cat" value="order"/>
                    <input type="hidden" name="sign" value="up"/>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Excel文件</label>

                        <div class="col-sm-8">
                            <input type="file" name="excel" accept=".xls,.xlsx" class="form-control-static"/>

                            <p class="help-block">点这里上传</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"></label>

                        <div class="col-sm-8">
                            <input type="submit" class="btn btn-primary" id="btnUpload" value="上传Excel"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modModal_d" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">删除指定日期的订单</h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">删除订单的日期</label>
                        <div class="col-sm-8">
                            <input type="text" data-field="dt" class="form-control my-date-input"/>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="btnComfirm">确定</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modModal_c" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">计算今日卖出的订单</h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">上个交易日日期</label>
                        <div class="col-sm-8">
                            <input type="text" data-field="cdt" class="form-control my-date-input"/>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="btnComfirm_c">确定</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modModal_income" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">导出盈亏</h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">选择日期</label>
                        <div class="col-sm-8">
                            <input type="text" data-field="imcome_dt" class="form-control my-date-input"/>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="btnComfirm_income">确定</button>
            </div>
        </div>
    </div>
</div>

<script>
  $sls = {
    dt: $('[data-field=dt]'),
    cdt: $('[data-field=cdt]'),
    imcome_dt: $('[data-field=imcome_dt]'),

  };
  $('.opImport').on('click', function() {
    $('#modModal').modal('show');
  });
  $('.opDelete').on('click', function() {
    $sls.dt.val('');
    $('#modModal_d').modal('show');
  });

  $('#btnComfirm').on('click', function() {
    var dt = $sls.dt.val();
    if (!dt) {
      layer.msg('还没填写日期哦');
      return;
    }
    if ($sls.load_flag) {
      return;
    }
    layer.load();
    $sls.load_flag = 1;
    $.post('/api/stock', {
      tag: 'delete_stock_order',
      dt: dt,
    }, function(resp) {
      layer.closeAll();
      $sls.load_flag = 0;
      layer.msg(resp.msg);
      if (resp.code == 0) {
        setTimeout(function() {
          location.reload();
        }, 2000);
      }
    }, 'json');
  });
  /********************* 导出我的客户 start *********************************/
  $('.opExcel').on('click', function() {
    // var admin = $("select[name=admin]").val();
    var sdate = $('input[name=sdate]').val();
    var edate = $('input[name=edate]').val();
    var st = $('select[name=st]').val();
    var url = '/stock/export_stock_order?sdate=' + sdate + '&edate=' + edate + '&sign=excel&st=' + st;
    location.href = url;
  });
  /********************* 导出我的客户 end ******************************/

  /********************* 计算今日卖出 start ******************************/
  $('.opCalSold').on('click', function() {
    $sls.cdt.val('');
    $('#modModal_c').modal('show');
  });
  $('#btnComfirm_c').on('click', function() {
    var dt = $sls.cdt.val();
    if (!dt) {
      layer.msg('还没填写日期哦');
      return;
    }
    if ($sls.load_flag) {
      return;
    }
    layer.load();
    $sls.load_flag = 1;
    $.post('/api/stock', {
      tag: 'cal_sold_order',
      dt: dt,
    }, function(resp) {
      layer.closeAll();
      $sls.load_flag = 0;
      layer.msg(resp.msg);
      if (resp.code == 0) {
        setTimeout(function() {
          location.reload();
        }, 2000);
      }
    }, 'json');
  });
  /********************* 计算今日卖出 end ******************************/

  /********************* 导出今日盈亏 start ******************************/
  // $(".opIncome").on("click", function () {
  //location.href = "/stock/export_today_income";
  // });
  $('.opIncome').on('click', function() {
    $sls.imcome_dt.val('');
    $('#modModal_income').modal('show');
  });

  $('#btnComfirm_income').on('click', function() {
    var dt = $sls.imcome_dt.val();
    if (!dt) {
      layer.msg('还没填写日期哦');
      return;
    }
    location.href = '/stock/export_today_income?dt=' + dt;
  });
  /********************* 导出今日盈亏 end ********************************/
  /********************* 导出今日盈亏 end ********************************/

  $('.opHoldDays').on('click', function() {
    if ($sls.load_flag) {
      return;
    }
    layer.load();
    $sls.load_flag = 1;
    $.post('/api/stock', {
      tag: 'cal_hold_days',
    }, function(resp) {
      layer.closeAll();
      $sls.load_flag = 0;
      layer.msg(resp.msg);
      if (resp.code == 0) {
        setTimeout(function() {
          location.reload();
        }, 2000);
      }
    }, 'json');

  });
  /********************* 导出今日盈亏 end ********************************/

</script>
{{include file="layouts/footer.tpl"}}