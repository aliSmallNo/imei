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

	.pInfo .role20 {
		color: #44b549;
		padding: 1px 10px;
		border: 1px solid #4CAF50;
	}

	.pInfo .role10 {
		color: #666;
		padding: 1px 10px;
		border: 1px solid #aaa;
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
		margin: 3px 0;
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
				<option value="">用户状态</option>
				{{foreach from=$statusT key=key item=item}}
				<option value="{{$key}}" {{if $status!="" && $status==$key}}selected{{/if}}>{{$item}}</option>
				{{/foreach}}
			</select>
			<input class="form-control" name="name" placeholder="名字" type="text" value="{{$name}}">
			<input class="form-control" name="phone" placeholder="手机号" type="text" value="{{$phone}}">
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
			<th class="col-sm-6">
				个人信息
			</th>
			<th class="col-sm-3">
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
				<img src="{{$prod.thumb}}" width="100%">
			</td>
			<td class="pInfo">

				<span class="role{{$prod.role}}">{{$prod.role_t}}</span>
				<span class="status-{{$prod.status}}">{{$prod.status_t}}</span>
				{{$prod.name}} {{$prod.phone}} <em>({{$prod.location_t}})</em>
				<em>{{$prod.note_t}}</em>
				<br>
				{{if $prod.age}}<span>{{$prod.age}}</span>{{/if}}
				{{if $prod.horos_t}}<span>{{$prod.horos_t}}</span>{{/if}}
				{{if $prod.gender_t}}<span>{{$prod.gender_t}}</span>{{/if}}
				{{if $prod.height_t}}<span>{{$prod.height_t}}</span>{{/if}}
				{{if $prod.weight_t}}<span>{{$prod.weight_t}}</span>{{/if}}
				{{if $prod.education_t}}<span>{{$prod.education_t}}</span>{{/if}}
				{{if $prod.scope_t}}<span>{{$prod.scope_t}}</span>{{/if}}
				{{if $prod.profession_t}}<span>{{$prod.profession_t}}</span>{{/if}}
				{{if $prod.income_t}}<span>{{$prod.income_t}}</span>{{/if}}
				{{if $prod.estate_t}}<span>{{$prod.estate_t}}</span>{{/if}}
				{{if $prod.car_t}}<span>{{$prod.car_t}}</span>{{/if}}
				{{if $prod.smoke_t}}<span>{{$prod.smoke_t}}</span>{{/if}}
				{{if $prod.alcohol_t}}<span>{{$prod.alcohol_t}}</span>{{/if}}
				{{if $prod.diet_t}}<span>{{$prod.diet_t}}</span>{{/if}}
				{{if $prod.rest_t}}<span>{{$prod.rest_t}}</span>{{/if}}
				{{if $prod.fitness_t}}<span>{{$prod.fitness_t}}</span>{{/if}}
				{{if $prod.belief_t}}<span>{{$prod.belief_t}}</span>{{/if}}
				{{if $prod.pet_t}}<span>{{$prod.pet_t}}</span>{{/if}}
				{{if $prod.intro}}<span>{{$prod.intro}}</span>{{/if}}
				{{if $prod.interest}}<span>{{$prod.interest}}</span>{{/if}}
			</td>
			<td class="pInfo">
				<span>{{foreach from=$prod.filter_t.age key=key item=item}}
					{{$item.name}}{{if $key<1}}~{{/if}}{{/foreach}}</span>

				<span>{{foreach from=$prod.filter_t.height item=item}}
					{{$item.name}}~{{/foreach}}</span>

				<span>{{$prod.filter_t.income.name}}</span>
				<span>{{$prod.filter_t.edu.name}}</span>

			</td>
			<td>
				<a href="javascript:;" class="modU btn btn-outline btn-primary btn-xs" cid="{{$prod.id}}">修改用户</a>
				<div class="btn-divider"></div>
				<a href="javascript:;" class="delU btn btn-outline btn-danger btn-xs" cid="{{$prod.id}}">删除用户</a>
				<h5>创建于{{$prod.addedon|date_format:'%y-%m-%d %H:%M'}}</h5>
				<h5>更新于{{$prod.updatedon|date_format:'%y-%m-%d %H:%M'}}</h5>
			</td>
		</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>
<script>
	$("a.delU").click(function () {
		var id = $(this).attr("cid");
		layer.confirm('您确定要删除这个用户吗？', {
			btn: ['确定', '取消'],
			title: '删除用户'
		}, function () {
			delUser(id);
		}, function () {
		});
	});

	function delUser(id) {
		$.post("/api/user", {
			tag: "del-user",
			id: id
		}, function (resp) {
			if (resp.code == 0) {
				location.reload();
			}
			layer.msg(resp.msg);
		}, "json");
	}

	$("a.modU").click(function () {
		var cid = $(this).attr("cid");
		location.href = "/site/account?id=" + cid;
	});
</script>

{{include file="layouts/footer.tpl"}}