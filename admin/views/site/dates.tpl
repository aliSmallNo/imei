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

	.status {
		background: #0f9d58;
		color: #fff;
		display: inline-block;
		font-size: 12px;
		padding: 3px 8px;
		border-radius: 3px;
	}

	td img {
		width: 64px;
		height: 64px;
	}
</style>
<div class="row">
	<h4>用户约会列表</h4>
</div>
<form action="/site/date" class="form-inline">
	<input class="form-control" placeholder="用户名称" name="name"
				 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
	<input class="form-control" placeholder="用户手机" name="phone"
				 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
	<select class="form-control" name="st">
		<option value="">-=请选择约会状态=-</option>
		{{foreach from=$relations key=key item=item}}
		<option value="{{$key}}"
						{{if isset($getInfo["st"]) && $getInfo["st"]==$key}}selected{{/if}}>{{$item}}</option>
		{{/foreach}}
	</select>
	<button class="btn btn-primary">查询</button>
</form>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th class="col-sm-1">
				头像
			</th>
			<th class="col-sm-1">
				用户
			</th>
			<th class="col-sm-3">
				文字描述
			</th>
			<th class="col-sm-1">
				头像
			</th>
			<th class="col-sm-1">
				用户
			</th>
			<th class="col-sm-3">
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
				<div class="note">时间:{{$item.dDate|date_format:'%Y-%m-%d'}}</div>
				<div class="note">地点:{{$item.dLocation}}</div>
			</td>
			<td class="modMp" data-id="{{$item.right.id}}" data-name="{{$item.right.name}}">
				<img src="{{$item.right.avatar}}">
			</td>
			<td>
				{{$item.right.name}}<br>
				{{$item.right.phone}}
			</td>
			<td>
				<span class="co status">{{$item.sText}}</span><br>
				<span class="co">约会说明:{{$item.dTitle}}</span><br>
				<span class="co" style="display: none">自我介绍:{{$item.dIntro}}</span><br>
			</td>
			<td>
				{{$item.dAddedOn}}
			</td>
		</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
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