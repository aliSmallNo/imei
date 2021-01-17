{{include file="layouts/header.tpl"}}
<style>
  td, th {
    font-size: 12px;
  }

  .form_tip {
    font-size: 10px;
    color: #f80;
    font-weight: 400;
  }

  .bot_line div {
    border-bottom: 1px solid #aaa;
  }

  .avg_font {
    color: #ff3c08;
    font-weight: 500;
    border: none !important;
  }

  .bg_err {
    background: rgba(0, 128, 0, 0.78);
    color: #fff;
  }
</style>
<div class="row">
  <div class="col-sm-6">
    <h4>策略结果列表
    </h4>
    <div>
      <a class="reset_result btn btn-xs btn-primary">重置结果</a>
      <span class="form_tip">重置结果会删除现有数据，然后根据当前策略重新计算结果，每次改变策略后应该重置结果</span>
    </div>
  </div>
</div>
<div class="row">
  <form action="/stock/stock_main_result2" method="get" class="form-inline">
    <div class="form-group">
      <input type="text" name="name" class="form-control" placeholder="策略名称" value="{{$name}}">
      <select class="form-control" name="cat">
        <option value="">-=请选择=-</option>
        {{foreach from=$cats item=day key=key}}
          <option value="{{$key}}" {{if $key==$cat}}selected{{/if}}>{{$day}}</option>
        {{/foreach}}
      </select>
      <select class="form-control" name="price_type">
        <option value="">-=请选择价格类型=-</option>
        {{foreach from=$price_types item=price_type_name key=key}}
          <option value="{{$key}}" {{if $key==$price_type}}selected{{/if}}>{{$price_type_name}}</option>
        {{/foreach}}
      </select>

      <select class="form-control" name="right_rate_gt_val">
        <option value="">-=请选择正确率=-</option>
        {{foreach from=$right_rate_gt_val_map item=$right_rate_gt_val_name key=key}}
          <option value="{{$key}}" {{if $key==$right_rate_gt_val}}selected{{/if}}>{{$right_rate_gt_val_name}}</option>
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
      <th>{{$price_type_t}}</th>
      <th class="col-sm-3">买入</th>
      <th class="col-sm-3">卖出</th>
      <th class="col-sm-2">预警</th>
      <th>备注</th>
      <th class="col-sm-2">复盘</th>
      <th>时间</th>
      {{if $is_stock_leader}}
        <th>操作</th>
      {{/if}}
    </tr>
    </thead>
    <tbody>

    {{foreach from=$list item=item key=key}}
      <tr>
        <td>{{$key+1}}</td>
        <td>{{$item.r_trans_on}}</td>
        <td>{{$item.curr_stock_price}}</td>

        <!-- 买入 -->
        <td class="bot_line">
          <div class="hidden">
            {{if $item.r_buy5}}
              <div>5日:{{$item.r_buy5}}</div>{{/if}}
            {{if $item.r_buy10}}
              <div>10日:{{$item.r_buy10}}</div>{{/if}}
            {{if $item.r_buy20}}
              <div>20日:{{$item.r_buy20}}</div>{{/if}}
            {{if $item.r_buy60}}
              <div>60日:{{$item.r_buy60}}</div>{{/if}}
          </div>

          <!-- 买入正确率 -->
          {{foreach from=$item.buy_rules_right_rate item=right_rate_item key=day}}
            {{if $right_rate_item}}
              {{foreach from=$right_rate_item item=desc}}
                <div>{{$day}}日: {{$desc.rule_name}}, {{$desc.times_yes_rate}}%, {{$desc.append_hope_val}}%, {{$desc.no_avg_rate}}%, {{$desc.yes_avg_rate}}%, {{$desc.d1_median0_yes}}%, {{$desc.d1_median0_no}}%</div>
              {{/foreach}}
            {{/if}}
          {{/foreach}}
          <br>

          {{if $item.f1}}
            {{if $item.buy_avg_right_rate}}
              <div class="avg_font">平均正确率：{{$item.buy_avg_right_rate}}%</div>{{/if}}
            {{if $item.buy_avg_right_rate_2p}}
              <div class="avg_font">2P-1：{{$item.buy_avg_right_rate_2p}}%</div>{{/if}}
            {{if $item.buy_avg_rate}}
            <div class="avg_font" data-co="{{$item.buy_avg_rate_buy_co}}">平均收益率：{{$item.buy_avg_rate}}%</div>{{/if}}
          {{/if}}
        </td>

        <!-- 卖出 -->
        <td class="bot_line">
          <div class="hidden">
            {{if $item.r_sold5}}
              <div>5日:{{$item.r_sold5}}</div>{{/if}}
            {{if $item.r_sold10}}
              <div>10日:{{$item.r_sold10}}</div>{{/if}}
            {{if $item.r_sold20}}
              <div>20日:{{$item.r_sold20}}</div>{{/if}}
            {{if $item.r_sold60}}
              <div>60日:{{$item.r_sold60}}</div>{{/if}}
          </div>

          <!-- 卖出正确率 -->
          {{foreach from=$item.sold_rules_right_rate item=right_rate_item key=day}}
            {{if $right_rate_item}}
              {{foreach from=$right_rate_item item=desc}}
                <div>{{$day}}日: {{$desc.rule_name}}, {{$desc.times_yes_rate}}%, {{$desc.append_hope_val}}%, {{$desc.no_avg_rate}}%, {{$desc.yes_avg_rate}}%, {{$desc.d1_median0_yes}}%, {{$desc.d1_median0_no}}%</div>
              {{/foreach}}
            {{/if}}
          {{/foreach}}

          <br>

          {{if $item.f2}}
            {{if $item.sold_avg_right_rate}}
              <div class="avg_font">平均正确率{{$item.sold_avg_right_rate}}%</div>{{/if}}
            {{if $item.sold_avg_right_rate_2p}}
              <div class="avg_font">2P-1：{{$item.sold_avg_right_rate_2p}}%</div>{{/if}}
            {{if $item.sold_avg_rate}}
            <div class="avg_font" data-co="{{$item.sold_avg_rate_sold_co}}">平均收益率：{{$item.sold_avg_rate}}%</div>{{/if}}
          {{/if}}
        </td>

        <td class="bot_line">
          {{if $item.r_warn5}}
            <div>5日:{{$item.r_warn5}}</div>{{/if}}
          {{if $item.r_warn10}}
            <div>10日:{{$item.r_warn10}}</div>{{/if}}
          {{if $item.r_warn20}}
            <div>20日:{{$item.r_warn20}}</div>{{/if}}
          {{if $item.r_warn60}}
            <div>60日:{{$item.r_warn60}}</div>{{/if}}

          {{if $item.warn_avg_right_rate}}
            <div class="avg_font">平均正确率{{$item.warn_avg_right_rate}}%</div>{{/if}}
          {{if $item.warn_avg_right_rate_2p}}
            <div class="avg_font">2P-1：{{$item.warn_avg_right_rate_2p}}%</div>{{/if}}
          {{if $item.warn_avg_rate}}
          <div class="avg_font" data-co="{{$item.warn_avg_rate_warn_co}}">平均收益率：{{$item.warn_avg_rate}}%</div>{{/if}}
        </td>

        <td class="{{$item.cls}}">{{$item.r_note}}</td>
        <td>{{$item.r_cb}}</td>
        <td>
          <div>{{$item.r_added_on}}</div>
          <div>{{$item.r_update_on}}</div>
        </td>
        {{if $is_stock_leader}}
        <td data-id="{{$item.r_id}}" data-r_trans_on="{{$item.r_trans_on}}" data-r_note="{{$item.r_note}}" data-r_cb="{{$item.r_cb}}">
          <a class="btnModify btn btn-xs btn-primary">修改</a>
        </td>
        {{/if}}
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
        <h4 class="modal-title" id="myModalLabel">xxx</h4>
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
      <label class="col-sm-4 control-label">备注:</label>
      <div class="col-sm-7">
        <select class="form-control r_note">
          <option value="">-=请选择=-</option>
          {{foreach from=$notes item=item key=key}}
            <option value="{{$key}}">{{$item}}</option>
          {{/foreach}}
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">复盘心得:</label>
      <div class="col-sm-7">
        <textarea class="form-control r_cb" rows="3"></textarea>
      </div>
    </div>
  </div>
</script>
<script>
    var $sls = {
        load_flag: false,
    };

    $(document).on('click', '.btnSaveMod', function () {
        var self = $(this);
        var tag = self.attr('tag');
        var postData = null;
        var url = '/api/stock_main';
        console.log(tag);
        switch (tag) {
            case "edit_main_result2":
                postData = {
                    tag: tag,
                    r_note: $.trim($('.r_note').val()),
                    r_cb: $.trim($('.r_cb').val()),
                    id: self.attr("id")
                };
                console.log(postData);
                if (!postData["r_note"]
                ) {
                    layer.msg("备注不能为空！");
                    return;
                }
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
        $('#myModalLabel').html('修改' + td.attr('data-r_trans_on'));
        $('.btnSaveMod').attr({
            tag: "edit_main_result2",
            id: td.attr("data-id")
        });

        $('.r_note').val(td.attr("data-r_note"));
        $('.r_cb').val(td.attr("data-r_cb"));
        $('#modModal').modal('show');
    });

    $(document).on("click", '.reset_result', function () {
        layer.confirm('您确定重置结果数据', {
            btn: ['确定', '取消'],
            title: '重置数据'
        }, function () {
            reset();
        }, function () {
        });
    });

    function reset() {
        var url = '/api/stock_main';
        layer.load();
        $.post(url, {tag: 'reset_main_result2'}, function (resp) {
            layer.closeAll();
            layer.msg(resp.msg);
            if (resp.code == 0) {
                setTimeout(function () {
                    location.reload();
                }, 800);
            }
        }, 'json');
    }

</script>
{{include file="layouts/footer.tpl"}}