{{include file="layouts/header.tpl"}}
<style>
	.font12 {
		font-size: 12px;
	}
</style>
<div class="row">
	<h4>分销员列表</h4>
</div>
<div class="row">
	<form action="/youz/sman" method="get" class="form-inline">
		<div class="form-group">
			<input class="form-control" placeholder="严选师名称" type="text" name="name"
						 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
			<input class="form-control" placeholder="严选师手机" type="text" name="phone"
						 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
		</div>
		<input class="form-control" placeholder="邀请方名称" type="text" name="fname"
					 value="{{if isset($getInfo['fname'])}}{{$getInfo['fname']}}{{/if}}"/>
		<input class="form-control" placeholder="邀请方手机" type="text" name="fphone"
					 value="{{if isset($getInfo['fphone'])}}{{$getInfo['fphone']}}{{/if}}"/>
		<input class="form-control" placeholder="管理员名字" type="text" name="aname"
					 value="{{if isset($getInfo['aname'])}}{{$getInfo['aname']}}{{/if}}"/>
		<button class="btn btn-primary">查询</button>
		<span class="space"></span>
	</form>
	<div style="height: 1em"></div>
	<button class="btn btn-primary opExcel">导出管理</button>
</div>

<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th class="col-sm-1">
				严选师头像
			</th>
			<th class="col-sm-2">
				严选师信息
			</th>
			<th>
				成交金额/笔数
			</th>
			<th class="col-sm-1">
				邀请方
			</th>
			<th class="col-sm-2">
				邀请方信息
			</th>
			<th class="col-sm-2">
				时间
			</th>
			<th>
				管理人
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$items item=item}}
			<tr data-uid="{{$item.uYZUId}}" data-name="{{$item.uName}}">
				<td align="center">
					{{if $item.uAvatar}}
						<span>
						<img src="{{$item.uAvatar}}" style="width: 65px;height: 65px;">
					</span>
					{{/if}}
				</td>
				<td>
					{{$item.uName}}<br>
					{{$item.uPhone}}<br>
					{{if $item.uFollow==1}}<span class="m-cert-1">关注</span>{{else}}<span class="m-sub-0">未关注</span>{{/if}}
				</td>
				<td>
					{{$item.uTradeMoney}}/{{$item.uTradeNum}}
				</td>
				<td>
					{{if $item.favatar}}
						<img src="{{$item.favatar}}" style="width: 65px;height: 65px;">
					{{/if}}
				</td>
				<td>
					{{$item.fname}}<br>
					{{$item.fphone}}<br>
					{{if $item.ffollow==1}}<span class="m-cert-1">关注</span>{{else}}<span class="m-sub-0">未关注</span>{{/if}}
				</td>
				<td class="font12">
					添加时间:<br>{{$item.uCreateOn}}<br>
					更新时间:<br>{{$item.uUpdatedOn}}
				</td>
				<td>
					{{$item.aName}}
					<a href="javascript:;" class="modU btn btn-outline btn-primary btn-xs">添加管理</a>
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
				<h4 class="modal-title">管理员</h4>
			</div>
			<div class="modal-body" style="overflow:hidden">
				<div class="col-sm-12 form-horizontal">

					<div class="form-group">
						<label class="col-sm-2 control-label">管理员:</label>
						<div class="col-sm-4">
							<select class="form-control" data-field="aid">
								<option value="">-=请选择=-</option>
								{{foreach from=$admins item=name key=key}}
									<option value="{{$key}}">{{$name}}</option>
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
		uid: '',
		name: '',
		titleObj: $("#modModal").find('.modal-title'),
	};
	$("a.modU").click(function () {
		var self = $(this).closest("tr");
		$sls.uid = self.attr('data-uid');
		$sls.name = self.attr('data-name');
		$sls.titleObj.html('请选择【' + $sls.name + '】的管理员');
		$("#modModal").modal("show")
	});

	var loadflag = 0;
	$(document).on("click", "#btnSave", function () {
		var err = 0;
		var postData = {tag: "mod_admin_id", uid: $sls.uid};
		var aid = $("[data-field=aid]").val();
		if (!aid) {
			layer.msg('请选择管理员');
			return;
		}
		postData['aid'] = aid;
		console.log(postData);

		if (loadflag) {
			return;
		}
		loadflag = 1;
		$.post("/api/youz",
			postData,
			function (resp) {
				loadflag = 0;
				if (resp.code == 0) {
					location.reload();
				} else {
					layer.msg(resp.msg);
				}
			}, "json");
	})

	$(".opExcel").on("click", function () {
		var aname = $("input[name=aname]").val();
		var url = "/youz/export_yxs?aname=" + aname + "&sign=excel";
		location.href = url;
	});

</script>
{{include file="layouts/footer.tpl"}}