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
	<h4>配资CRM客户来源
		<a href="javascript:;" class="add_user btn btn-outline btn-primary btn-xs">添加来源</a>
	</h4>
</div>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th>字段</th>
			<th>字段名称</th>
			<th>状态</th>
			<th>时间</th>
			<th>操作</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
			<tr data-sStatus="{{$item.sStatus}}" data-sName="{{$item.sName}}" data-sTxt="{{$item.sTxt}}" data-sId="{{$item.sId}}">
				<td>{{$item.sName}}</td>
				<td>{{$item.sTxt}}</td>
				<td>{{$item.st_txt}}</td>
				<td>{{$item.sUpdatedOn}}</td>
				<td>
					<a href="javascript:;" class="edit_user btn btn-outline btn-primary btn-xs">修改来源</a>
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
						<label class="col-sm-3 control-label">字段</label>
						<div class="col-sm-8">
							<input type="text" data-field="sName" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">字段名称</label>
						<div class="col-sm-8">
							<input type="text" data-field="sTxt" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">状态</label>
						<div class="col-sm-8">
							<select class="form-control" data-field="sStatus">
								{{foreach from=$sts key=key item=item}}
									<option value="{{$key}}">{{$item}}</option>
								{{/foreach}}
							</select>
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
		sId: '',
		modal: $("#modModal"),
		title: $("#modModal").find(".modal-header h4"),
	};

	$(document).on("click", ".add_user", function () {
		$sls.tag = 'edit_source';
		$sls.sId = '';
		$sls.title.html("添加来源信息");
		$("[data-field]").each(function () {
			if ($(this).attr('data-field') == 'sStatus') {
				$(this).val(1);
			} else {
				$(this).val('');
			}
		});
		$sls.modal.modal('show');
	});

	$(document).on("click", ".edit_user", function () {
		var self = $(this).closest("tr");
		$sls.tag = 'edit_source';
		$sls.sId = self.attr('data-sId');
		$sls.title.html("修改来源信息");
		$("[data-field=sStatus]").val(self.attr('data-sStatus'));
		$("[data-field=sName]").val(self.attr('data-sName'));
		$("[data-field=sTxt]").val(self.attr('data-sTxt'));
		$sls.modal.modal('show');
	});

	$(document).on('click', '#btnSave', function () {
		var sStatus = $("[data-field=sStatus]").val();
		var sName = $("[data-field=sName]").val();
		var sTxt = $("[data-field=sTxt]").val();
		if (!sName) {
			layer.msg('字段不能为空');
			return;
		}
		if (!sTxt) {
			layer.msg('字段名称不能为空');
			return;
		}
		if (!sStatus) {
			layer.msg('状态不能为空');
			return;
		}
		var postData = {
			sStatus: sStatus,
			sTxt: sTxt,
			sName: sName,
			sId: $sls.sId,
			tag: $sls.tag,
		};
		console.log(postData);

		if ($sls.loadflag) {
			return;
		}
		$sls.loadflag = 1;
		layer.load();
		$.post("/api/stock", postData, function (resp) {
			$sls.loadflag = 0;
			layer.closeAll();
			if (resp.code == 0) {
				layer.msg(resp.msg);
				setTimeout(function () {
					location.reload();
				}, 1500)
			} else {
				layer.msg(resp.msg);
			}
		}, "json");
	})
</script>
{{include file="layouts/footer.tpl"}}