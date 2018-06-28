{{include file="layouts/header.tpl"}}
<style>
	.font12 {
		font-size: 12px;
	}

	.font10 {
		font-size: 10px;
		color: #0d5ccf;
	}

	.st_WAIT_BUYER_PAY,
	.st_WAIT_CONFIRM,
	.st_WAIT_SELLER_SEND_GOODS,
	.st_WAIT_BUYER_CONFIRM_GOODS,
	.st_TRADE_SUCCESS,
	.st_TRADE_CLOSED {
		color: #fff;
		border-radius: 3px;
		display: inline-block;
		font-size: 10px;
		padding: 0 2px;
		border: none;
		margin: 0;
	}

	.st_WAIT_SELLER_SEND_GOODS {
		background: #ee021b;
	}

	.st_WAIT_BUYER_PAY, .st_WAIT_CONFIRM {
		background: #fbc02d;
	}

	.st_WAIT_BUYER_CONFIRM_GOODS {
		background: #953b39;
	}

	.st_TRADE_SUCCESS {
		background: #0f9d58;
	}

	.st_TRADE_CLOSED {
		background: #777;
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
					<option value="{{$key}}"
									{{if isset($getInfo['st']) && $getInfo['st']==$key}}selected{{/if}}>{{$item}}</option>
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
			<th class="col-sm-2">
				用户信息
			</th>
			<th class="col-sm-2">
				状态|汇总订单
			</th>
			<th class="col-sm-1">
				商品图片
			</th>
			<th class="col-sm-2">
				商品
			</th>
			<th>
				订单信息
			</th>
			<th class="col-sm-2">
				时间
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$items item=item}}
			{{foreach from=$item.orders item=order key=okey}}
				<tr>
					{{if $okey==0}}
						<td align="center" rowspan='{{$item.co}}'>
							{{if $item.avatar}}
								<span>
						<img src="{{$item.avatar}}" style="width: 65px;height: 65px;">
					</span>
							{{/if}}
						</td>
						<td rowspan='{{$item.co}}'>
							用户:{{$item.name}} {{if $item.phone}}{{$item.phone}}{{else}}{{$item.o_buyer_phone}}{{/if}}<br>
							收货人:{{$item.o_receiver_name}} {{$item.o_receiver_tel}}<br>
							<span class="font10">{{$item.o_fans_id}}</span><br>
							<span class="font10">{{$item.o_tid}}</span><br>
						</td>
						<td rowspan='{{$item.co}}'>
							<span class="st_{{$item.o_status}}">{{$item.status_str}}</span><br><br>
							订单: {{$item.o_sku_num}}件 | {{$item.o_total_fee}}<br>
							支付: {{$item.o_payment}}元 退款: {{$item.o_refund}}<br>
						</td>
					{{/if}}
					<td>
						<img src="{{$order.pic_path}}" style="width: 65px;height: 65px;">
					</td>
					<td>
						{{$order.title}}
					</td>
					<td>
						<div>
							订单: {{$order.price}}*{{$order.num}}={{$order.total_fee}}<br>
							支付: {{if $item.o_pay_time}}{{$order.payment}}{{else}}0{{/if}}元<br>
							{{foreach from=$order.sku_properties_name_arr item=$prop}}
								{{$prop.k}}:{{$prop.v}}
							{{/foreach}}
						</div>
					</td>
					{{if $okey==0}}
						<td rowspan='{{$item.co}}'>
							添加时间: {{$item.o_created}}<br>
							更新时间: {{$item.o_update_time}}
						</td>
					{{/if}}
				</tr>
			{{/foreach}}
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