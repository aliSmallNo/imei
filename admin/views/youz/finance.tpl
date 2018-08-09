{{include file="layouts/header.tpl"}}
<style>

	.font10 {
		font-size: 10px;
		color: #0d5ccf;
	}

	.font_pic_note {
		color: red;
		font-size: 12px;
	}

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

	.pay_pic_last img {
		width: 50px;
		margin-right: 5px;
	}

	.prop_name {
		background: #919191;
		color: #fff;
		font-size: 10px;
		padding: 2px 5px;
		border-radius: 3px;
	}

	.btn-outline {
		margin: 2px 0;
	}

	.audit_reson_str {
		font-size: 10px;
		color: #fc030a;
	}
</style>
<div class="row">
	<h4>对账信息: 总付款：{{$total_pay}}</h4>
</div>
<div class="row">
	<form action="/youz/finance" class="form-inline">
		<input class="form-control" placeholder="商品名称" type="text" name="title"
					 value="{{if isset($getInfo['title'])}}{{$getInfo['title']}}{{/if}}"/>
		<input class="form-control beginDate my-date-input" placeholder="开始时间" name="stime"
					 value="{{if isset($getInfo['stime'])}}{{$getInfo['stime']}}{{/if}}">
		至
		<input class="form-control endDate my-date-input" placeholder="截止时间" name="etime"
					 value="{{if isset($getInfo['etime'])}}{{$getInfo['etime']}}{{/if}}">
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
		<input class="form-control" placeholder="订单号" type="text" name="tid"
					 value="{{if isset($getInfo['tid'])}}{{$getInfo['tid']}}{{/if}}"/>
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
			<th class="col-sm-2">商品信息</th>
			<th class="col-sm-2">订单信息</th>
			<th class="col-sm-3">截图信息</th>
			<th class="col-sm-2">操作</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$items item=item}}
			<tr data-fid="{{$item.f_id}}">
				<td>
					{{$item.od_fans_nickname}}<br>
					{{$item.od_receiver_name}}({{$item.od_receiver_tel}})
					{{if $isDebugger}}<span class="font10">{{$item.od_fans_id}}</span>{{/if}}
				</td>
				<td>
					<img src="{{$item.od_pic_path}}" alt="">
				</td>
				<td>
					<div class="order_title">
						{{$item.od_title}}
						{{if $item.prop_name}}
							<div>
								<span class="prop_name">{{foreach from=$item.prop_name item=prop}}{{$prop.k}}:{{$prop.v}} {{/foreach}}</span>
							</div>
						{{/if}}
					</div>
					{{if $isDebugger}}
						<div><span class="font10">{{$item.od_item_id}}</span> <span class="font10">{{$item.od_sku_id}}</span></div>
					{{/if}}
				</td>
				<td>
					<div>
						订单: 价格{{$item.od_price}} X 数量{{$item.od_num}} = 应付: {{$item.od_total_fee}}<br>
						买家支付：{{$item.od_payment}}
					</div>
					<span class="st_{{$item.od_status}}">{{$item.status_str}}</span>
					<div><span class="font10">{{$item.od_tid}}</span></div>
				</td>
				<td>
					{{if $item.trade_memo}}<span class="st_WAIT_BUYER_PAY">有赞备注：{{$item.trade_memo}}</span>{{/if}}
					<span class="st_WAIT_BUYER_PAY">备注：{{$item.f_pay_note}}</span>
					<div>
						{{$item.aName}}支付：{{$item.f_pay_amt/100}}元
						<span class="font_pic_note">截图金额:{{$item.f_pic_pay_amt/100}}</span>
					</div>
					<div>上传截图时间：{{$item.f_create_on|date_format:'%y-%m-%d %H:%M'}}</div>
					<div>
						{{foreach from=$item.pay_pic item=pic}}
							<img src="{{$pic[0]}}" bsrc="{{$pic[1]}}" class="i-av small">
						{{/foreach}}
					</div>
				</td>
				<td data-tid="{{$item.od_tid}}" data-gid="{{$item.od_item_id}}" data-skuid="{{$item.od_sku_id}}"
						data-title="{{$item.od_title}}" data-reason="{{$item.f_audit_reason}}" data-st="{{$item.f_status}}"
						data-payment="{{if $item.od_paytime}}{{$item.od_payment}}{{else}}0{{/if}}">
					{{if $item.f_status!=1 && $is_finance}}
						<a href="javascript:;" class="operate btn btn-outline btn-primary btn-xs">审核</a>
					{{/if}}
					{{if $is_supply_chain && $item.f_status!=1}}
						<a class="add_pay_info btn btn-outline btn-danger btn-xs">编辑付款信息</a>
					{{/if}}
					<div>
						<span class="f_st_{{$item.f_status}}">{{$item.f_status_str}}</span>
					</div>
					<div class="audit_reson_str">
						{{if $item.f_audit_reason}}
							{{if $item.f_status==1}}通过原因: {{/if}}
							{{if $item.f_status==9}}失败原因: {{/if}}
							{{$item.f_audit_reason}}
						{{/if}}
					</div>
					<div>{{if $item.f_audit_on}}审核于{{$item.f_audit_on|date_format:'%y-%m-%d %H:%M'}}{{/if}}</div>
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

<div class="modal fade" id="auditModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">审核支付</h4>
			</div>
			<div class="modal-body" style="overflow:hidden">
				<div class="col-sm-12 form-horizontal">

					<div class="form-group">
						<label class="col-sm-2 control-label">状态:</label>
						<div class="col-sm-8">
							<select class="form-control" data-field="st">
								<option value="">-=请选择=-</option>
								{{foreach from=$f_stDict item=ftext key=key}}
									<option value="{{$key}}">{{$ftext}}</option>
								{{/foreach}}
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">原因:</label>
						<div class="col-sm-8">
							<textarea class="form-control" data-field="reason"></textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-primary" id="audit_save">确定保存</button>
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
					setTimeout(function () {
						location.reload();
					}, 800)
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


	var audit = {
		fid: '',
		modal: $("#auditModal"),
	};
	$("a.operate").click(function () {
		var self = $(this);
		var cell = self.closest('tr');
		var td = self.closest('td');
		audit.fid = cell.attr("data-fid");
		$("[data-field=st]").val(td.attr("data-st"));
		$("[data-field=reason]").val(td.attr("data-reason"));
		audit.modal.modal("show");
	});

	$(document).on("click", "#audit_save", function () {
		var reason = $("[data-field=reason]").val();
		var st = $("[data-field=st]").val();
		if (!st) {
			layer.msg("状态还没选择~");
			return;
		}
		$.post("/api/youz",
			{
				tag: 'audit_finance_info',
				fid: audit.fid,
				st: st,
				reason: reason
			},
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
	});


</script>
{{include file="layouts/footer.tpl"}}