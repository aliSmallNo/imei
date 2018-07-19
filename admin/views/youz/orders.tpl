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

	.pay_pic_last img {
		width: 50px;
		margin-right: 5px;
	}
</style>
<div class="row">
	<h4>订单列表</h4>
</div>
<div class="row">
	<form action="/youz/orders" method="get" class="form-inline">

		<div class="form-group">
			<input class="form-control" placeholder="名称" type="text" name="title"
						 value="{{if isset($getInfo['title'])}}{{$getInfo['title']}}{{/if}}"/>
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
		<a class="btn btn-primary opExcel">导出未发货订单</a>
		{{if $able_refresh_data}}<a class="btn btn-primary update_data">刷新</a>{{/if}}
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
							{{if $isDebugger}}
								<span class="font10">{{$item.o_fans_id}}</span>
								<br>
								<span class="font10">{{$item.o_tid}}</span>
								<br>
							{{/if}}
						</td>
						<td rowspan='{{$item.co}}'>
							<span class="st_{{$item.o_status}}">{{$item.status_str}}</span><br><br>
							订单: {{$item.o_sku_num}}件 | {{$item.o_total_fee}}<br>
							支付: {{$item.o_payment}}元 退款: {{$item.o_refund}}<br>
							{{if $item.trade_memo}}<span class="st_WAIT_BUYER_PAY">备注：{{$item.trade_memo}}</span>{{/if}}
						</td>
					{{/if}}
					<td>
						<img src="{{$order.pic_path}}" style="width: 65px;height: 65px;">
						{{if $isDebugger}}
							<div><span class="font10">{{$order.item_id}}</span></div>
							<div><span class="font10">{{$order.sku_id}}</span></div>
						{{/if}}
					</td>
					<td>
						{{$order.title}}
					</td>
					<td data-tid="{{$item.o_tid}}" data-gid="{{$order.item_id}}" data-skuid="{{$order.sku_id}}"
							data-title="{{$order.title}}"
							data-payment="{{if $item.o_pay_time}}{{$order.payment}}{{else}}0{{/if}}">
						<div>
							订单: {{$order.price}}*{{$order.num}}={{$order.total_fee}}<br>
							支付: {{if $item.o_pay_time}}{{$order.payment}}{{else}}0{{/if}}元<br>
							{{foreach from=$order.sku_properties_name_arr item=$prop}}
							{{$prop.k}}:{{$prop.v}}
							{{/foreach}}<br>
							{{if $is_supply_chain}}
								{{if $order.f_status==0}}
									<a class="add_pay_info btn btn-outline btn-primary btn-xs">添加付款信息</a>
								{{else}}
									<a class="add_pay_info btn btn-outline btn-danger btn-xs">编辑付款信息</a>
								{{/if}}
							{{/if}}
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
				<h4 class="modal-title">请填写支付信息</h4>
			</div>
			<div class="modal-body" style="overflow:hidden">
				<div class="col-sm-12 form-horizontal">
					<div class="form-group">
						<label class="col-sm-2 control-label">商品:</label>
						<div class="col-sm-7">
							<p data-title="title"></p>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">买家支付:</label>
						<div class="col-sm-7">
							<p data-payment="payment"></p>
						</div>
					</div>
					<input type="hidden" class="form-control" data-f="fid">
					<div class="form-group">
						<label class="col-sm-2 control-label">付款人</label>
						<div class="col-sm-7">
							<select class="form-control" data-f="pay_aid">
								<option value="">-=请选择=-</option>
								{{foreach from=$bds item=bd key=key}}
									<option value="{{$key}}">{{$bd}}</option>
								{{/foreach}}
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">付款金额</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" data-f="pay_amt">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">截图金额:</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" data-f="pic_pay_amt">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">备注</label>
						<div class="col-sm-7">
							<input type="text" class="form-control" data-f="pay_note">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">付款截图</label>
						<div class="col-sm-7">
							<input type="file" data-f="pay_pic" multiple accept=".png,.jpg,.jpeg">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"></label>
						<div class="col-sm-7 pay_pic_last" id="pay_pic_last">

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
		tid: '',
		gid: '',
		skuid: '',
		title: '',
		payment: '',
		titleObj: $("#modModal").find('.modal-title'),
	};
	$("a.add_pay_info").click(function () {
		var self = $(this).closest("td");
		$sls.tid = self.attr('data-tid');
		$sls.gid = self.attr('data-gid');
		$sls.skuid = self.attr('data-skuid');
		$sls.title = self.attr('data-title');
		$sls.payment = self.attr('data-payment');
		$('[data-title=title]').html($sls.title);
		$('[data-payment=payment]').html($sls.payment);
		load_pay_info(function (resp) {
			init_pay_info(resp);
			$("#modModal").modal("show")
		});
	});

	function init_pay_info(data) {
		if (!data) {
			data = {pay_aid: '', pay_amt: '', pic_pay_amt: '', pay_note: '', id: ''}
		}
		$("[data-f=fid]").val(data.id);
		$("[data-f=pay_aid]").val(data.pay_aid);
		$("[data-f=pay_amt]").val(data.pay_amt);
		$("[data-f=pic_pay_amt]").val(data.pic_pay_amt);
		$("[data-f=pay_note]").val(data.pay_note);
		$("[data-f=pay_pic]").val('');
		$("#pay_pic_last").html(Mustache.render('{[#pay_pic]}<img src="{[0]}" class="i-av" bsrc="{[1]}">{[/pay_pic]}', data));
	}

	$(document).on("click", "#btnSave", function () {
		var err = 0;
		var fid = $("[data-f=fid]").val();
		var formData = new FormData();
		formData.append("tag", 'edit_finance_info');
		formData.append("skuid", $sls.skuid);
		formData.append("gid", $sls.gid);
		formData.append("tid", $sls.tid);
		$("[data-f]").each(function () {
			var self = $(this);
			var f = self.attr('data-f');
			var v = self.val();
			var t = self.closest('.form-group').find('.col-sm-2').html();
			if ($.inArray(f, ['pay_aid', 'pay_amt', 'pic_pay_amt']) > -1 && !v) {
				layer.msg(t + '是必填项');
				self.focus();
				err = 1;
				return false;
			} else {
				formData.append(f, v)
			}
			if (f == 'pay_pic') {
				var files = self[0].files;
				console.log(files, fid);
				var img_len = files.length;
				if (!fid && (img_len < 1 || img_len > 10)) {
					layer.msg('图片至少1张，且不可超过10张');
					err = 1;
					return false;
				}
				$.each(files, function (k, v) {
					formData.append("pay_pic[]", v)
				});
			}
		});
		if (err) {
			return;
		}
		if ($sls.loadflag) {
			return;
		}
		$sls.loadflag = 1;
		$.ajax({
			url: '/api/youz',
			type: "POST",
			data: formData,
			cache: false,
			processData: false,
			contentType: false,
			success: function (resp) {
				$sls.loadflag = 0;
				if (resp.code < 1) {
					init_pay_info({});
					$("#modModal").modal("hide");
					layer.msg('编辑成功~');
				} else {
					layer.msg(resp.msg);
				}
			}
		});
	});

	function load_pay_info(cb) {
		if ($sls.loadflag) {
			return;
		}
		$sls.loadflag = 1;
		layer.load();
		$.post("/api/youz",
			{
				tag: 'get_finance_info',
				tid: $sls.tid,
				skuid: $sls.skuid,
				gid: $sls.gid,
			},
			function (resp) {
				layer.closeAll();
				$sls.loadflag = 0;
				if (resp.code == 0) {
					typeof cb == "function" && cb(resp.data);
				} else {
					layer.msg(resp.msg);
				}
			}, "json");
	}

	// 导出表格
	$(".opExcel").on("click", function () {
		var url = "/youz/orders?export=excel";
		location.href = url;
	});
	// 更新订单
	$(".update_data").on("click", function () {
		if ($sls.loadflag) {
			return;
		}
		$sls.loadflag = 1;
		layer.load();
		$.post("/api/youz",
			{
				tag: 'update_admin_data',
				subtag: 'orders',
			},
			function (resp) {
				layer.clear();
				$sls.loadflag = 0;
				if (resp.code == 0) {
					location.reload();
				} else {
					layer.msg(resp.msg);
				}
			}, "json");
	});

	$(document).on("click", ".i-av", function () {
		var self = $(this);
		var photos = {
			title: '头像大图',
			data: [{
				src: self.attr("bsrc")
			}]
		};
		showImages(photos);
	});

	function showImages(imagesJson, idx) {
		if (idx) {
			imagesJson.start = idx;
		}
		layer.photos({
			photos: imagesJson,
			shift: 5,
			tab: function (info) {
				console.log(info);
			}
		});
	}

</script>
{{include file="layouts/footer.tpl"}}