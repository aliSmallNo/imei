{{include file="layouts/header.tpl"}}
<style>
  td, th {
    font-size: 10px;
  }

  th {
    max-width: 40px;
  }

  .span {
    font-size: 8px;
    background: #aaa;
    display: inline-block;
    padding: 1px 3px;
    border-radius: 3px;
    color: #fff;
    margin: 1px 0;
  }

  .span_upd {
    font-size: 12px;
    color: #666666;
  }

  .border_r {
    border-right: 2px solid #666 !important;
  }
</style>
<div class="row">
  <div class="col-sm-6">
    <h4>{{$cat}}日 指数列表 <span class="span_upd">数据更新：{{$update_on}}</span></h4>
  </div>
</div>
<div class="row">
  <form action="/stock/stock_main" method="get" class="form-inline">
    <div class="form-group">
      <select class="form-control" name="cat">
        {{foreach from=$cats item=day key=key}}
          <option value="{{$key}}" {{if $key==$cat}}selected{{/if}}>{{$day}}</option>
        {{/foreach}}
      </select>
    </div>

    <button class="btn btn-primary">查询</button>
    <span class="space"></span>
  </form>
</div>

<div class="row-divider"></div>
<div class="row">
  <table class="table table-striped table-bordered">
    <thead>
    <tr>
      <th>#</th>
      <th>交易日期</th>
      <th>
        500ETF
      </th>
      <th>
        上证指数
      </th>
      <th>
        上证交易额
      </th>
      <th>
        深圳交易额
      </th>
      <th>
        合计交易额
      </th>
      <th class="border_r">
        散户比值
      </th>

      <th>
        上证涨跌
      </th>
      <th>
        <!-- 散户比值 -->
        <!-- 比例 -->
        散户比值
      </th>
      <th>
        <!-- 交易额 -->
        <!-- 比例 -->
        合计交易额
      </th>
      <th>
        <!-- 上证均值 -->
        <!-- 位置 -->
        上证指数
      </th>
      <th>
        上证交易额
      </th>

      <th>买入</th>
      <th>卖出</th>
    </tr>
    </thead>
    <tbody>
    {{foreach from=$list item=item key=key}}
      <tr>
        <td>{{$key+1}}</td>
        <td>{{$item.s_trans_on}}</td>
        <td>{{$item.m_etf_close}}</td>
        <td>{{$item.m_sh_close}}</td>
        <td>{{$item.m_sh_turnover}}</td>
        <td>{{$item.m_sz_turnover}}</td>
        <td>{{$item.m_sum_turnover}}</td>
        <td class="border_r">{{$item.m_cus_rate}}%</td>

        <td>{{$item.s_sh_change}}%</td>
        <td>
          <div>均值 {{$item.s_cus_rate_avg}}%</div>
          <div>均值比例 {{$item.s_cus_rate_avg_scale}}%</div>
        </td>
        <td>
          <div>均值 {{$item.s_sum_turnover_avg}}</div>
          <div>均值比例 {{$item.s_sum_turnover_avg_scale}}%</div>
        </td>
        <td>
          <div>均值 {{$item.s_sh_close_avg}}</div>
          <div>均值比例 {{$item.s_sh_close_avg_scale}}%</div>
        </td>
        <td>
          <div>均值 {{$item.s_sh_turnover_avg}}</div>
          <div>均值比例 {{$item.s_sh_turnover_avg_scale}}%</div>
        </td>
        <td>
          {{foreach from=$item.buys item=buy}}
            <span class="span">{{$buy}}</span>
          {{/foreach}}
        </td>
        <td>
          {{foreach from=$item.solds item=sold}}
            <span class="span">{{$sold}}</span>
          {{/foreach}}
        </td>
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
                  aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="myModalLabel">上传操作数据Excel</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" action="/stock/upload_excel" method="post" enctype="multipart/form-data">
          <input type="hidden" name="cat" value="action"/>
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

<div class="modal fade" id="modModal_change" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                  aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">更新状态改变用户</h4>
      </div>
      <div class="modal-body">
        <div class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-3 control-label">日期</label>
            <div class="col-sm-8">
              <input type="text" data-field="cdt" class="form-control my-date-input"/>
            </div>
          </div>

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-primary" id="btnComfirm_change">确定</button>
      </div>
    </div>
  </div>
</div>

<script>
    var $sls = {
        load_flag: false,
        cdt: $('input[data-field=cdt]'),
    };
    $('.opImport').on('click', function () {
        $('#modModal').modal('show');
    });
    /********************* 更新状态改变用户 start ******************************/
    $('.updateChangeUser').on('click', function () {
        $sls.cdt.val('');
        $('#modModal_change').modal('show');
    });
    $('#btnComfirm_change').on('click', function () {
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
            tag: 'update_user_action_change',
            dt: dt,
        }, function (resp) {
            layer.closeAll();
            $sls.load_flag = 0;
            layer.msg(resp.msg);
            if (resp.code == 0) {
                setTimeout(function () {
                    location.reload();
                }, 2000);
            }
        }, 'json');
    });
    /********************* 更新状态改变用户 start ******************************/
</script>
{{include file="layouts/footer.tpl"}}