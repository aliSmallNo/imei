{{include file="layouts/header.tpl"}}

<div class="row">
	<div class="col-sm-6">
		<h4>操作列表
			{{if $is_run}}<a href="javascript:;" class="opImport btn btn-outline btn-primary btn-xs">导入</a>{{/if}}
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
	<form action="/stock/stock_user" method="get" class="form-inline">
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
			<th>手机</th>
			<th>状态</th>
			<th>时间</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
		<tr>
			<td>{{$item.aPhone}}</td>
			<td>{{$item.aTypeTxt}}</td>
			<td>{{$item.aAddedOn}}</td>
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
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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

<script>
	$('.opImport').on('click', function () {
		$('#modModal').modal('show');
	});
</script>
{{include file="layouts/footer.tpl"}}