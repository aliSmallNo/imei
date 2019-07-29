{{include file="layouts/header.tpl"}}
<style>
    .hval{
        word-break: break-all;
    }
</style>
<div class="row">
    <div class="col-sm-6">
        <h4>hash {{$_key}}键列表
        </h4>
    </div>

</div>
<div class="row">
    <form method="get" class="form-inline" action="/redis/keys">
        <input name="cat" type="text" class="form-control" placeholder="key name">
        <button type="submit" class="btn btn-primary">查询</button>
    </form>
</div>
<div class="row-divider"></div>
<div class="row">
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th>index</th>
            <th>field</th>
            <th class="col-sm-8">value</th>
            <th>field length</th>

        </tr>
        </thead>
        <tbody>
        {{foreach from=$data item=prod key=k}}
            <tr>

                <td>{{$k+1}}</td>
                <td>{{$prod.field}}</td>
                <td class="hval">{{$prod.val}}</td>
                <td>{{$prod.val_length}}</td>
            </tr>
        {{/foreach}}
        </tbody>
    </table>
</div>

<div class="modal fade" id="modModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">添加key</h4>
            </div>
            <div class="modal-body">
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">key名称:</label>
                        <div class="col-sm-7 form-control-static">
                            <input type="text" data_field="key_name" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">key二级名称:</label>
                        <div class="col-sm-7 form-control-static">
                            <input type="text" data_field="key_name_sub" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">key的值:</label>
                        <div class="col-sm-7 form-control-static">
                            <input type="text" data_field="key_val" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">key有效期:</label>
                        <div class="col-sm-7">
                            <input type="text" data_field="key_expire" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="btnSaveMod">确定保存</button>
            </div>
        </div>
    </div>
</div>

<script>
  $(document).on('click', '.delete_key', function() {
    var self = $(this);
    var key_name = self.closest('td').attr('data-key');

    ajax_req({
      tag: 'delete_key',
      key_name: key_name,
    });
  });

  var modal = $('#modModal');
  var key_type;

  $(document).on('click', '.add_key', function() {
    var self = $(this);
    key_type = self.closest('td').attr('data-type');

    $('input[data_field]').each(function() {
      $(this).val('');
    });
    modal.modal('show');

  });

  $(document).on('click', '#btnSaveMod', function() {

    var key_name = $('input[data_field=key_name]').val();
    var key_name_sub = $('input[data_field=key_name_sub]').val();
    var key_val = $('input[data_field=key_val]').val();
    var key_expire = $('input[data_field=key_expire]').val();

    ajax_req({
      tag: 'add_key',
      key_type: key_type,
      key_name: key_name,
      key_name_sub: key_name_sub,
      key_val: key_val,
      key_expire: key_expire,
    });
  });

  function ajax_req(data) {
    $.post('/api/redis_opt', data, function(resp) {
      layer.msg(resp.msg);
      if (resp.code == 0) {
        setTimeout(function() {
          //location.reload();
        }, 800);
      }
    }, 'json');
  }

</script>


{{include file="layouts/footer.tpl"}}