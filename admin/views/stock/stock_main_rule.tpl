{{include file="layouts/header.tpl"}}
<style>
  td, th {
    font-size: 12px;
  }

  th {
    max-width: 40px;
  }

  .form_tip {
    font-size: 10px;
    color: #f80;
    font-weight: 400;
  }
</style>
<div class="row">
  <div class="col-sm-6">
    <h4>策略列表
      <a class="add_rule btn btn-xs btn-primary">添加策略</a>
    </h4>
  </div>
</div>
<div class="row">
  <form action="/stock/stock_main_rule" method="get" class="form-inline">
    <div class="form-group">
      <select class="form-control" name="cat">
        <option value="">-=请选择=-</option>
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
      <th>买卖名称</th>
      <th>状态</th>
      <th>类型</th>
      <th>
        <!-- 上证涨跌大于 -->
        <!-- 上证涨跌小于 -->
        大盘
      </th>
      <th>
        <!-- 散户比值均值比例大于 -->
        <!-- 散户比值均值比例小于 -->
        散户
      </th>
      <th>
        <!-- 合计交易额均值比例大于 -->
        <!-- 合计交易额均值比例小于 -->
        交易额
      </th>
      <th>
        <!-- 上证指数均值比例大于 -->
        <!-- 上证指数均值比例小于 -->
        上证交易额
      </th>
      <th>差值</th>
      <th>上证指数均值</th>
      <th>上证指数60日均值-上证指数10日均值</th>
      <th>上证指数均值比例/上证涨跌</th>
      <th>日期</th>
      <th>DAY</th>
      <th>备注</th>
      <th>时间</th>
      <th>操作</th>
    </tr>
    </thead>
    <tbody>
    {{foreach from=$list item=item}}
      <tr>
        <td>{{$item.r_name}}</td>
        <td>
          <a class="btn btn-xs btn-{{if $item.r_status==9}}danger{{else}}primary{{/if}}">{{$item.r_status_t}}</a>
        </td>
        <td>{{$item.r_cat_t}}</td>
        <td>
          <div>>{{$item.r_stocks_gt}}</div>
          <div><{{$item.r_stocks_lt}}</div>
        </td>
        <td>
          <div>>{{$item.r_cus_gt}}</div>
          <div><{{$item.r_cus_lt}}</div>
        </td>
        <td>
          <div>>{{$item.r_turnover_gt}}</div>
          <div><{{$item.r_turnover_lt}}</div>
        </td>
        <td>
          <div>>{{$item.r_sh_turnover_gt}}</div>
          <div><{{$item.r_sh_turnover_lt}}</div>
        </td>
        <!-- 差值 -->
        <td>
          <div>>{{$item.r_diff_gt}}</div>
          <div><{{$item.r_diff_lt}}</div>
        </td>
        <td>
          <div>>{{$item.r_sh_close_avg_gt}}</div>
          <div><{{$item.r_sh_close_avg_lt}}</div>
        </td>
        <td>
          <div>>{{$item.r_sh_close_60avg_10avg_offset_gt}}</div>
          <div><{{$item.r_sh_close_60avg_10avg_offset_lt}}</div>
        </td>
        <!-- 上证指数均值比例/上证涨跌 比例 -->
        <td>
          <div>>{{$item.r_sh_close_avg_change_rate_gt}}</div>
          <div><{{$item.r_sh_close_avg_change_rate_lt}}</div>
        </td>
        <td>
          <div>>{{$item.r_date_gt}}</div>
          <div><{{$item.r_date_lt}}</div>
        </td>
        <td>{{$item.r_scat}}</td>
        <td class="col-sm-1">{{$item.r_note}}</td>
        <td>
          <div>{{$item.r_added_on}}</div>
          <div>{{$item.r_update_on}}</div>
        </td>
        <td data-id="{{$item.r_id}}" data-r_name="{{$item.r_name}}"
            data-r_status="{{$item.r_status}}" data-r_cat="{{$item.r_cat}}"
            data-r_stocks_gt="{{$item.r_stocks_gt}}" data-r_stocks_lt="{{$item.r_stocks_lt}}"
            data-r_cus_gt="{{$item.r_cus_gt}}" data-r_cus_lt="{{$item.r_cus_lt}}"
            data-r_turnover_gt="{{$item.r_turnover_gt}}" data-r_turnover_lt="{{$item.r_turnover_lt}}"
            data-r_sh_turnover_gt="{{$item.r_sh_turnover_gt}}" data-r_sh_turnover_lt="{{$item.r_sh_turnover_lt}}"
            data-r_diff_gt="{{$item.r_diff_gt}}" data-r_diff_lt="{{$item.r_diff_lt}}"
            data-r_sh_close_avg_gt="{{$item.r_sh_close_avg_gt}}" data-r_sh_close_avg_lt="{{$item.r_sh_close_avg_lt}}"
            data-r_sh_close_60avg_10avg_offset_gt="{{$item.r_sh_close_60avg_10avg_offset_gt}}"
            data-r_sh_close_60avg_10avg_offset_lt="{{$item.r_sh_close_60avg_10avg_offset_lt}}"
            data-r_sh_close_avg_change_rate_gt="{{$item.r_sh_close_avg_change_rate_gt}}"
            data-r_sh_close_avg_change_rate_lt="{{$item.r_sh_close_avg_change_rate_lt}}"
            data-r_date_gt="{{$item.r_date_gt}}" data-r_date_lt="{{$item.r_date_lt}}"
            data-r_scat="{{$item.r_scat}}"
            data-r_note="{{$item.r_note}}"
        >
          <a class="btnModify btn btn-xs btn-primary">修改策略</a>
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
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">分配BD信息</h4>
      </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-primary btnSaveMod">确定保存</button>
      </div>
    </div>
  </div>
</div>
<script type="text/html" id="formTmp">
  <div class="form-horizontal">
    <div class="form-group">
      <label class="col-sm-4 control-label">类型:</label>
      <div class="col-sm-7">
        <select class="form-control r_cat">
          <option value="0">-=请选择=-</option>
          {{foreach from=$cats item=day key=key}}
            <option value="{{$key}}">{{$day}}</option>
          {{/foreach}}
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">状态:</label>
      <div class="col-sm-7">
        <select class="form-control r_status">
          <option value="0">-=请选择=-</option>
          {{foreach from=$sts item=item key=key}}
            <option value="{{$key}}">{{$item}}</option>
          {{/foreach}}
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">买卖名称:</label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_name">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">大盘大于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_stocks_gt">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">大盘小于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_stocks_lt">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">散户大于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_cus_gt">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">散户小于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_cus_lt">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">交易额大于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_turnover_gt">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">交易额小于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_turnover_lt">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">上证交易额大于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_sh_turnover_gt">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">上证交易额小于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_sh_turnover_lt">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">差值【合计交易额均值比例—散户比值均值比例】大于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_diff_gt">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">差值【合计交易额均值比例—散户比值均值比例】小于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_diff_lt">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">上证指数均值大于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_sh_close_avg_gt">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">上证指数均值小于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_sh_close_avg_lt">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">上证指数60日均值-上证指数10日均值 大于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_sh_close_60avg_10avg_offset_gt">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">上证指数60日均值-上证指数10日均值 小于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_sh_close_60avg_10avg_offset_lt">
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-4 control-label">上证指数均值比例/上证涨跌 比例 大于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_sh_close_avg_change_rate_gt">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">上证指数均值比例/上证涨跌 比例 小于: <p class="form_tip">填 999 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_sh_close_avg_change_rate_lt">
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-4 control-label">日期大于: <p class="form_tip">不填 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_date_gt my-date-input">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">日期小于: <p class="form_tip">不填 则忽略此条件</p></label>
      <div class="col-sm-7">
        <input type="text" class="form-control r_date_lt my-date-input">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">day类型: <p class="form_tip">不填 则忽略此条件</p></label>
      <div class="col-sm-7">
        <select class="form-control r_scat">
          <option value="0">-=请选择=-</option>
          {{foreach from=$scat item=item key=key}}
            <option value="{{$key}}">{{$item}}</option>
          {{/foreach}}
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">备注:</label>
      <div class="col-sm-7">
        <textarea class="form-control r_note"></textarea>
      </div>
    </div>

  </div>
</script>
<script>
    var $sls = {
        load_flag: false,
    };
    $(document).on('click', '.add_rule', function () {
        var vHtml = $('#formTmp').html();
        $('div.modal-body').html(vHtml);
        $('#myModalLabel').html('添加策略');
        $('.btnSaveMod').attr({
            tag: "edit_main_rule",
            id: "",
        });
        $('#modModal').modal('show');

    });
    $(document).on('click', '.btnSaveMod', function () {
        var self = $(this);
        var tag = self.attr('tag');
        var postData = null;
        var url = '/api/stock_client';
        console.log(tag);
        switch (tag) {
            case "edit_main_rule":
                postData = {
                    tag: tag,
                    r_name: $.trim($('.r_name').val()),
                    r_status: $.trim($('.r_status').val()),
                    r_cat: $.trim($('.r_cat').val()),
                    r_stocks_gt: $.trim($('.r_stocks_gt').val()),
                    r_stocks_lt: $.trim($('.r_stocks_lt').val()),
                    r_cus_gt: $.trim($('.r_cus_gt').val()),
                    r_cus_lt: $.trim($('.r_cus_lt').val()),
                    r_turnover_gt: $.trim($('.r_turnover_gt').val()),
                    r_turnover_lt: $.trim($('.r_turnover_lt').val()),
                    r_sh_turnover_gt: $.trim($('.r_sh_turnover_gt').val()),
                    r_sh_turnover_lt: $.trim($('.r_sh_turnover_lt').val()),
                    r_diff_gt: $.trim($('.r_diff_gt').val()),
                    r_diff_lt: $.trim($('.r_diff_lt').val()),
                    r_sh_close_avg_gt: $.trim($('.r_sh_close_avg_gt').val()),
                    r_sh_close_avg_lt: $.trim($('.r_sh_close_avg_lt').val()),
                    r_sh_close_60avg_10avg_offset_gt: $.trim($('.r_sh_close_60avg_10avg_offset_gt').val()),
                    r_sh_close_60avg_10avg_offset_lt: $.trim($('.r_sh_close_60avg_10avg_offset_lt').val()),
                    r_sh_close_avg_change_rate_gt: $.trim($('.r_sh_close_avg_change_rate_gt').val()),
                    r_sh_close_avg_change_rate_lt: $.trim($('.r_sh_close_avg_change_rate_lt').val()),
                    r_date_gt: $.trim($('.r_date_gt').val()),
                    r_date_lt: $.trim($('.r_date_lt').val()),
                    r_scat: $.trim($('.r_scat').val()),
                    r_note: $.trim($('.r_note').val()),
                    id: self.attr("id")
                };
                console.log(postData);
                if (!postData["r_name"]
                    || !postData["r_status"]
                    || !postData["r_cat"]
                ) {
                    layer.msg("类型、状态、买卖名称不能为空！");
                    return;
                }
                url = '/api/stock_main';
                break;
        }
        if (postData) {
            layer.load();
            $.post(url, postData, function (resp) {
                layer.closeAll();
                layer.msg(resp.msg);
                if (resp.code == 0) {
                    setTimeout(function () {
                        location.reload();
                    }, 800);
                }
            }, 'json');
        }
    });

    $(document).on('click', '.btnModify', function () {
        var td = $(this).closest("td");

        var vHtml = $('#formTmp').html();
        $('div.modal-body').html(vHtml);
        $('#myModalLabel').html('修改策略');
        $('.btnSaveMod').attr({
            tag: "edit_main_rule",
            id: td.attr("data-id")
        });

        $('.r_name').val(td.attr("data-r_name"))
        $('.r_status').val(td.attr("data-r_status"))
        $('.r_cat').val(td.attr("data-r_cat"))
        $('.r_stocks_gt').val(td.attr("data-r_stocks_gt"))
        $('.r_stocks_lt').val(td.attr("data-r_stocks_lt"))
        $('.r_cus_gt').val(td.attr("data-r_cus_gt"))
        $('.r_cus_lt').val(td.attr("data-r_cus_lt"))
        $('.r_turnover_gt').val(td.attr("data-r_turnover_gt"))
        $('.r_turnover_lt').val(td.attr("data-r_turnover_lt"))
        $('.r_sh_turnover_gt').val(td.attr("data-r_sh_turnover_gt"))
        $('.r_sh_turnover_lt').val(td.attr("data-r_sh_turnover_lt"))
        $('.r_diff_gt').val(td.attr("data-r_diff_gt"))
        $('.r_diff_lt').val(td.attr("data-r_diff_lt"))
        $('.r_sh_close_avg_gt').val(td.attr("data-r_sh_close_avg_gt"))
        $('.r_sh_close_avg_lt').val(td.attr("data-r_sh_close_avg_lt"))
        $('.r_sh_close_60avg_10avg_offset_gt').val(td.attr("data-r_sh_close_60avg_10avg_offset_gt"))
        $('.r_sh_close_60avg_10avg_offset_lt').val(td.attr("data-r_sh_close_60avg_10avg_offset_lt"))
        $('.r_sh_close_avg_change_rate_gt').val(td.attr("data-r_sh_close_avg_change_rate_gt"))
        $('.r_sh_close_avg_change_rate_lt').val(td.attr("data-r_sh_close_avg_change_rate_lt"))
        $('.r_date_gt').val(td.attr("data-r_date_gt"))
        $('.r_date_lt').val(td.attr("data-r_date_lt"))
        $('.r_scat').val(td.attr("data-r_scat"))
        $('.r_note').val(td.attr("data-r_note"))
        $('#modModal').modal('show');


    });


    /********************* 更新状态改变用户 start ******************************/
    /********************* 更新状态改变用户 start ******************************/
</script>
{{include file="layouts/footer.tpl"}}