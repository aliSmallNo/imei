{{include file="layouts/header.tpl"}}
<style>
	.font12 {
		font-size: 12px;
	}
	.font10{
		font-size: 10px;
		color: #0d5ccf;
	}
</style>
<div class="row">
	<h4>订单列表</h4>
</div>
<div class="row">
	<form action="/youz/orders" method="get" class="form-inline">

		<div class="form-group">
			<input class="form-control" placeholder="用户名称" type="text" name="name"
						 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
			<input class="form-control" placeholder="用户手机" type="text" name="phone"
						 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
			<select class="form-control" name="st">
				<option value="">-=请选择=-</option>
				{{foreach from=$stDict item=item key=key}}
					<option value="{{$key}}" {{if isset($getInfo['st']) && $getInfo['st']==$key}}selected{{/if}}>{{$item}}</option>
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
				用户头像
			</th>
			<th class="col-sm-3">
				用户信息
			</th>
			<th class="col-sm-1">
				状态
			</th>
			<th class="col-sm-1">
				商品图片
			</th>
			<th>
				订单信息
			</th>
			<th class="col-sm-3">
				时间
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$items item=item}}
			<tr>
				<td align="center">
					{{if $item.avatar}}
						<span>
						<img src="{{$item.avatar}}" style="width: 65px;height: 65px;">
					</span>
					{{/if}}
				</td>
				<td>
					用户:{{$item.name}} {{if $item.phone}}{{$item.phone}}{{else}}{{$item.o_buyer_phone}}{{/if}}<br>
					收货人:{{$item.o_receiver_name}} {{$item.o_receiver_tel}}<br>
					<span class="font10">{{$item.o_fans_id}}</span><br>
					<span class="font10">{{$item.o_tid}}</span><br>
				</td>
				<td>
					{{$item.status_str}}
				</td>
				<td>
					<img src="{{$item.pic_path}}" style="width: 65px;height: 65px;">
				</td>
				<td>
					订单: {{$item.o_price}}*{{$item.o_num}}={{$item.o_total_fee}} <br>
					支付: {{$item.o_payment}} 退款:{{$item.o_refund}}
				</td>
				<td >
					添加时间: {{$item.o_created}}<br>
					更新时间: {{$item.o_update_time}}
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

</script>
{{include file="layouts/footer.tpl"}}