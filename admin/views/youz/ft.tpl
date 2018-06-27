{{include file="layouts/header.tpl"}}
<style>
	.font12 {
		font-size: 12px;
	}

	.font10 {
		font-size: 10px;
		color: #0d5ccf;
	}
</style>
<div class="row">
	<h4>申请更换上级严选师列表</h4>
</div>
<div class="row">
	<form action="/youz/ft" method="get" class="form-inline">

		<div class="form-group">
			<input class="form-control" placeholder="用户名称" type="text" name="name"
						 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
			<input class="form-control" placeholder="用户手机" type="text" name="phone"
						 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
			<select class="form-control" name="st">
				<option value="">-=请选择=-</option>
				{{foreach from=$stDict item=item key=key}}
					<option value="{{$key}}">{{$item}}</option>
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
			<th class="col-sm-1">
				待添加上级的用户头像
			</th>
			<th class="col-sm-2">
				待添加上级的用户信息
			</th>
			<th class="col-sm-1">
				上级用户头像
			</th>
			<th class="col-sm-2">
				上级的用户信息
			</th>
			<th class="col-sm-1">
				状态
			</th>
			<th class="col-sm-3">
				时间
			</th>
			<th>
				操作
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$items item=item}}
			<tr data-fid="{{$item.f_id}}" data-from-name="{{$item.from_name}}" data-to-name="{{$item.to_name}}">
				<td align="center">
					{{if $item.to_avatar}}
						<img src="{{$item.to_avatar}}" style="width: 65px;height: 65px;">
					{{/if}}
				</td>
				<td>
					{{$item.to_name}}<br>{{$item.to_phone}}
				</td>
				<td>
					{{if $item.from_avatar}}
						<img src="{{$item.from_avatar}}" style="width: 65px;height: 65px;">
					{{/if}}
				</td>
				<td>
					{{$item.from_name}}<br>{{$item.from_phone}}
				</td>
				<td>
					<span class="m-status-{{$item.f_status}}">{{$item.status_str}}</span>
				</td>
				<td>
					添加:{{$item.add_admin}}:{{$item.f_created}} <br>
					{{if $item.comfirm_admin}}
						审核:{{$item.comfirm_admin}}:{{$item.f_updated}}
					{{/if}}
				</td>
				<td>
					{{if $item.f_status==2}}
						<a href="javascript:;" class="modU btn btn-outline btn-primary btn-xs">审核</a>
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
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body" style="overflow:hidden">
				<div class="col-sm-12 form-horizontal">

					<div class="form-group">
						<label class="col-sm-2 control-label">请选择:</label>
						<div class="col-sm-4">
							<select class="form-control" data-field="st">
								<option value="">-=请选择=-</option>
								{{foreach from=$stDict item=item key=key}}
									<option value="{{$key}}">{{$item}}</option>
								{{/foreach}}
							</select>
						</div>
					</div>

				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-primary" data-tag="cat-chat" id="btnSave">确定保存</button>
			</div>
		</div>
	</div>
</div>

<script>
	$sls = {
		loadflag: 0,
		fid: '',
		from_name: '',
		to_name: '',
		titleObj: $("#modModal").find('.modal-title'),
	};
	$("a.modU").click(function () {
		var self = $(this).closest("tr");
		$sls.fid = self.attr('data-fid');
		$sls.from_name = self.attr('data-from-name');
		$sls.to_name = self.attr('data-to-name');
		$sls.titleObj.html('审核【' + $sls.from_name + '】是' + '【' + $sls.to_name + '】' + '的上级');
		$("#modModal").modal("show")
	});

	$(document).on("click", "#btnSave", function () {
		var postData = {tag: "mod_yxs_comfirm", fid: $sls.fid};
		var st = $("[data-field=st]").val();
		if (!st) {
			layer.msg('请选择审核状态');
			return;
		}
		postData['st'] = st;
		console.log(postData);

		if ($sls.loadflag) {
			return;
		}
		$sls.loadflag = 1;
		$.post("/api/youz",
			postData,
			function (resp) {
				$sls.loadflag = 0;
				if (resp.code == 0) {
					location.reload();
				} else {
					layer.msg(resp.msg);
				}
			}, "json");
	});

</script>
{{include file="layouts/footer.tpl"}}