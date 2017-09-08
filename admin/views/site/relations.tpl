{{include file="layouts/header.tpl"}}
<style>
	.note {
		font-size: 14px;
		font-weight: 300;
	}

	.note b {
		padding-left: 2px;
		padding-right: 2px;
		font-size: 15px;
		font-weight: 500;
	}

	td img {
		width: 64px;
		height: 64px;
	}
</style>
<div id="page-wrapper">
	<div class="row">
		<h4>用户操作列表</h4>
	</div>
	<form action="/site/net" class="form-inline">
		<input class="form-control" placeholder="用户名称" name="name"
					 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
		<input class="form-control" placeholder="用户手机" name="phone"
					 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
		<select class="form-control" name="relation">
			<option value="">-=请选择用户操作=-</option>
			{{foreach from=$relations key=key item=item}}
			<option value="{{$key}}"
							{{if isset($getInfo["relation"]) && $getInfo["relation"]==$key}}selected{{/if}}>{{$item}}</option>
			{{/foreach}}
		</select>
		<button class="btn btn-primary">查询</button>
	</form>
	<div class="row-divider"></div>
	<div class="row">
		<table class="table table-striped table-bordered">
			<thead>
			<tr>
				<th>
					用户
				</th>
				<th>
					扫码数
				</th>
				<th>
					扫码关注数
				</th>
				<th>
					取消关注
				</th>
				<th>
					关注并注册
				</th>
				<th>
					注册成功
				</th>

			</tr>
			</thead>
			<tbody>
			{{foreach from=$scanStat item=stat}}
			<tr>
				<td>
					{{$stat.name}}
				</td>
				<td>
					{{$stat.scan}}
				</td>
				<td>
					{{$stat.subscribe}}
				</td>
				<td>
					{{$stat.unsubscribe}}
				</td>
				<td>
					{{$stat.reg}}
				</td>
				<td>
					{{$stat.mps}}
				</td>

			</tr>
			{{/foreach}}
			</tbody>
		</table>
	</div>
	<div class="row">
		<table class="table table-striped table-bordered">
			<thead>
			<tr>
				<th class="col-sm-1">
					头像
				</th>
				<th>
					用户
				</th>
				<th class="col-sm-3">
					文字描述
				</th>
				<th class="col-sm-1">
					头像
				</th>
				<th>
					用户
				</th>
				<th>
					操作
				</th>
				<th>
					日期
				</th>
			</tr>
			</thead>
			<tbody>
			{{foreach from=$list item=item}}
			<tr>
				<td align="center" data-id="{{$item.left.id}}">
					<img src="{{$item.left.avatar}}">
				</td>
				<td>
					{{$item.left.name}}<br>
					{{$item.left.phone}}
				</td>
				<td>
					<div class="note">{{$item.text}}</div>
				</td>
				<td class="modMp" data-id="{{$item.right.id}}" data-name="{{$item.right.name}}">
					<img src="{{$item.right.avatar}}">
				</td>
				<td>
					{{$item.right.name}}<br>
					{{$item.right.phone}}
				</td>
				<td>
					<span class="co">
					{{$item.rText}}<br>
						{{if $item.qcode}}{{$item.qcode}}{{/if}}
						{{if $item.nRelation==140}}{{$item.sText}}{{/if}}
					</span>
				</td>
				<td>
					{{$item.dt}}
				</td>
			</tr>
			{{/foreach}}
			</tbody>
		</table>
		{{$pagination}}
	</div>
</div>

<script>
	$sls = {
		id: '',
		name: '',
		src: ''
	};
	$(document).on('click', '.modMp', function () {
		var self = $(this);
		$sls.id = self.attr("data-id");
		$sls.name = self.attr("data-name");
		$sls.src = self.find("img").attr("src");
		location.href = "/site/searchnet?id=" + $sls.id;
		//$('#modModal').modal('show');
	});
</script>
{{include file="layouts/footer.tpl"}}