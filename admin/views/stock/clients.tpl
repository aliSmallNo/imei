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
		background: -webkit-gradient(linear, left top, right top, from(#fb597a), to(#e2002e));
		background: -moz-linear-gradient(left, #fb597a, #e2002e);
		background: -o-linear-gradient(left, #fb597a, #e2002e);
		background: -ms-linear-gradient(left, #fb597a, #e2002e);
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

	.action_1, .action_9 {
		font-size: 12px;
		color: #f0ad4e;
	}

	.action_1 {
		color: red;
	}
</style>
<div class="row">
	<div class="col-sm-6">
		<h4>客户线索
			<a class="addClue btn btn-xs btn-primary">添加线索</a>
			{{if $isAssigner}}
				<a class="addClueMore btn btn-xs btn-primary">批量导入线索</a>
				<a class="choose_lose_client_btn btn btn-xs btn-primary">导入到无意向客户</a>
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
	<form method="get" class="form-inline" action="/stock/clients">
		<input name="cat" type="hidden" value="{{$cat}}">
		<input class="my-date-input form-control" name="dt1" placeholder="注册日期 From" type="text" value="{{$dt1}}">
		<input class="my-date-input form-control" name="dt2" placeholder="注册日期 To" type="text" value="{{$dt2}}">
		<input class="form-control" name="prov" placeholder="客户省市" type="text" value="{{$prov}}">
		<input class="form-control" name="name" placeholder="客户姓名" type="text" value="{{$name}}">
		<input class="form-control" name="phone" placeholder="客户手机号" type="text" value="{{$phone}}">
		{{if $isAssigner}}
			<select class="form-control" name="bdassign">
				<option value="">请选择BD</option>
				{{foreach from=$bds item=bd}}
					<option value="{{$bd.id}}" {{if $bd.id==$bdassign}}selected{{/if}}>{{$bd.name}}</option>
				{{/foreach}}
			</select>
			<select class="form-control" name="action">
				<option value="">请选择操作</option>
				{{foreach from=$actionDict item=act key=key}}
					<option value="{{$key}}" {{if $key==$action}}selected{{/if}}>{{$act}}</option>
				{{/foreach}}
			</select>
		{{/if}}
		<select class="form-control" name="src">
			<option value="">请选择来源</option>
			{{foreach from=$SourceMap item=source key=key}}
				<option value="{{$key}}" {{if $key==$src}}selected{{/if}}>{{$source}}</option>
			{{/foreach}}
		</select>
		<button type="submit" class="btn btn-primary">查询</button>

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
			{{if $key=='sea'}}
				{{if $is_saler}}
					<li class="ng-scope {{if $cat== $key}} active{{/if}}">
						<a href="/stock/clients?cat={{$key}}&sort={{$sort}}&{{$urlParams}}"
							 class="ng-binding">{{$tab.title}}{{if $tab.count > 0}}
								<span class="badge">{{$tab.count}}</span>{{/if}}
						</a>
					</li>
				{{/if}}
			{{else}}
				<li class="ng-scope {{if $cat== $key}} active{{/if}}">
					<a href="/stock/clients?cat={{$key}}&sort={{$sort}}&{{$urlParams}}"
						 class="ng-binding">{{$tab.title}}{{if $tab.count > 0}}
							<span class="badge">{{$tab.count}}</span>{{/if}}
					</a>
				</li>
			{{/if}}

		{{/foreach}}
	</ul>
</div>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered table-hover">
		<thead>
		<tr>
			{{if $cat=="sea"}}
				<th>选择</th>
			{{/if}}
			<th class="col-sm-1">地区</th>
			<th style="width: 185px">
				姓名/手机/微信
			</th>
			<th class="col-lg-3">
				客户自述
			</th>
			<th>
				BD负责人
			</th>
			<th class="col-lg-4">
				最新跟进
				<a {{if $sort=='dd' || $sort=='da'}}class="active"{{/if}}
					 href="/stock/clients?cat={{$cat}}&sort={{$dNext}}&{{$urlParams}}">跟进日期 <i class="fa {{$dIcon}}"></i></a>
				<a {{if $sort=='sd' || $sort=='sa'}}class="active"{{/if}}
					 href="/stock/clients?cat={{$cat}}&sort={{$sNext}}&{{$urlParams}}">跟进进度 <i class="fa {{$sIcon}}"></i></a>
			</th>
			<th>
				操作
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$items item=prod}}
			<tr>
				{{if $cat=="sea"}}
					<td>
						<input type="checkbox" data_cid="{{$prod.cId}}" class="choose_lose_client">
					</td>
				{{/if}}
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
					<div class="action_{{$prod.cStockAction}}">
						{{$prod.action_t}} {{if $prod.cStockActionDate}}({{$prod.cStockActionDate}}){{/if}}
					</div>

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
					<a href="/stock/detail?id={{$prod.cId}}" class="btnDetail btn btn-outline btn-primary btn-xs">跟进详情</a>
					{{if $cat!="lose"}}
						{{if $cat=="sea" && $prod.cBDAssign==0}}
							<a href="javascript:;" class="btnGrab btn btn-outline btn-success btn-xs">我来跟进</a>
						{{/if}}
						{{if $isAssigner}}{{/if}}
						<a href="javascript:;" class="btnModify btn btn-outline btn-danger btn-xs">修改信息</a>
						{{if $cat=="my" && !$sub_staff}}
							<a href="javascript:;" class="btnChange btn btn-outline btn-info btn-xs">转给他人</a>
						{{/if}}
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
			<label class="col-sm-4 control-label">客户/电话:</label>
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
					{{foreach from=$bds item=bd}}
						<option value="{{$bd.id}}" {{if $bd.id==$bdassign}}selected{{/if}}>{{$bd.name}}</option>
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
			<label class="col-sm-4 control-label">客户来源:</label>
			<div class="col-sm-7">
				<select class="form-control clue_src">
					{{foreach from=$sources key=k item=source}}
						<option value="{{$k}}">{{$source}}</option>
					{{/foreach}}
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">客户姓名:</label>
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
			<label class="col-sm-4 control-label">客户性别:</label>
			<div class="col-sm-7">
				<select class="form-control clue_gender">
					<option value="">-=请选择=-</option>
					<option value="10">女</option>
					<option value="11">男</option>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">客户年龄:</label>
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
			<label class="col-sm-4 control-label">炒股时长:</label>
			<div class="col-sm-7">
				<select class="form-control clue_stock_age">
					<option value="">-=请选择=-</option>
					{{foreach from=$stock_age_map item=stock_age key=key}}
						<option value="{{$key}}">{{$stock_age}}</option>
					{{/foreach}}
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">客户职业:</label>
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
			<label class="col-sm-4 control-label">客户自述:</label>
			<div class="col-sm-7">
				<textarea class="form-control clue_note"></textarea>
			</div>
		</div>
	</div>
</script>

<div class="modal fade" id="addClueMoreModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">批量导入客户线索</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal" action="/stock/upload_excel" method="post" enctype="multipart/form-data">
					<input type="hidden" name="cat" value="add_clues"/>
					<input type="hidden" name="sign" value="up"/>
					<div class="form-group">
						<label class="col-sm-3 control-label">Excel文件</label>
						<div class="col-sm-8">
							<input type="file" name="excel" accept=".xls,.xlsx" class="form-control-static"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label"></label>
						<div class="col-sm-8">
							<input type="submit" class="btn btn-primary" id="btnUpload" value="发送"/>
						</div>
					</div>

				</form>
			</div>
		</div>
	</div>
</div>

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
		layer.confirm('是否确定要删除这个客户线索？', {
			btn: ['确定', '取消'],
			title: '删除客户线索'
		}, function () {
			removeClient(cid);
		}, function () {
		});
	});

	function removeClient(cid) {
		layer.load();
		$.post("/api/stock_client", {
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
		var url = '/api/stock_client';
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
					stock_age: $.trim($('.clue_stock_age').val()),
					gender: $.trim($('.clue_gender').val()),
					job: $.trim($('.clue_job').val()),
					bd: $('.clue_bd').val(),
					src: $('.clue_src').val(),
					id: self.attr("cid")
				};
				console.log(postData);
				if (!postData["name"]
					|| !postData["src"]
					|| !postData["prov"]
					|| !postData["city"]
					|| !postData["stock_age"]
				) {
					layer.msg("客户姓名、来源、省、市、炒股时长不能为空！");
					return;
				}
				url = '/api/stock_client';
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

	$(document).on("click", ".btnGrab", function () {
		var self = $(this);
		var cid = self.closest("td").attr("data-id");
		$.post("/api/stock_client", {
			tag: "grab",
			id: cid
		}, function (resp) {
			layer.msg(resp.msg);
			setTimeout(function () {
				location.reload();
			}, 800);
		}, "json")
	});

	var mItems = [];
	// var jsonItems = $("#jsonItems").html();
	// jsonItems = jsonItems.replace(/\s*/g, '');
	// console.log(jsonItems,jsonItems.length);
	// if (jsonItems.length) {
	// 	mItems = JSON.parse(jsonItems);
	// }
	try {
		mItems = JSON.parse($("#jsonItems").html());
	} catch (err) {
		mItems = [];
	}

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
		$('#myModalLabel').html('修改客户线索');
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
		$('.clue_stock_age').val(client.cStockAge);
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
		$('#myModalLabel').html('转客户给他人');
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

	$(document).on('click', '.addClueMore', function () {
		$('#addClueMoreModal').modal('show');
	});

	$(function () {
		if (!$("input[name=phone]").val()) {
			$.post("/api/stock_client", {
				tag: 'user_alert'
			}, function (resp) {
				if (resp.code == 0) {
					var temp = "<ol class='users'>{[#data]}<li class=''>{[cName]}: <a href='javascript:;' class='update_alert' alert-phone='{[cPhone]}' alert-oId='{[oId]}'>{[cPhone]}</a></li>{[/data]}</ol>";
					layer.open({
						content: Mustache.render(temp, resp),
						area: ['400px', '500px'],
						title: "用户准点买操作后、系统更新用户的状态"
					});
				}
			}, 'json');
		}

		$(document).on("click", ".update_alert", function () {
			var self = $(this);
			var phone = self.attr('alert-phone');
			layer.load();
			$.post("/api/stock_client", {
				tag: 'update_user_alert',
				oid: self.attr('alert-oId'),
			}, function (resp) {
				layer.closeAll();
				if (resp.code == 0) {
					location.href = "/stock/clients?phone=" + phone;
				}
			}, 'json');
		})
	})

	function get_lose_client() {
		var data = [];
		$(".choose_lose_client").each(function () {
			var cid = $(this).attr('data_cid');
			if ($(this).is(':checked')) {
				data.push(cid);
			}
		});
		if (data.length == 0) {
			layer.msg("请先选择客户");
			return false;
		}
		return data;
	}

	$(".choose_lose_client_btn").on("click", function () {
		var data = get_lose_client();
		if (!data) {
			return false;
		}
		console.log(data);
		console.log(JSON.stringify(data));
		layer.load();
		$.post("/api/stock_client", {
			tag: 'choose_2_lose_client',
			data: JSON.stringify(data)
		}, function (resp) {
			layer.closeAll();
			if (resp.code == 0) {
				//location.reload();
			} else {
				layer.msg(resp.msg);
			}
		}, 'json');
	})
</script>
{{include file="layouts/footer.tpl"}}