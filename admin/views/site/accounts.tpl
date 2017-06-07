{{include file="layouts/header.tpl"}}
<style>
	.pInfo span {
		font-size: 12px;
		border: 1px solid #f491b2;
		padding: 1px 2px;
		border-radius: 3px;
		color: #f491b2;
		display: inline-block;
		margin-left: 5px;
		margin-bottom: 5px;
	}

	.pInfo em {
		font-size: 12px;
		color: #777;
	}

	.pInfo span.status-0 {
		color: #fff;
		border: 1px solid #f80;
		background: #f80;
	}

	.pInfo .role {
		background: #44b549;
		color: #fff;
		padding: 1px 10px;
		border: none;
	}

	.pInfo span.status-1 {
		color: #fff;
		border: 1px solid #44b549;
		background: #44b549;
	}

	.pInfo span.status-9 {
		color: #fff;
		border: 1px solid #ddd;
		background: #ddd;
	}

	td h5 {
		font-size: 12px;
		font-weight: 400;
	}
</style>
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-12">
			<h4>用户列表 </h4>
		</div>
	</div>
	<div class="row">
		<form class="form-inline" action="/site/accounts" method="get">
			<select name="status" class="form-control">
				{{foreach from=$statusT key=key item=item}}
				<option value="{{$key}}" {{if $status==$key}}selected{{/if}}>{{$item}}</option>
				{{/foreach}}
			</select>
			<input class="form-control" name="name" placeholder="名字" type="text" value="{{$name}}">
			<input type="submit" class="btn btn-primary" value="查询">
		</form>
	</div>
	<div class="row-divider"></div>
	<table class="table table-striped table-bordered table-hover">
		<thead>
		<tr>
			<th style="width: 70px">
				头像
			</th>
			<th class="col-sm-5">
				个人信息
			</th>
			<th class="col-sm-5">
				择偶标准
			</th>
			<th>
				操作
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=prod}}
		<tr data-id="{{$prod.id}}">
			<td>
				<img src="{{$prod.avatar}}" width="100%">
			</td>
			<td class="pInfo">
				{{$prod.name}} <em>({{$prod.location_t}})</em>
				<span class="role">{{$prod.role_t}}</span>
				<span class="status-{{$prod.status}}">{{$prod.status_t}}</span>
				<br>
				<span>{{$prod.age}}岁</span>

				<span>{{$prod.gender_t}}</span>
				<span>{{$prod.height_t}}</span>
				<span>{{$prod.weight_t}}</span>
				<span>{{$prod.education_t}}</span>
				<span>{{$prod.profession_t}}</span>
				<span>{{$prod.scope_t}}</span>
				<span>{{$prod.income_t}}</span>
				<span>{{$prod.estate_t}}</span>
				<span>{{$prod.car_t}}</span>
				<span>{{$prod.smoke_t}}</span>
				<span>{{$prod.alcohol_t}}</span>
				<span>{{$prod.diet_t}}</span>
				<span>{{$prod.rest_t}}</span>
				<span>{{$prod.fitness_t}}</span>
				<span>{{$prod.belief_t}}</span>
				<span>{{$prod.pet_t}}</span>
				<span>{{$prod.intro}}</span>
				<span>{{$prod.interest}}</span>
			</td>
			<td>
			</td>
			<td>
				<a href="javascript:;" class="modU btn btn-outline btn-primary btn-xs" cid="{{$prod.id}}">修改用户</a>
				<div class="btn-divider"></div>
				<a href="javascript:;" class="delU btn btn-outline btn-danger btn-xs" cid="{{$prod.id}}">删除用户</a>
				<h5>更新于{{$prod.updatedon|date_format:'%Y-%m-%d %H:%M'}}</h5>
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
			//delUser(aId);
		}, function () {
		});
	});

	function delUser(aId) {
//		$.post("/api/user", {
//			tag: "del-admin",
//			id: aId
//		}, function (resp) {
//			if (resp.code == 0) {
//				layer.msg(resp.msg);
//				location.reload();
//			}
//		}, "json");
	}

	$("a.modU").click(function () {
		var cid = $(this).attr("cid");
		location.href = "/site/account?id=" + cid;
	});
</script>

{{include file="layouts/footer.tpl"}}