{{include file="layouts/header.tpl"}}
<style>
	.f-tip {
		font-size: 12px;
		color: #666;
		font-weight: 300;
	}
	.uname {
		font-size: 12px;
		font-weight: 400;
	}

	.prefix- {
		color: #f50 !important;
	}

	.bals {
		margin: 0;
		padding: 20px 0 25px;
		list-style: none;
		display: flex;
		flex-flow: row wrap;
		align-content: flex-start;
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}

	.bals li {
		flex: 0 0 25%;
		display: flex;
		position: relative;
		padding: 3px 8px;
	}

	.bals li:after {
		content: '';
		position: absolute;
		bottom: 0;
		left: 8px;
		right: 8px;
		border-bottom: 1px dotted #c8c8c8;
	}

	.bals li em {
		font-style: normal;
		flex: 0 0 140px;
		font-weight: 400;
	}

	.bals li b {
		font-style: normal;
		font-weight: 400;
		flex: 1;
		text-align: right;
		color: #838383;
	}
</style>


<div class="row">
	<h4>充值账户记录列表
		{{if $isDebugger}}<a href="javascript:;" class="modU btn btn-outline btn-primary btn-xs">修改账户</a>{{/if}}
	</h4>
</div>
<form action="/site/recharges" class="form-inline">
	<input class="form-control" placeholder="用户名称" name="name"
	       value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}">
	<input class="form-control" placeholder="手机号" name="phone"
	       value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}">
	<select class="form-control" name="cat">
		<option value="">-=请选择=-</option>
		{{foreach from=$catDict key=key item=item}}
			<option value="{{$key}}" {{if isset($getInfo['cat']) && $getInfo['cat']==$key}}selected{{/if}}>
				{{$item}}</option>
		{{/foreach}}
	</select>
	<label><input type="checkbox" name="income" value="1"
	              {{if isset($getInfo['income']) && $getInfo['income']}}checked{{/if}}> 只看收入</label>
	<button class="btn btn-primary">查询</button>
</form>
<ul class="bals clearfix">
	{{foreach from=$bals item=bal}}
		<li><em>{{$bal.title}}</em><b class="prefix{{$bal.prefix}}">{{$bal.amt}}{{$bal.unit_name}}</b></li>
	{{/foreach}}
</ul>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th>
				头像
			</th>
			<th class="col-lg-3">
				账户余额
			</th>
			<th>
				类型
			</th>
			<th>
				数量/金额
			</th>
			<th>
				媒桂花/金额
			</th>
			<th>
				时间
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$items item=item}}
			<tr>
				<td>
					<img src="{{$item.avatar}}" style="width: 65px;height: 65px;">
					<div class="uname">{{$item.uname}}<br>
						{{$item.phone}}</div>
				</td>
				<td>
					{{foreach from=$item.details key=key item=detail}}
						{{if $key=='bal'}}
							{{$detail.title}}: {{$detail.amt}}{{$detail.unit_name}}
							{{if $detail.amt2}}+{{$detail.amt2}}{{$detail.unit_name2}}{{/if}}
							{{if $detail.amt3}}+{{$detail.amt3}}{{$detail.unit_name3}}{{/if}}
							{{if $detail.amt4}}+{{$detail.amt4}}{{$detail.unit_name4}}{{/if}}
							<br>
						{{else}}
							{{$detail.title}}: {{$detail.amt}}{{$detail.unit_name}}
							<br>
						{{/if}}
					{{/foreach}}
				</td>
				<td>
					{{$item.tcat}}
					<div class="f-tip">{{if $item.subtitle}}({{$item.subtitle}}){{/if}}</div>
				</td>
				<td class="prefix{{$item.prefix}}">
					{{if $item.amt}}￥{{$item.amt/100.0|string_format:"%.2f"}}{{/if}}
				</td>
				<td class="prefix{{$item.prefix}}">
					{{$item.amt_title}}
				</td>
				<td>
					{{$item.date}}
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
				<h4 class="modal-title">操作</h4>
			</div>
			<div class="modal-body" style="overflow:hidden">
				<div class="col-sm-12 form-horizontal">
					<div class="form-group">
						<label class="col-sm-2 control-label">用户手机号:</label>
						<div class="col-sm-9">
							<input data-field="phone" required class="form-control" value="" placeholder="(必填)">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">数量:</label>
						<div class="col-sm-9">
							<input data-field="amt" required class="form-control" value="" placeholder="(必填)">
							<p style="font-size: 12px;color: red">如果是提现，单位是分</p>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">类型:</label>
						<div class="col-sm-4">
							<select class="form-control" data-field="cat">
								<option value="650">提现</option>
								<option value="108">新人奖励</option>
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
	$("a.modU").click(function () {
		var self = $(this);
		$("#modModal").modal("show")
	});

	var loadflag = 0;
	$(document).on("click", "#btnSave", function () {
		var err = 0;
		var postData = {};
		$("[data-field]").each(function () {
			var field = $(this).attr("data-field");
			var text = parseInt($.trim($(this).val()));
			if (!text) {
				console.log(field + ' ' + text);
				layer.msg("必填项不能为空！");
				err = 1;
				$(this).focus();
				return false;
			}
			postData[field] = text;
		});
		if (err) {
			return false;
		}
		console.log(postData);

		if (loadflag) {
			return;
		}
		loadflag = 1;
		$.post("/api/user", {
			tag: "mod_user_trans",
			data: JSON.stringify(postData),
		}, function (resp) {
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
