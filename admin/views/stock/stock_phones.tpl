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
		<h4>手机号列表
			<a href="javascript:;" class="add_phone_section btn btn-outline btn-primary btn-xs">添加手机号段</a>
		</h4>
	</div>
</div>
<div class="row">
	<form action="/stock/phones" method="get" class="form-inline">
		<select class="form-control" name="cat">
			<option value="">请选择来源</option>
			{{foreach from=$cats item=source key=key}}
				<option value="{{$key}}" {{if $key==$cat}}selected{{/if}}>{{$source}}</option>
			{{/foreach}}
		</select>
		<input class="form-control autoW beginDate my-date-input" placeholder="开始时间" name="sdate" value="{{$st}}">
		至
		<input class="form-control autoW endDate my-date-input" placeholder="截止时间" name="edate" value="{{$et}}">
		<button class="btn btn-primary">查询</button>
		<span class="space"></span>
	</form>
</div>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th>手机号</th>
			<th>归属地</th>
			<th>网站</th>
			<th>时间</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
			<tr>
				<td>{{$item.oOpenId}}</td>
				<td>{{$item.oUId}}</td>
				<td>{{$item.st_txt}}</td>
				<td>{{$item.oAfter}}</td>
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
						<label class="col-sm-3 control-label">手机号段</label>
						<div class="col-sm-8">
							<textarea data-field="section_phones" class="form-control" rows="10" placeholder="一行一个手机号段"></textarea>
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
	$(document).on("click", ".add_phone_section", function () {
		$sls.tag = 'add_phone_section';
		$sls.sId = '';
		$sls.title.html("添加手机号段");
		$("[data-field=section_phones]").val("");
		$sls.modal.modal('show');
	});
	$(document).on('click', '#btnSave', function () {
		var section_phones = $("[data-field=section_phones]").val();
		if (!section_phones) {
			layer.msg('号段不能为空');
			return;
		}
		var postData = {
			section_phones: section_phones,
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