{{include file="layouts/header.tpl"}}
<style>
	ul.branches,
	ul.folders {
		list-style: none;
		margin: 0;
		padding: 0;
	}

	ul li label {
		font-weight: normal;
	}

	ul.folders li {
		float: left;
		width: 33%;
		margin: 1px 0;

	}

	.branches li {
		float: left;
		width: 47%;
		margin: 6px 0 6px 15px;
		font-weight: 400;
		border-bottom: 1px dotted #d8d8d8;
	}

	@media only screen and (max-width: 880px) {
		.branches li {
			width: 97%;
			margin: 6px 0;
		}
	}

	.branches li label {
		padding-right: 5px;
		font-weight: 300;
	}

	.branches li input {
		margin-right: 3px;
	}

	.tip {
		color: #777;
		font-size: 13px;
		padding-left: 10px;
	}

	.tip b {
		color: #333;
	}
</style>

<div class="row">
	<h4>{{if not $userInfo}}添加后台用户{{else}}修改后台用户{{/if}}</h4>
</div>

<div class="row">
	<div class="col-lg-5 form-horizontal">
		<div class="form-group">
			<label class="col-sm-4 control-label">登录ID:</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" name="name" autocomplete="off" required
							 placeholder="(必填)登录ID. 例如:liming, 13912138868"
							 value="{{if $userInfo}}{{$userInfo.aLoginId}}{{/if}}">
			</div>
		</div>

		<div class="form-group">
			<label class="col-sm-4 control-label">登录密码:</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" name="pass" autocomplete="off" {{if $userInfo}}
					placeholder="(选填)不填则不修改登录密码" {{else}} required {{/if}}>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">用户名称:</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" name="note" autocomplete="off" required
							 placeholder="(必填)用户名称. 例如:张三, 李四" value="{{if $userInfo}}{{$userInfo.aName}}{{/if}}">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">用户手机:</label>
			<div class="col-sm-8">
				<input type="text" class="form-control" name="phone" autocomplete="off" required
							 placeholder="(非必填)" value="{{if $userInfo}}{{$userInfo.aPhone}}{{/if}}">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">权限等级:</label>
			<div class="col-sm-8">
				<select name="level" required="required" class="form-control">
					<option>请选择</option>
					{{foreach from=$levels key=k item=level}}
						<option value="{{$k}}" {{if $userInfo && $userInfo['aLevel']==$k}}selected{{/if}}>{{$level}}</option>
					{{/foreach}}
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">是否是财务:</label>
			<div class="col-sm-8">
				<select name="isfinance" required="required" class="form-control">
					<option>请选择</option>
					<option value="0" {{if $userInfo && $userInfo['aIsFinance']==0}}selected{{/if}}>否</option>
					<option value="1" {{if $userInfo && $userInfo['aIsFinance']==1}}selected{{/if}}>是</option>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">是否是供应链:</label>
			<div class="col-sm-8">
				<select name="isapply" required="required" class="form-control">
					<option>请选择</option>
					<option value="0" {{if $userInfo && $userInfo['aIsApply']==0}}selected{{/if}}>否</option>
					<option value="1" {{if $userInfo && $userInfo['aIsApply']==1}}selected{{/if}}>是</option>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">是否是运营:</label>
			<div class="col-sm-8">
				<select name="isoperator" required="required" class="form-control">
					<option>请选择</option>
					<option value="0" {{if $userInfo && $userInfo['aIsOperator']==0}}selected{{/if}}>否</option>
					<option value="1" {{if $userInfo && $userInfo['aIsOperator']==1}}selected{{/if}}>是</option>
				</select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">是否是销售:</label>
			<div class="col-sm-8">
				<select name="aIsSaler" required="required" class="form-control">
					<option>请选择</option>
					<option value="0" {{if $userInfo && $userInfo['aIsSaler']==0}}selected{{/if}}>否</option>
					<option value="1" {{if $userInfo && $userInfo['aIsSaler']==1}}selected{{/if}}>是</option>
				</select>
			</div>
		</div>

	</div>
	<div class="col-lg-7">
		<div class="form-group">
			<label class="col-sm-4 control-label">可见菜单:</label>
			<div class="col-sm-8">
				<ul class="folders">
					{{foreach from=$rights key=rId item=rItem}}
						<li>
							<label>
								<input name="rights" class="ck-rights" type="checkbox" value="{{$rId}}" {{$rItem["checked"]}}>
								{{$rItem["name"]}}
							</label>
						</li>
					{{/foreach}}
				</ul>
			</div>
		</div>
	</div>
</div>
<div style="height:5em"></div>
<div class="m-bar-bottom">
	<a href="javascript:;" class="opSave btn btn-primary" data-id="{{$id}}">确认保存</a>
</div>
<script>
	$(".opSave").on("click", function () {
		var id = $(this).attr("data-id");
		var branches = [];
		$("input[type=radio]").each(function () {
			var self = $(this);
			if (self.is(':checked')) {
				branches[branches.length] = [self.attr("name"), self.val()];
			}
		});
		var rights = [];
		$.each($(".ck-rights"), function () {
			var self = $(this);
			if (self.is(':checked')) {
				rights.push(self.val())
			}
		});

		layer.load();
		$.post("/api/user", {
			id: id,
			tag: "edit-admin",
			name: $("input[name=name]").val(),
			phone: $("input[name=phone]").val(),
			pass: $("input[name=pass]").val(),
			note: $("input[name=note]").val(),
			level: $("select[name=level]").val(),
			isfinance: $("select[name=isfinance]").val(),
			isapply: $("select[name=isapply]").val(),
			isoperator: $("select[name=isoperator]").val(),
			aIsSaler: $("select[name=aIsSaler]").val(),
			rights: JSON.stringify(rights)
		}, function (resp) {
			layer.closeAll();
			if (resp.code == 0) {
				layer.msg(resp.msg);
				setTimeout(function () {
					location.href = '/admin/users';
				}, 600);
			} else {
				layer.msg(resp.msg);
			}
		}, "json");
	});
</script>
{{include file="layouts/footer.tpl"}}