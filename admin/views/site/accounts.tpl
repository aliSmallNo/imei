{{include file="layouts/header.tpl"}}
<style>
	.pInfo span {
		font-size: 12px;
		border: 1px solid #f491b2;
		padding: 0 3px;
		line-height: 17px;
		border-radius: 3px;
		color: #f491b2;
		display: inline-block;
		margin: 3px 1px;
	}

	.pInfo span:empty {
		display: none;
	}

	.pInfo em {
		font-size: 12px;
		color: #777;
		font-style: normal;
	}

	.pInfo span.status-0 {
		color: #fff;
		border: 1px solid #f90;
		background: #f90;
	}

	.pInfo .role20 {
		font-size: 12px;
		line-height: 16px;
		color: #fff;
		background: #f491b2;
		padding: 0 5px;
		border: none;
	}

	.pInfo .role10 {
		font-size: 12px;
		line-height: 16px;
		color: #fff;
		background: #a5a5a5;
		padding: 0 5px;
		border: none;
	}

	.pInfo span.status-1 {
		color: #fff;
		border: 1px solid #44b549;
		background: #44b549;
	}

	.pInfo span.sub0 {
		color: #fff;
		background: #f40;
		border: 1px solid #f40;
	}

	.pInfo span.sub1 {
		display: none;
		color: #fff;
		border: 1px solid #44b549;
		background: #44b549;
	}

	.pInfo span.status-9 {
		color: #fff;
		border: 1px solid #ddd;
		background: #ddd;
	}

	.pInfo span.status-10 {
		color: #fff;
		border: 1px solid #4d4d4d;
		background: #4d4d4d;
	}

	td h5 {
		font-size: 12px;
		font-weight: 400;
		margin: 3px 0;
	}

	.perc-wrap {
		display: -webkit-box;
		display: -webkit-flex;
		display: -ms-flexbox;
		display: flex;
	}

	.perc-bar-title {
		font-size: 12px;
		color: #f491b2;
		margin: 0;
		-webkit-box-flex: 0 0 108px;
		-webkit-flex: 0 0 108px;
		-ms-flex: 0 0 108px;
		flex: 0 0 108px;
	}

	.perc-bar-wrap {
		-webkit-box-flex: 1;
		-webkit-flex: 1;
		-ms-flex: 1;
		flex: 1;
		padding-top: 6px;
	}

	.perc-bar {
		border: 1px solid #f491b2;
		width: 65%;
		height: 4px;
		border-radius: 3px
	}

	.perc-bar em {
		background: #f491b2;
		display: block;
		height: 2px;
		border-radius: 3px
	}

	.stat-item {
		margin-left: 30px;
	}

	.album-items img {
		width: 40px;
		height: 40px;
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
			<span class="stat-item">
				<span>总计:{{$stat.amt}}</span>
				<span>注册:{{$stat.reg}}</span>
				<span>帅哥:{{$stat.male}}</span>
				<span>美女:{{$stat.female}}</span>
			</span>
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
			<th class="col-sm-2">
				相册
			</th>
			<th class="col-sm-1">
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
				<span class="role{{$prod.role}}">{{$prod.role_t}}</span> {{$prod.name}}
				<em>{{$prod.phone}} {{$prod.wechatid}} {{$prod.location_t}}</em>
				<em style="display: none">{{$prod.note_t}}</em>
				<span class="sub{{$prod.subscribe}}">{{if $prod.subscribe}}已关注{{else}}未关注{{/if}}</span>
				<span class="status-{{if $prod.note_t}}10{{else}}{{$prod.status}}{{/if}}">{{if $prod.note_t}}{{$prod.note_t}}{{else}}{{$prod.status_t}}{{/if}}</span>
				<span class="status-1">{{if $prod.certstatus==2}}{{$prod.certstatus_t}}{{/if}}</span>
				<br>
				<div class="perc-wrap">
					<div class="perc-bar-title">资料完整度 <b>{{$prod.percent}}%</b></div>
					<div class="perc-bar-wrap"><p class="perc-bar"><em style="width: {{$prod.percent}}%"></em></p></div>
				</div>
				<span>{{$prod.age}}</span>
				<span>{{$prod.horos_t}}</span>
				<span>{{$prod.gender_t}}</span>
				<span>{{$prod.height_t}}</span>
				<span>{{$prod.weight_t}}</span>
				<span>{{$prod.education_t}}</span>
				<span>{{$prod.scope_t}}</span>
				<span>{{$prod.profession_t}}</span>
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
			<td class="album-items"  data-images='{{$prod.showImages}}'>
				{{if $prod.album}}
				{{foreach from=$prod.album item=img}}
				<img src="{{$img}}">
				{{/foreach}}
				{{/if}}
			</td>
			<td class="pInfo">
				{{foreach from=$prod.filter_t item=item}}
				<span>{{$item}}</span>
				{{/foreach}}
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

  $(document).on("click", ".album-items img", function () {
	  var self = $(this);
	  var images = self.closest("td").attr("data-images");
	  showImages(JSON.parse(images))

  });

  function showImages(imagesJson) {
	  layer.photos({
		  photos: imagesJson, //格式见API文档手册页
		  shift: 5,
//			isOutAnim: false,
//			shade: 1,
//			shadeClose: true,
		  //area: ['400px', '400px'],
	  });
  }
</script>

{{include file="layouts/footer.tpl"}}