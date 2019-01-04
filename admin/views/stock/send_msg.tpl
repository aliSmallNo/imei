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
		<h4>发送短信
			<p class="left">剩余{{$leftMsgCount}}条</p>
			{{if $is_run}}<a href="javascript:;" class="opImport btn btn-outline btn-primary btn-xs">添加发送</a>{{/if}}
		</h4>
	</div>
</div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th class="col-sm-6">发送内容</th>
			<th>发送状态</th>
			<th>发送条数</th>
			<th>下载表格</th>
			<th>发送者</th>
			<th>时间</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
			<tr>
				<td>{{$item.oAfter}}</td>
				<td>{{$item.st_txt}}</td>
				<td>{{$item.oOpenId}}</td>
				<td><a href="{{$item.url}}">下载表格</a></td>
				<td>{{$item.aName}}</td>
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
				<h4 class="modal-title" id="myModalLabel">发送短信</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal" action="/stock/upload_excel" method="post" enctype="multipart/form-data">
					<input type="hidden" name="cat" value="send_msg"/>
					<input type="hidden" name="sign" value="up"/>
					<div class="form-group">
						<label class="col-sm-3 control-label">发送内容</label>
						<div class="col-sm-8">
							<textarea name="content" rows="3" class="form-control"></textarea>
						</div>
					</div>
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

<script>
	$('.opImport').on('click', function () {
		$('#modModal').modal('show');
	});
</script>
{{include file="layouts/footer.tpl"}}