{{include file="layouts/header.tpl"}}
<style>
    .type_1, .type_2 {
        font-size: 10px;
        background: #888;
        color: #fff;
        padding: 2px 4px;
        border-radius: 3px;
    }

    .type_2 {
        background: #00aa00;
    }

    .rate {
        font-size: 12px;
        color: red;
    }
</style>
<div class="row">
    <h4>BD管理客户列表
        {{if $is_stock_leader}}
            <a href="javascript:;" class="add_user btn btn-outline btn-primary btn-xs">添加用户</a>
        {{/if}}
    </h4>
</div>
<div class="row">
    <form action="/stock/stock_user_admin" method="get" class="form-inline">
        <div class="form-group">
            <input class="form-control" placeholder="渠道名" type="text" name="name"
                   value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
            <input class="form-control" placeholder="渠道手机" type="text" name="phone"
                   value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
            <select class="form-control" name="bdphone">
                <option value="">-=BD=-</option>
                {{foreach from=$bds key=key item=item}}
                    <option value="{{$key}}">{{$item}}</option>
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
            <th>渠道名</th>
            <th>渠道手机</th>
            
            <th>BD名</th>
            <th>BD手机</th>
            
            <th>备注</th>
            <th>时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        {{foreach from=$list item=item}}
            <tr data-uaPhone="{{$item.uaPhone}}" data-uaId="{{$item.uaId}}"
                data-uaPtPhone="{{$item.uaPtPhone}}" data-uaNote="{{$item.uaNote}}">
                <td>{{$item.uaName}}</td>
                <td>{{$item.uaPhone}}</td>

                <td>{{$item.uaPtName}}</td>
                <td>{{$item.uaPtPhone}}</td>

                <td>{{$item.uaNote}}</td>
                <td>{{$item.uaUpdatedOn}}</td>
                <td>
                    {{if $is_run}}
                        <a href="javascript:;" class="edit_user btn btn-outline btn-primary btn-xs">修改用户</a>
                    {{/if}}
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
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">渠道手机号</label>
                        <div class="col-sm-8">
                            <input type="tel" data-field="uaPhone" class="form-control"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">BD手机号</label>
                        <div class="col-sm-8">
                            <input type="tel" data-field="uaPtPhone" class="form-control"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">备注</label>
                        <div class="col-sm-8">
                            <input type="text" data-field="uaNote" class="form-control"/>
                            <input type="hidden" data-field="uaId" class="form-control"/>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="btnSave">确定保存</button>
            </div>

        </div>
    </div>
</div>
<script>
  $sls = {
    loadflag: 0,
    tag: '',
    modal: $('#modModal'),
    title: $('#modModal').find('.modal-header h4'),
  };

  $(document).on('click', '.add_user', function() {
    $sls.tag = 'edit_user_admin';
    $sls.title.html('添加用户信息');
    $('[data-field]').each(function() {
      if ($(this).attr('data-field') == 'uaType') {
        //$(this).val(1);
      }
      else {
        $(this).val('');
      }
    });
    $sls.modal.modal('show');
  });

  $(document).on('click', '.edit_user', function() {
    var self = $(this).closest('tr');
    $sls.tag = 'edit_user_admin';
    $sls.title.html('修改用户信息');
    $('[data-field=uaNote]').val(self.attr('data-uaNote'));
    $('[data-field=uaPtPhone]').val(self.attr('data-uaPtPhone'));
    $('[data-field=uaPhone]').val(self.attr('data-uaPhone'));
    $('[data-field=uaId]').val(self.attr('data-uaId'));
    $sls.modal.modal('show');
  });

  $(document).on('click', '#btnSave', function() {
    var uaPhone = $('[data-field=uaPhone]').val();
    var uaPtPhone = $('[data-field=uaPtPhone]').val();
    var uaNote = $('[data-field=uaNote]').val();
    var uaId = $('[data-field=uaId]').val();
    if (!uaPhone) {
      layer.msg('渠道手机号不能为空');
      return;
    }
    if (!uaPtPhone) {
      layer.msg('BD手机号不能为空');
      return;
    }
    var postData = {
      uaPhone: uaPhone,
      uaPtPhone: uaPtPhone,
      uaId: uaId,
      uaNote: uaNote,
      tag: $sls.tag,
    };
    console.log(postData);

    if ($sls.loadflag) {
      return;
    }
    $sls.loadflag = 1;
    layer.load();
    $.post('/api/stock', postData, function(resp) {
      $sls.loadflag = 0;
      layer.closeAll();
      if (resp.code == 0) {
        layer.msg(resp.msg);
        setTimeout(function() {
          //location.reload();
        }, 1500);
      }
      else {
        layer.msg(resp.msg);
      }
    }, 'json');
  });
</script>
{{include file="layouts/footer.tpl"}}