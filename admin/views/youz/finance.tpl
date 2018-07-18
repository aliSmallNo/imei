{{include file="layouts/header.tpl"}}
<style xmlns="">
	td img {
		width: 64px;
		height: 64px;
	}

	td img.small {
		width: 32px;
		height: 32px;
		margin: 5px;
	}

	.st_WAIT_BUYER_PAY,
	.st_WAIT_CONFIRM,
	.st_WAIT_SELLER_SEND_GOODS,
	.st_WAIT_BUYER_CONFIRM_GOODS,
	.st_TRADE_SUCCESS,
	.st_TRADE_CLOSED,
	.f_st_1,
	.f_st_3,
	.f_st_9 {
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

	.st_WAIT_BUYER_PAY,
	.st_WAIT_CONFIRM,
	.f_st_3 {
		background: #fbc02d;
	}

	.st_WAIT_BUYER_CONFIRM_GOODS {
		background: #953b39;
	}

	.st_TRADE_SUCCESS, .f_st_1 {
		background: #0f9d58;
	}

	.st_TRADE_CLOSED, .f_st_9 {
		background: #777;
	}

	td {
		font-size: 13px;
	}

	.order_title {

	}

	.order_des {
		font-size: 12px;
		color: #032ea4;
		font-weight: 500;
	}

	.pay_pic_last img {
		width: 50px;
		margin-right: 5px;
	}

</style>
<div class="row">
	<h4>对账信息</h4>
</div>
<div class="row">
	<form action="/youz/finance" class="form-inline">
		<input class="form-control beginDate my-date-input" placeholder="开始时间" name="sdate"
					 value="{{if isset($getInfo['sdate'])}}{{$getInfo['sdate']}}{{/if}}">
		至
		<input class="form-control endDate my-date-input" placeholder="截止时间" name="edate"
					 value="{{if isset($getInfo['edate'])}}{{$getInfo['edate']}}{{/if}}">
		<select class="form-control" name="bd">
			<option value="">-=请选择=-</option>
			{{foreach from=$bds item=bd key=key}}
				<option value="{{$key}}"
								{{if isset($getInfo['bd']) && $getInfo['bd']==$key}}selected{{/if}}>{{$bd}}</option>
			{{/foreach}}
		</select>
		<select class="form-control" name="st">
			<option value="">-=请选择=-</option>
			{{foreach from=$f_stDict item=ftext key=key}}
				<option value="{{$key}}"
								{{if isset($getInfo['st']) && $getInfo['st']==$key}}selected{{/if}}>{{$ftext}}</option>
			{{/foreach}}
		</select>
		<button class="btn btn-primary">查询</button>
	</form>
</div>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th class="col-sm-1">用户信息</th>
			<th class="col-sm-1">商品图片</th>
			<th class="col-sm-4">订单信息</th>
			<th class="col-sm-3">截图信息</th>
			<th class="col-sm-2">财务审核</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$items item=item}}
			<tr data-fid="{{$item.f_id}}">
				<td>
					{{$item.od_fans_nickname}}<br>
					{{$item.od_receiver_name}}({{$item.od_receiver_tel}})
				</td>
				<td>
					<img src="{{$item.od_pic_path}}" alt="">
				</td>
				<td>
					<div class="order_title">
						{{$item.od_title}}
					</div>
					<span class="st_{{$item.od_status}}">{{$item.status_str}}</span><br>
					<div class="order_des">
						订单: 价格{{$item.od_price}} X 数量{{$item.od_num}} = 应付: {{$item.od_total_fee}}<br>
						买家支付：{{$item.od_payment}}
					</div>
				</td>
				<td data-tid="{{$item.od_tid}}" data-gid="{{$item.od_item_id}}" data-skuid="{{$item.od_sku_id}}"
						data-title="{{$item.od_title}}"
						data-payment="{{if $item.od_paytime}}{{$item.od_payment}}{{else}}0{{/if}}">
					{{$item.aName}}支付：{{$item.f_pay_amt/100}}元({{$item.f_pay_note}})<br>
					<div>
						{{foreach from=$item.pay_pic item=pic}}
							<img src="{{$pic[0]}}" bsrc="{{$pic[1]}}" class="i-av small">
						{{/foreach}}
					</div>
					<div>上传截图时间：{{$item.f_create_on|date_format:'%y-%m-%d %H:%M'}}</div>
					{{if $is_supply_chain}}<a class="add_pay_info btn btn-outline btn-primary btn-xs">编辑付款信息</a>{{/if}}
				</td>
				<td>
					{{if $item.f_status==3 && $is_finance}}
						<a href="javascript:;" class="operate btn btn-outline btn-primary btn-xs" data-tag="pass">审核通过</a>
						<a href="javascript:;" class="operate btn btn-outline btn-danger btn-xs" data-tag="fail">审核失败</a>
					{{else}}
						<div><span class="f_st_{{$item.f_status}}">{{$item.f_status_str}}</span></div>
						<div>审核于{{$item.f_audit_on|date_format:'%y-%m-%d %H:%M'}}</div>
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
			data = {pay_aid: '', pay_amt: '', pay_note: '', id: ''}
		}
		$("[data-f=fid]").val(data.id);
		$("[data-f=pay_aid]").val(data.pay_aid);
		$("[data-f=pay_amt]").val(data.pay_amt);
		$("[data-f=pay_note]").val(data.pay_note);
		$("[data-f=pay_pic]").val('');
		$("#pay_pic_last").html(Mustache.render('{[#pay_pic]}<img src="{[0]}" class="i-av" bsrc="{[1]}">{[/pay_pic]}', data));
	}

	$(document).on("click", "#btnSave", function () {
		var err = 0;
		var fid = $("[data-f=fid]").val();
		var formData = new FormData();
		formData.append("tag", 'edit_refinance_info');
		formData.append("skuid", $sls.skuid);
		formData.append("gid", $sls.gid);
		formData.append("tid", $sls.tid);
		$("[data-f]").each(function () {
			var self = $(this);
			var f = self.attr('data-f');
			var v = self.val();
			var t = self.closest('.form-group').find('.col-sm-2').html();
			if ($.inArray(f, ['pay_aid', 'pay_amt']) > -1 && !v) {
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
				tag: 'get_refinance_info',
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


	var auditData = {
		tag: 'audit_refinance_info',
		fid: 0,
		f: '',
	};

	$("a.operate").click(function () {
		var self = $(this);
		var cell = self.closest('tr');
		auditData['fid'] = cell.attr("data-fid");
		auditData['f'] = $(this).attr('data-tag');
		var text = self.html();
		layer.confirm('您确定' + text, {
			btn: ['确定', '取消'],
			title: '审核'
		}, function () {
			toAudit(auditData);
		}, function () {
		});
	});

	function toAudit(postData) {
		$.post("/api/youz",
			postData,
			function (resp) {
				if (resp.code < 1) {
					BpbhdUtil.showMsg(resp.msg, 1);
					setTimeout(function () {
						location.reload();
					}, 800);
				} else {
					BpbhdUtil.showMsg(resp.msg);
				}
			}, "json");
	}

	$(document).on("click", ".i-av", function () {
		var self = $(this);
		var photos = {
			title: '头像大图',
			data: [{
				src: self.attr("bsrc")
			}]
		};
		console.log(photos)
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