{{include file="layouts/header.tpl"}}
<style>

</style>
<div class="row">
  <div class="col-sm-6">
    <h4>配置项
      <a class="add_phone btn btn-xs btn-primary">添加手机号</a>
    </h4>
  </div>
</div>

<div class="row-divider"></div>
<div class="row">
  <div class="col-sm-12">
    <table class="table table-striped table-bordered">
      <thead>
      <tr>
        <th>#</th>
        <th>手机号</th>
        <th>状态</th>
        <th>备注</th>
        <th>时间</th>
        <th>操作</th>
      </tr>
      </thead>
      <tbody>
      {{foreach from=$list item=item key=key}}
        <tr>
          <td>{{$key+1}}</td>
          <td>{{$item.c_content}}</td>
          <td>
            <span class="m-status-{{$item.c_status}}">{{if $item.c_status==1}}使用{{else}}禁用{{/if}}</span>
          </td>
          <td>{{$item.c_note}}</td>
          <td>
            <div>{{$item.c_update_on}}</div>
          </td>
          <td data-id="{{$item.c_id}}" data-c_content="{{$item.c_content}}"
              data-c_note="{{$item.c_note}}" data-c_status="{{$item.c_status}}">
            <a class="btnModify btn btn-xs btn-primary">修改</a>
          </td>
        </tr>
      {{/foreach}}
      </tbody>
    </table>
  </div>
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
      <label class="col-sm-3 control-label">手机号:</label>
      <div class="col-sm-7">
        <input type="text" class="form-control c_content">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">状态:</label>
      <div class="col-sm-7">
        <select class="form-control c_status">
          {{foreach from=$stDict item=status key=key}}
            <option value="{{$key}}">{{$status}}</option>
          {{/foreach}}
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">备注:</label>
      <div class="col-sm-7">
        <textarea class="form-control c_note"></textarea>
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
            case "edit_main_config":
                postData = {
                    tag: tag,
                    c_note: $.trim($('.c_note').val()),
                    c_status: $.trim($('.c_status').val()),
                    c_content: $.trim($('.c_content').val()),
                    id: self.attr("id")
                };
                console.log(postData);
                if (!postData["c_content"]) {
                    layer.msg("不能为空！");
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
        $('#myModalLabel').html('修改' + td.attr('data-c_content'));
        $('.btnSaveMod').attr({
            tag: "edit_main_config",
            id: td.attr("data-id")
        });

        $('.c_status').val(td.attr("data-c_status"));
        $('.c_note').val(td.attr("data-c_note"));
        $('.c_content').val(td.attr("data-c_content"));
        $('#modModal').modal('show');
    });

    $(document).on("click", '.add_phone', function () {
        var vHtml = $('#formTmp').html();
        $('div.modal-body').html(vHtml);
        $('#myModalLabel').html('添加手机号');
        $('.btnSaveMod').attr({
            tag: "edit_main_config",
            id: ''
        });
        $('.c_note').val('');
        $('.c_content').val('');
        $('#modModal').modal('show');
    });


</script>
{{include file="layouts/footer.tpl"}}