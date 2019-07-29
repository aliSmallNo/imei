{{include file="layouts/header.tpl"}}
<style>

</style>
<div class="row">
    <div class="col-sm-6">
        <h4>键列表({{$dbsize}})
            <a class="addClue btn btn-xs btn-primary">添加key</a>
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
    <ul class="nav nav-tabs">
        {{foreach from=$types key=key item=type}}
            <li class="ng-scope {{if $_type== $type}} active{{/if}}">
                <a href="/redis/keys?_type={{$type}}"
                   class="ng-binding">{{$type}}
                    <span class="badge">{{count($data[$type])}}</span>
                </a>
            </li>
        {{/foreach}}
    </ul>
</div>
<div class="row-divider"></div>
<div class="row">
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th>index</th>
            <th>key名称</th>
            <th>key类型</th>
            <th>长度</th>
            <th>key有效期</th>
            <th>key有效期</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$data[$_type] item=prod key=k}}
            <tr>
                <td>{{$k+1}}</td>
                <td>
                    {{if $prod['type']=='hash'}}
                        <a href="/redis/hkeys?_type={{$prod['type']}}&_key={{$prod['key']}}">{{$prod['key']}}</a>
                    {{/if}}
                    {{if $prod['type']=='list'}}
                        <a href="/redis/list?_type={{$prod['type']}}&_key={{$prod['key']}}">{{$prod['key']}}</a>
                    {{/if}}
                    {{if $prod['type']=='set'}}
                        <a href="/redis/set?_type={{$prod['type']}}&_key={{$prod['key']}}">{{$prod['key']}}</a>
                    {{/if}}
                    {{if !in_array($prod['type'],['hash','list','set'])}}
                        {{$prod['key']}}
                    {{/if}}
                </td>
                <td>{{$prod['type']}}</td>
                <td>{{$prod['len']}}</td>
                <td>{{$prod['expire']}}</td>
                <td>{{$prod['expire2']}}</td>
                <td data-key="{{$prod['key']}}" data-type="{{$prod['type']}}">
                    <a href="javascript:;" class="add_key btn btn-outline btn-primary btn-xs">添加</a>
                    <a href="javascript:;" class="delete_key btn btn-outline btn-danger btn-xs">删除</a>
                </td>
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