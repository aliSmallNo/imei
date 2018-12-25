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
		<h4>批量发送短信
			<p class="left">剩余{{$leftMsgCount}}条</p>
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
				<p class="help-block">每次最好不要超过5000条</p>
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

<div class="row-divider"></div>

<script>

</script>
{{include file="layouts/footer.tpl"}}