{{include file="layouts/header.tpl"}}
<style>
  .alert {
    padding-top: 5px;
    padding-bottom: 5px;
    margin-bottom: 10px;
  }

  .text-muted {
    color: #999;
    font-size: 13px;
  }

  .c-ic {
    display: inline-block;
    width: 17px;
    text-align: center;
    color: #888;
  }

  .w-progressBar {
    padding-right: 15px;
  }

  .w-progressBar .txt {
    margin-bottom: 3px;
    font-size: 12px;
    color: #999;
  }

  .w-progressBar .txt strong {
    color: #f80;
  }

  .w-progressBar .wrap {
    position: relative;
    margin-bottom: 10px;
    height: 5px;
    border-radius: 5px;
    background-color: #E4E4E4;
    overflow: hidden;
  }

  .w-progressBar .bar {
    overflow: hidden;
  }

  .w-progressBar .bar, .w-progressBar .color {
    display: block;
    height: 100%;
    border-radius: 4px;
  }

  .w-progressBar .color {
    width: 100%;
    background: #2a8;
    background: -webkit-gradient(linear, left top, right top, from(#8c5), to(#208850));
    background: -moz-linear-gradient(left, #8c5, #208850);
    background: -o-linear-gradient(left, #8c5, #208850);
    background: -ms-linear-gradient(left, #8c5, #208850);
  }

  th a {
    padding-left: 6px;
    padding-right: 6px;
    font-size: 12px;
    color: #999;
    font-weight: normal;
  }

  th a.active {
    color: #f40;
  }

  th a:hover {
    text-decoration: none;
  }

  td.cell-act a {
    margin-bottom: 3px;
  }

  input.form-control[type=text] {
    width: 10em;
  }
</style>
<div class="row">
  <h4>严选师线索
  </h4>
</div>
<div class="row">
  <form method="get" class="form-inline" action="/crm/clients">
    <input name="cat" type="hidden" value="{{$cat}}">
    <input class="my-date-input form-control" name="dt1" placeholder="注册日期 From" type="text" value="{{$dt1}}">
    <input class="my-date-input form-control" name="dt2" placeholder="注册日期 To" type="text" value="{{$dt2}}">
    <input class="form-control" name="prov" placeholder="严选师省市" type="text" value="{{$prov}}">
    <input class="form-control" name="name" placeholder="严选师姓名" type="text" value="{{$name}}">
    <input class="form-control" name="phone" placeholder="严选师手机号" type="text" value="{{$phone}}">
    <select class="form-control" name="bdassign">
      <option value="">请选择BD</option>
      {{foreach from=$bds item=bd}}
        <option value="{{$bd.id}}" {{if $bd.id==$bdassign}}selected{{/if}}>{{$bd.name}}</option>
      {{/foreach}}
    </select>
    <button type="submit" class="btn btn-primary">查询</button>
    <a class="addClue btn btn-primary">添加线索</a>
  </form>
</div>
<div class="row-divider"></div>
{{if $alertMsg}}
  <div class="row">
    <div class="col-lg-7">
      <div class=" alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        {{$alertMsg}}
      </div>
    </div>
  </div>
{{/if}}
<div class="row">
  <ul class="nav nav-tabs">
    {{foreach from=$tabs key=key item=tab}}
      <li class="ng-scope {{if $cat== $key}} active{{/if}}">
        <a href="/crm/clients?cat={{$key}}&sort={{$sort}}&{{$urlParams}}"
           class="ng-binding">{{$tab.title}}{{if $tab.count > 0}}
            <span class="badge">{{$tab.count}}</span>{{/if}}
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
      <th>地区</th>
      <th style="width: 185px">
        姓名/手机/微信
      </th>
      <th class="col-lg-3">
        严选师自述
      </th>
      <th>
        BD负责人
      </th>
      <th class="col-lg-4">
        最新跟进
        <a {{if $sort=='dd' || $sort=='da'}}class="active"{{/if}}
           href="/crm/clients?cat={{$cat}}&sort={{$dNext}}&{{$urlParams}}">跟进日期 <i
                  class="fa {{$dIcon}}"></i></a>
        <a {{if $sort=='sd' || $sort=='sa'}}class="active"{{/if}}
           href="/crm/clients?cat={{$cat}}&sort={{$sNext}}&{{$urlParams}}">跟进进度 <i
                  class="fa {{$sIcon}}"></i></a>
      </th>
      <th>
        操作
      </th>
    </tr>
    </thead>
    <tbody>
    {{foreach from=$items item=prod}}
      <tr>
        <td>
          {{$prod.cProvince}} - {{$prod.cCity}}
        </td>
        <td>
          {{$prod.cName}}
          <div><i class="c-ic fa fa-phone-square"></i> {{$prod.cPhone}}</div>
          <div><i class="c-ic fa fa-wechat"></i> {{$prod.cWechat}}</div>
          <div>性别:{{$prod.genderText}};年龄:{{$prod.ageText}};职业:{{$prod.cJob}};</div>
        </td>
        <td>
          {{if $prod.cIntro}}{{$prod.cIntro}}{{else}}<span class="text-muted">（无）</span>{{/if}}
          <div class="text-muted">{{$prod.addedDate}}<br>来源：{{$prod.src}}</div>
        </td>

        <td>
          {{if $prod.bdName}}
            {{$prod.bdName}}
            <div class="text-muted">{{$prod.assignDate}}</div>
          {{/if}}
        </td>
        <td>
          <div class="w-progressBar">
            <p class="txt">{{$prod.statusText}} <strong>{{$prod.percent}}%</strong></p>
            <p class="wrap">
              <span class="bar" style="width:{{$prod.percent}}%;"><i class="color"></i></span>
            </p>
          </div>
          {{if isset($prod.lastNote) && $prod.lastNote}}
            {{$prod.lastNote}}
            <br>
            <div class="text-muted">{{$prod.lastDate}}</div>
          {{/if}}

        </td>
        <td class="cell-act" data-id="{{$prod.cId}}">
          <a href="/crm/detail?id={{$prod.cId}}"
             class="btnDetail btn btn-outline btn-primary btn-xs">跟进详情</a>
          {{if $cat=="sea" && $prod.cBDAssign==0}}
            <a href="javascript:;" class="btnGrab btn btn-outline btn-success btn-xs">我来跟进</a>
          {{/if}}
          {{if $isAssigner}}
            <a href="javascript:;" class="btnModify btn btn-outline btn-danger btn-xs">修改信息</a>
          {{/if}}
          {{if $cat=="my"}}
            <a href="javascript:;" class="btnChange btn btn-outline btn-info btn-xs">转给他人</a>
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
        <h4 class="modal-title" id="myModalLabel">分配BD信息</h4>
      </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="button" class="btn btn-danger" id="btnRemove">删除线索</button>
        <button type="button" class="btn btn-primary" id="btnSaveMod">确定保存</button>
      </div>
    </div>
  </div>
</div>
<script type="text/html" id="tpl_change">
  <div class="form-horizontal">
    <div class="form-group">
      <label class="col-sm-4 control-label">严选师/电话:</label>
      <div class="col-sm-7 form-control-static">
        <span class="client_name"></span> <span class="client_phone"></span>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">所属城市:</label>
      <div class="col-sm-7 form-control-static">
        <span class="client_prov"></span> <span class="client_city"></span>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">转移给:</label>
      <div class="col-sm-7">
        <select class="form-control clue_bd">
          <option value="0">放入公海</option>
          {{foreach from=$staff item=bd}}
            <option value="{{$bd.id}}" {{if $bd.id==$bdDefault}}selected{{/if}}>{{$bd.name}}</option>
          {{/foreach}}
        </select>
        <input type="hidden" id="client_status">
      </div>
    </div>
  </div>
</script>
<script type="text/html" id="cClueTmp">
  <div class="form-horizontal">
    <div class="form-group">
      <label class="col-sm-4 control-label">BD分派:</label>
      <div class="col-sm-7">
        <select class="form-control clue_bd">
          <option value="0">放入公海</option>
          {{foreach from=$staff item=bd}}
            <option value="{{$bd.id}}" {{if $bd.id==$bdDefault}}selected{{/if}}>{{$bd.name}}</option>
          {{/foreach}}
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">严选师来源:</label>
      <div class="col-sm-7">
        <select class="form-control clue_src">
          {{foreach from=$sources key=k item=source}}
            <option value="{{$k}}">{{$source}}</option>
          {{/foreach}}
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">严选师姓名:</label>
      <div class="col-sm-7">
        <input type="text" class="form-control clue_name">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">联系电话:</label>
      <div class="col-sm-7">
        <input type="text" class="form-control clue_phone">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">微信号:</label>
      <div class="col-sm-7">
        <input type="text" class="form-control clue_wechat">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">严选师性别:</label>
      <div class="col-sm-7">
        <select class="form-control clue_gender">
          <option value="">-=请选择=-</option>
          <option value="10">女</option>
          <option value="11">男</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">严选师年龄:</label>
      <div class="col-sm-7">
        <select class="form-control clue_age">
          <option value="">-=请选择=-</option>
          {{foreach from=$ageMap item=age key=key}}
            <option value="{{$key}}">{{$age}}</option>
          {{/foreach}}
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">严选师职业:</label>
      <div class="col-sm-7">
        <input type="text" class="form-control clue_job">
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">所属省份:</label>
      <div class="col-sm-7">
        <select class="form-control clue_province">
          <option></option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">所属城市:</label>
      <div class="col-sm-7">
        <select class="form-control clue_city">
          <option></option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">严选师自述:</label>
      <div class="col-sm-7">
        <textarea class="form-control clue_note"></textarea>
      </div>
    </div>
  </div>
</script>
<script type="text/html" id="jsonItems">
  {{$strItems}}
</script>
<script src="/js/clue_areas.js?v={{#gVersion#}}"></script>
<script>
    $(document).on("click", "button.close", function () {
        var form = $("form");
        form.find(".form-control").val("");
        form.submit();
    });
    $(document).on('click', '#btnRemove', function () {
        var self = $(this);
        var cid = self.attr('cid');
        layer.confirm('是否确定要删除这个严选师线索？', {
            btn: ['确定', '取消'],
            title: '删除严选师线索'
        }, function () {
            removeClient(cid);
        }, function () {
        });
    });

    function removeClient(cid) {
        layer.load();
        $.post("/api/client", {
            tag: "remove",
            id: cid
        }, function (resp) {
            layer.closeAll();
            layer.msg(resp.msg);
            setTimeout(function () {
                location.reload();
            }, 400);
        }, 'json');
    }

    $(document).on('click', '#btnSaveMod', function () {
        var self = $(this);
        var tag = self.attr('tag');
        var postData = null;
        var url = '/api/client';
        console.log(tag);
        switch (tag) {
            case "change":
                postData = {
                    tag: tag,
                    bd: $('.clue_bd').val(),
                    status: $('#client_status').val(),
                    id: self.attr("cid")
                };
                break;
            case "edit":
                postData = {
                    tag: tag,
                    name: $.trim($('.clue_name').val()),
                    phone: $.trim($('.clue_phone').val()),
                    wechat: $.trim($('.clue_wechat').val()),
                    prov: $.trim($('.clue_province').val()),
                    city: $.trim($('.clue_city').val()),
                    note: $.trim($('.clue_note').val()),
                    age: $.trim($('.clue_age').val()),
                    gender: $.trim($('.clue_gender').val()),
                    job: $.trim($('.clue_job').val()),
                    bd: $('.clue_bd').val(),
                    src: $('.clue_src').val(),
                    id: self.attr("cid")
                };
                console.log(postData);
                if (!postData["name"] || !postData["wechat"]) {
                    layer.msg("严选师姓名和微信号不能为空！");
                    return;
                }
                url = '/api/client';
                break;
        }
        if (postData) {
            layer.load();
            $.post(url, postData, function (resp) {
                layer.closeAll();
                layer.msg(resp.msg);
                setTimeout(function () {
                    location.reload();
                }, (resp.code == 0 ? 400 : 800));
            }, 'json');
        }

    });

    $(document).on("click", ".btnGrab", function () {
        var self = $(this);
        var cid = self.closest("td").attr("data-id");
        $.post("/api/client", {
            tag: "grab",
            id: cid
        }, function (resp) {
            layer.msg(resp.msg);
            setTimeout(function () {
                location.reload();
            }, 800);
        }, "json")
    });

    var mItems = JSON.parse($("#jsonItems").html());
    $(document).on('click', '.btnModify', function () {
        var self = $(this);
        var cid = self.closest("td").attr("data-id");
        var client = null;
        for (var k in mItems) {
            if (mItems[k].cId == cid) {
                client = mItems[k];
                break;
            }
        }
        console.log(client);
        if (!client) {
            return;
        }
        var vHtml = $('#cClueTmp').html();
        $('div.modal-body').html(vHtml);
        $('#myModalLabel').html('修改严选师线索');
        $('#btnSaveMod').attr({
            tag: "edit",
            cid: cid
        });
        $('#btnRemove').attr({
            tag: "remove",
            cid: cid
        });
        $('#btnRemove').show();
        $('#modModal').modal('show');
        $('.clue_name').val(client.cName);
        $('.clue_phone').val(client.cPhone);
        $('.clue_wechat').val(client.cWechat);
        $('.clue_note').val(client.cIntro);
        $('.clue_bd').val(client.cBDAssign);
        $('.clue_src').val(client.cSource);
        $('.clue_province').val(client.cProvince);
        $('.clue_age').val(client.cAge);
        $('.clue_gender').val(client.cGender);
        $('.clue_job').val(client.cJob);
        updateArea(client.cProvince);
        $('.clue_city').val(client.cCity);
    });

    $(document).on('click', '.btnChange', function () {
        var self = $(this);
        var cid = self.closest("td").attr("data-id");
        var client = null;
        for (var k in mItems) {
            if (mItems[k].cId == cid) {
                client = mItems[k];
                break;
            }
        }
        console.log(client);
        if (!client) {
            return;
        }
        var vHtml = $('#tpl_change').html();
        $('div.modal-body').html(vHtml);
        $('#myModalLabel').html('转严选师给他人');
        $('#btnSaveMod').attr({
            tag: "change",
            cid: cid
        });
        $('#btnRemove').hide();
        $('#modModal').modal('show');
        $('.client_name').html(client.cName);
        $('.client_phone').html(client.cPhone);
        $('.client_prov').html(client.cProvince);
        $('.client_city').html(client.cCity);
        $('#client_status').html(client.cStatus);
        $('.clue_bd').val(client.cBDAssign);
    });

    $(document).on('click', '.addClue', function () {
        var vHtml = $('#cClueTmp').html();
        $('div.modal-body').html(vHtml);
        $('#myModalLabel').html('添加线索');
        $('#btnSaveMod').attr({
            tag: "edit",
            cid: ""
        });
        $('#btnRemove').hide();
        $('#modModal').modal('show');
        updateArea("北京市");
    });

    $(document).on('change', '.clue_province', function () {
        updateArea($(this).val());
    });
</script>
{{include file="layouts/footer.tpl"}}