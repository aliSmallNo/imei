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

	.pInfo span.pending {
		color: #fff;
		border: 1px solid #f80;
		background: #f80;
	}

	.pInfo span.active {
		color: #fff;
		border: 1px solid #44b549;
		background: #44b549;
	}

	.pInfo span.delete {
		color: #fff;
		border: 1px solid #ddd;
		background: #ddd;
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
			<input class="form-control" name="name" placeholder="名字" type="text" value="{{$name}}">
			<input type="submit" class="btn btn-primary" value="查询">
		</form>
	</div>
	<div class="row-divider"></div>
	<div class="row">
		<table class="table table-striped table-bordered table-hover">
			<thead>
			<tr>
				<th class="col-sm-1">
					头像
				</th>
				<th class="col-sm-6">
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
			<tr data-id="{{$prod.uId}}">
				<td>
					<img src="{{$prod.uAvatar}}" width="100%">
				</td>
				<td class="pInfo">
					{{$prod.uName}} <em>({{foreach from=$prod.uLocation item=location}}{{$location.text}}{{/foreach}})</em>
					<span class="{{if $prod.uStatus==0}}pending{{/if}}{{if $prod.uStatus==1}}active{{/if}}
					{{if $prod.uStatus==9}}delete{{/if}}">{{$prod.uStatus}}</span>
					<br>

					<span>{{$prod.uRole}}</span>
					<span>{{$prod.uGender}}</span>
					<span>{{$prod.age}}岁</span>
					<span>{{$prod.uHeight}}</span>
					<span>{{$prod.uWeight}}</span>
					<br>

					<span>{{$prod.uEducation}}</span>
					<span>{{$prod.uProfession}}</span>
					<span>{{$prod.uScope}}</span>
					<span>{{$prod.uIncome}}</span>
					<span>{{$prod.uEstate}}</span>
					<span>{{$prod.uCar}}</span>
					<br>

					<span>{{$prod.uSmoke}}</span>
					<span>{{$prod.uAlcohol}}</span>
					<span>{{$prod.uDiet}}</span>
					<span>{{$prod.uRest}}</span>
					<span>{{$prod.uFitness}}</span>
					<span>{{$prod.uBrief}}</span>
					<span>{{$prod.uPet}}</span>

					<br>
					<span>{{$prod.uIntro}}</span>
					<span>{{$prod.uInterest}}</span>
				</td>
				<td>
				</td>
				<td>
					<a href="javascript:;" class="modU btn btn-outline btn-primary btn-xs" cid="{{$prod.uId}}">修改用户</a>
					<div class="btn-divider"></div>
					<a href="javascript:;" class="delU btn btn-outline btn-danger btn-xs" cid="{{$prod.uId}}">删除用户</a>
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