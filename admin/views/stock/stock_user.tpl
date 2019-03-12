{{include file="layouts/header.tpl"}}
<style>
	.type_1, .type_2 {
		font-size: 10px;
		background: #888;
		color: #fff;
		padding: 2px 4px;
		border-radius: 3px;
	}

	.type_2 {
		background: #00aa00;
	}

	.rate {
		font-size: 12px;
		color: red;
	}
</style>
<div class="row">
	<h4>用户列表
		{{if $is_stock_leader}}<a href="javascript:;" class="add_user btn btn-outline btn-primary btn-xs">添加用户</a>{{/if}}
	</h4>
</div>
<div class="row">
	<form action="/stock/stock_user" method="get" class="form-inline">
		<div class="form-group">
			<input class="form-control" placeholder="用户名" type="text" name="name"
						 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
			<input class="form-control" placeholder="用户手机" type="text" name="phone"
						 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
			<select class="form-control" name="type">
				<option value="">-=用户身份=-</option>
				{{foreach from=$types key=key item=item}}
					<option value="{{$key}}">{{$item}}</option>
				{{/foreach}}
			</select>
			<select class="form-control" name="bdphone">
				<option value="">-=渠道=-</option>
				{{foreach from=$bds key=key item=item}}
					<option value="{{$key}}">{{$item}}</option>
				{{/foreach}}
			</select>
			{{if $is_stock_leader}}
			<select class="form-control" name="ord">
				<option value="">-=排序=-</option>
				{{foreach from=$orders key=key item=item}}
					<option value="{{$key}}">{{$item}}</option>
				{{/foreach}}
			</select>
			{{/if}}
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
			<th>用户名</th>
			<th>用户手机</th>
			{{if $is_staff}}
				<th>用户身份|佣金比例</th>
				<th>渠道名</th>
				<th>渠道手机</th>
				<th>备注</th>
			{{/if}}
			<th>时间</th>
			{{if $is_stock_leader}}
				<th>操作</th>
				<th>最后操作订单时间</th>
			{{/if}}
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
			<tr data-uPhone="{{$item.uPhone}}" data-uName="{{$item.uName}}"
					data-uPtPhone="{{$item.uPtPhone}}" data-uPtName="{{$item.uPtName}}"
					data-uRate="{{$item.uRate}}" data-uNote="{{$item.uNote}}"
					data-uType="{{$item.uType}}">
				<td>{{$item.uName}}</td>
				<td>{{$item.uPhone}}</td>
				{{if $is_staff}}
					<td>
						<span class="type_{{$item.uType}}">{{$item.type_t}}</span>
						{{if $item.uRate}}<span class="rate">{{$item.uRate}}</span>{{/if}}
					</td>
					<td>{{$item.uPtName}}</td>
					<td>{{$item.uPtPhone}}</td>
					<td>{{$item.uNote}}</td>
				{{/if}}
				<td>{{$item.uUpdatedOn}}</td>
				{{if $is_staff}}
					<td>
						{{if $is_run}}<a href="javascript:;" class="edit_user btn btn-outline btn-primary btn-xs">修改用户</a>{{/if}}
					</td>
					<td>
						{{$item.opt_dt}}
					</td>
				{{/if}}
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
				<h4 class="modal-title" id="myModalLabel"></h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label">用户名</label>
						<div class="col-sm-8">
							<input type="text" data-field="uName" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">用户手机号</label>
						<div class="col-sm-8">
							<input type="tel" data-field="uPhone" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">用户身份</label>
						<div class="col-sm-8">
							<select class="form-control" data-field="uType">
								{{foreach from=$types key=key item=item}}
									<option value="{{$key}}">{{$item}}</option>
								{{/foreach}}
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">佣金比例</label>
						<div class="col-sm-8">
							<input type="text" data-field="uRate" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">渠道手机号</label>
						<div class="col-sm-8">
							<input type="tel" data-field="uPtPhone" class="form-control"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">备注</label>
						<div class="col-sm-8">
							<input type="text" data-field="uNote" class="form-control"/>
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

	$(document).on("click", ".add_user", function () {
		$sls.tag = 'edit_user';
		$sls.title.html("添加用户信息");
		$("[data-field]").each(function () {
			if ($(this).attr('data-field') == 'uType') {
				$(this).val(1);
			} else {
				$(this).val('');
			}
		});
		$sls.modal.modal('show');
	});

	$(document).on("click", ".edit_user", function () {
		var self = $(this).closest("tr");
		$sls.tag = 'edit_user';
		$sls.title.html("修改用户信息");
		$("[data-field=uNote]").val(self.attr('data-uNote'));
		$("[data-field=uPtPhone]").val(self.attr('data-uPtPhone'));
		$("[data-field=uPtName]").val(self.attr('data-uPtName'));
		$("[data-field=uPhone]").val(self.attr('data-uPhone'));
		$("[data-field=uName]").val(self.attr('data-uName'));
		$("[data-field=uRate]").val(self.attr('data-uRate'));
		$("[data-field=uType]").val(self.attr('data-uType'));
		$sls.modal.modal('show');
	});

	$(document).on('click', '#btnSave', function () {
		var uName = $("[data-field=uName]").val();
		var uPhone = $("[data-field=uPhone]").val();
		var uPtName = $("[data-field=uPtName]").val();
		var uPtPhone = $("[data-field=uPtPhone]").val();
		var uNote = $("[data-field=uNote]").val();
		var uRate = $("[data-field=uRate]").val();
		var uType = $("[data-field=uType]").val();
		if (!uName) {
			layer.msg('用户名不能为空');
			return;
		}
		if (!uPhone) {
			layer.msg('用户手机号不能为空');
			return;
		}
		var postData = {
			uName: uName,
			uPhone: uPhone,
			uPtPhone: uPtPhone,
			uNote: uNote,
			uRate: uRate,
			uType: uType,
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
				}, 1500)
			} else {
				layer.msg(resp.msg);
			}
		}, "json");
	})
</script>
{{include file="layouts/footer.tpl"}}