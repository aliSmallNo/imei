{{include file="layouts/header.tpl"}}
<style>
	.left {
		display: inline-block;
		font-size: 12px;
		font-weight: 400;
		color: #777;
	}
</style>
<div class="row">
	<div class="col-sm-6">
		<h4>准点买注册链接
			<a class="btn btn-primary btn-xs add_zdm_link">添加链接</a>
		</h4>
	</div>
</div>
<div class="row">
	<form action="/stock/zdm_reg_link" method="get" class="form-inline">
		<input class="form-control autoW " placeholder="手机号" name="phone" value="{{$phone}}">
		<button class="btn btn-primary">查询</button>
		<span class="space"></span>
	</form>
</div>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th>链接</th>
			<th>手机号</th>
			<th>时间</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
			<tr>
				<td>{{$item.oBefore}}</td>
				<td>{{$item.oOpenId}}</td>
				<td>{{$item.oDate}}</td>
			</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>
<div class="row-divider"></div>
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
						<label class="col-sm-3 control-label">手机号</label>
						<div class="col-sm-8">
							<input type="text" maxlength="11" data-field="spread_phone" class="form-control" placeholder="填写手机号">
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
		modal: $("#modModal"),
		title: $("#modModal").find(".modal-header h4"),
	};
	$(document).on("click", ".add_zdm_link", function () {
		$sls.tag = 'add_zdm_link';
		$sls.sId = '';
		$sls.title.html("填写传播者手机号");
		$("[data-field=spread_phone]").val("");
		$sls.modal.modal('show');
	});
	$(document).on('click', '#btnSave', function () {
		var spread_phone = $("[data-field=spread_phone]").val();
		if (!spread_phone) {
			layer.msg('手机号不能为空');
			return;
		}
		var postData = {
			spread_phone: spread_phone,
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
				}, 5000)
			} else {
				layer.msg(resp.msg);
			}
		}, "json");
	})
</script>
{{include file="layouts/footer.tpl"}}