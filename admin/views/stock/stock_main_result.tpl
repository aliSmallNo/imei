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
    <h4>策略结果列表
    </h4>
    <div>
      <a class="reset_result btn btn-xs btn-primary">重置结果</a>
      <span class="form_tip">重置结果会删除现有数据，然后根据当前策略重新计算结果，每次改变策略后应该重置结果</span>
    </div>
  </div>
</div>
<div class="row">
  <form action="/stock/stock_main_result" method="get" class="form-inline">
    <div class="form-group">
      <input type="text" name="name" class="form-control" placeholder="策略名称" value="{{$name}}">
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

      <th>500etf</th>
      <th>5日买入</th>
      <th>10日买入</th>
      <th>20日买入</th>
      <th>5日卖出</th>
      <th>10日卖出</th>
      <th>20日卖出</th>

      <th>备注</th>
      <th>时间</th>
      <th>操作</th>
    </tr>
    </thead>
    <tbody>

    {{foreach from=$list item=item key=key}}
      <tr>
        <td>{{$key+1}}</td>
        <td>{{$item.r_trans_on}}</td>
        <td>{{$item.m_etf_close}}</td>

        <td>{{$item.r_buy5}}</td>
        <td>{{$item.r_buy10}}</td>
        <td>{{$item.r_buy20}}</td>
        <td>{{$item.r_sold5}}</td>
        <td>{{$item.r_sold10}}</td>
        <td>{{$item.r_sold20}}</td>

        <td>{{$item.r_note}}</td>
        <td>
          <div>{{$item.r_added_on}}</div>
          <div>{{$item.r_update_on}}</div>
        </td>
        <td data-id="{{$item.r_id}}" data-r_trans_on="{{$item.r_trans_on}}">
          <a class="btnModify btn btn-xs btn-primary">修改</a>
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

    $(document).on('click', '.btnSaveMod', function () {
        var self = $(this);
        var tag = self.attr('tag');
        var postData = null;
        var url = '/api/stock_main';
        console.log(tag);
        switch (tag) {
            case "edit_main_result":
                postData = {
                    tag: tag,
                    r_note: $.trim($('.r_note').val()),
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
            tag: "edit_main_result",
            id: td.attr("data-id")
        });

        $('.r_note').val(td.attr("data-r_note"));
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
        $.post(url, {tag: 'reset_main_result'}, function (resp) {
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