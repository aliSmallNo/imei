{{include file="layouts/header.tpl"}}

<div class="row">
	<div class="col-sm-6">
		<h4>订单列表
			{{if $is_stock_leader}}
				<a href="javascript:;" class="opImport btn btn-outline btn-primary btn-xs">导入</a>
				<a href="javascript:;" class="opDelete btn btn-outline btn-danger btn-xs">删除</a>
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
	<form action="/stock/stock_order" method="get" class="form-inline">
		<div class="form-group">
			<input class="form-control" placeholder="用户名" type="text" name="name"
						 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
			<input class="form-control" placeholder="用户手机" type="text" name="phone"
						 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
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
			{{if $is_staff}}
				<th>ID</th>
			{{/if}}
			<th>用户名</th>
			<th>手机</th>
			<th>股票代码</th>
			<th>股数</th>
			<th>初期借款</th>
			<th>时间</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
			<tr>
				{{if $is_staff}}
					<td>{{$item.oId}}</td>{{/if}}
				<td>{{$item.oName}}</td>
				<td>{{$item.oPhone}}</td>
				<td>{{$item.oStockId}}</td>
				<td>{{$item.oStockAmt}}</td>
				<td>{{$item.oLoan}}</td>
				<td>{{$item.dt}}</td>
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
				<h4 class="modal-title" id="myModalLabel">上传订单汇总数据Excel</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal" action="/stock/upload_excel" method="post" enctype="multipart/form-data">
					<input type="hidden" name="cat" value="order"/>
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
<div class="modal fade" id="modModal_d" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">删除指定日期的订单</h4>
			</div>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label">删除订单的日期</label>
						<div class="col-sm-8">
							<input type="text" data-field="dt" class="form-control my-date-input"/>
						</div>
					</div>

				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-primary" id="btnComfirm">确定</button>
			</div>
		</div>
	</div>
</div>

<script>
	$sls = {
		dt: $("[data-field=dt]"),

	};
	$('.opImport').on('click', function () {
		$('#modModal').modal('show');
	});
	$('.opDelete').on('click', function () {
		$sls.dt.val('');
		$('#modModal_d').modal('show');
	});
	$('#btnComfirm').on('click', function () {
		var dt = $sls.dt.val();
		if (!dt) {
			layer.msg('还没填写日期哦');
			return;
		}
		if ($sls.load_flag) {
			return;
		}
		layer.load();
		$sls.load_flag = 1;
		$.post("/api/stock", {
			tag: 'delete_stock_order',
			dt: dt,
		}, function (resp) {
			layer.closeAll();
			$sls.load_flag = 0;
			layer.msg(resp.msg);
			if (resp.code == 0) {
				setTimeout(function () {
					location.reload();
				}, 2000);
			}
		}, 'json');
	});


</script>
{{include file="layouts/footer.tpl"}}