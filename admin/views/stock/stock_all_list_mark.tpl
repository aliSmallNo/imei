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
    <h4>标记股票
      <a class="add_rule btn btn-xs btn-primary">添加标记股票</a>
    </h4>
  </div>
</div>
<div class="row">
  <form action="/stock/stock_all_list_mark" method="get" class="form-inline">
    <div class="form-group">
      <input type="text" name="stock_id" class="form-control" placeholder="策略名称" value="{{$stock_id}}">
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
      <th>#</th>
      <th>股票名称</th>
      <th>股票代码</th>
      <th>标记</th>
      <th>说明</th>
      <th>时间</th>
      <th>操作</th>
    </tr>
    </thead>
    <tbody>

    {{foreach from=$list item=item key=key}}
      <tr>
        <td>{{$key+1}}</td>
        <td>{{$item.mStockName}}</td>
        <td>{{$item.m_cat_t}}-{{$item.m_cat_c}}</td>
        <td>{{$item.m_desc}}</td>
        <td>{{$item.m_updated_on}}</td>
        <td data-id="{{$item.m_id}}" data-m_stock_id="{{$item.m_stock_id}}" data-m_cat="{{$item.m_cat}}"
            data-m_desc="{{$item.m_desc}}">
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
      <label class="col-sm-4 control-label">类型:</label>
      <div class="col-sm-7">
        <select class="form-control m_cat">
          <option value="">-=请选择=-</option>
          {{foreach from=$cats item=day key=key}}
            <option value="{{$key}}">{{$day}}</option>
          {{/foreach}}
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">股票代码:</label>
      <div class="col-sm-7">
        <input type="text" class="form-control m_stock_id">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">说明:</label>
      <div class="col-sm-7">
        <textarea class="form-control m_desc"></textarea>
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
        $('#myModalLabel').html('添加标记');
        $('.btnSaveMod').attr({
            tag: "edit_stat2_mark",
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
            case "edit_stat2_mark":
                postData = {
                    tag: tag,
                    m_cat: $.trim($('.m_cat').val()),
                    m_stock_id: $.trim($('.m_stock_id').val()),
                    m_desc: $.trim($('.m_desc').val()),
                    id: self.attr("id")
                };
                console.log(postData);
                if (!postData["m_cat"]
                    || !postData["m_stock_id"]
                ) {
                    layer.msg("必填项不能为空！");
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
            tag: "edit_stat2_mark",
            id: td.attr("data-id")
        });

        $('.m_cat').val(td.attr("data-m_cat"));
        $('.m_stock_id').val(td.attr("data-m_stock_id"));
        $('.m_desc').val(td.attr("data-m_desc"));

        $('#modModal').modal('show');


    });


</script>
{{include file="layouts/footer.tpl"}}