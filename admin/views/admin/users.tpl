{{include file="layouts/header.tpl"}}
<style>
	.user_tag {
		font-size: 10px;
		background: #999;
		color: #fff;
		padding: 1px 4px;
		border-radius: 3px;
	}

	.user_tag.finance {
		background: #f89406;
	}

	.user_tag.apply {
		background: #00aa00;
	}
	.user_tag.oprerator {
		background: red;
	}
</style>
<div class="row">
	<h4>后台用户列表 </h4>
</div>
<form class="form-inline" action="/admin/users" method="get">
	<input class="form-control" name="note" placeholder="用户名" type="text" value="{{$note}}">
	<input class="form-control" name="name" placeholder="登录ID" type="text" value="{{$name}}">
	<input type="submit" class="btn btn-primary" value="查询">
</form>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered table-hover">
		<thead>
		<tr>
			<th>
				帐号
			</th>
			{{foreach from=$menus item=item}}
				<th>{{$item["name"]}}</th>
			{{/foreach}}
			<th>
				操作
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=prod}}
			<tr data-id="{{$prod.aId}}">
				<td>
					{{$prod.aName}} {{$prod.aLoginId}}
					{{if $prod.levelDesc}}
						<div>({{$prod.levelDesc}})</div>{{/if}}
					<div>
						{{if $prod.aIsApply==1}}<span class="user_tag apply">供应链</span>{{/if}}
						{{if $prod.aIsOperator==1}}<span class="user_tag oprerator">运营</span>{{/if}}
						{{if $prod.aIsFinance==1}}<span class="user_tag finance">财务</span>{{/if}}
					</div>
				</td>
				{{foreach from=$menus key=cKey item=cItem}}
					<td style="text-align: center;">{{if $prod['menu_'|cat:$cKey] >0}}
							<i class="fa fa-check" style="color: #39ac69;font-size: 1.4em;"></i>
						{{/if}}
					</td>
				{{/foreach}}
				<td>
					<a href="javascript:;" class="modU btn btn-outline btn-primary btn-xs" cid="{{$prod.aId}}">修改用户</a>
					<div class="btn-divider"></div>
					<a href="javascript:;" class="delU btn btn-outline btn-danger btn-xs" cid="{{$prod.aId}}">删除用户</a>
				</td>
			</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>
<script>
	$("a.delU").click(function () {
		var aId = $(this).attr("cid");
		layer.confirm('您确定要删除这个用户吗？', {
			btn: ['确定', '取消'],
			title: '删除后台用号'
		}, function () {
			delUser(aId);
		}, function () {
		});
	});

	function delUser(aId) {
		$.post("/api/user", {
			tag: "del-admin",
			id: aId
		}, function (resp) {
			if (resp.code == 0) {
				layer.msg(resp.msg);
				location.reload();
			}
		}, "json");
	}

	$("a.modU").click(function () {
		var cid = $(this).attr("cid");
		location.href = "/admin/user?id=" + cid;
	});
</script>
{{include file="layouts/footer.tpl"}}