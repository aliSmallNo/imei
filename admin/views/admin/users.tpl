{{include file="layouts/header.tpl"}}
<style>

</style>
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h4>后台用户列表 </h4>
		</div>
	</div>
	<div class="row">
		<form class="form-inline" action="/admin/users" method="get">
			<input class="form-control" name="name" placeholder="查询登录帐号" type="text" value="{{$name}}"/>
			<input class="form-control" name="note" placeholder="查询用户名称" type="text" value="{{$note}}"/>
			<input type="submit" class="btn btn-primary" value="查询">
		</form>
	</div>
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
			<tr>
				<td>
					{{$prod.aName}}
					{{if $prod.levelDesc}}<div>({{$prod.levelDesc}})</div>{{/if}}
					<div>{{$prod.aId}}</div>
				</td>

				{{foreach from=$menus key=cKey item=cItem}}
				<td style="text-align: center;">{{if $prod['menu_'|cat:$cKey] >0}}<i class="fa fa-check" style="color: #39ac69;font-size: 1.4em;"></i>{{/if}}
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
		$.post("/admin/edituser", {
			tag: "delete",
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