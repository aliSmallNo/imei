{{include file="layouts/header.tpl"}}

<div class="row">
    <div class="col-sm-6">
        <h4>操作列表
            {{if $is_stock_leader || $is_xiaodao}}
                <a href="javascript:;" class="opImport btn btn-outline btn-primary btn-xs">导入</a>
                <a href="javascript:;" class="updateChangeUser btn btn-outline btn-primary btn-xs">更新状态改变用户</a>
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
    <form action="/stock/stock_action" method="get" class="form-inline">
        <div class="form-group">
            <input class="form-control" placeholder="用户手机" type="text" name="phone"
                   value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
        </div>
        <button class="btn btn-primary">查询</button>
        <span class="space"></span>
    </form>
</div>

<div class="row-divider"></div>
<div class="row">
    <p>总共{{$count}}条记录</p>
</div>
<div class="row">
    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>手机</th>
            <th>状态</th>
            <th>名字</th>

        </tr>
        </thead>
        <tbody>
        {{foreach from=$list item=item}}
            <tr>
                <td>{{$item.aPhone}}</td>
                <td>{{$item.aTypeTxt}}</td>
                <td>{{$item.name}}</td>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
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
  $('.opImport').on('click', function() {
    $('#modModal').modal('show');
  });
  /********************* 更新状态改变用户 start ******************************/
  $('.updateChangeUser').on('click', function() {
    $sls.cdt.val('');
    $('#modModal_change').modal('show');
  });
  $('#btnComfirm_change').on('click', function() {
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
  /********************* 更新状态改变用户 start ******************************/
</script>
{{include file="layouts/footer.tpl"}}