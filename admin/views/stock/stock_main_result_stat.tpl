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
    <h4>策略结果列表 统计
    </h4>
  </div>
</div>
<div class="row-divider"></div>
<div class="row">
  {{foreach from=$list item=item key=key}}
    <div class="col-sm-6">
      <table class="table table-striped table-bordered">
        <thead>
        <tr>
          <th>DAY</th>
          <th>对</th>
          <th>错</th>
          <th>中性</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$item item=it1 key=rule_name}}
          {{$rule_name}}
          {{foreach from=$it1 item=it key=day}}
            <tr>
              <td>{{$day}}</td>
              <td>{{$it.times_yes}}次 - {{$it.times_yes_rate}}%</td>
              <td>{{$it.times_no}}次 - {{$it.times_no_rate}}%</td>
              <td>{{$it.times_mid}}次 - {{$it.times_mid_rate}}%</td>
            </tr>
          {{/foreach}}
        {{/foreach}}
        </tbody>
      </table>
    </div>
  {{/foreach}}
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