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
<div class="row">
	<h4>用户操作列表</h4>
</div>
<form action="/site/cut_list" class="form-inline">
	<input class="form-control" placeholder="用户名称" name="name"
				 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
	<input class="form-control" placeholder="用户手机" name="phone"
				 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
	<select class="form-control" name="key">
		<option value="">-=请选择用户操作=-</option>
		{{foreach from=$keys key=key item=item}}
		<option value="{{$key}}"
						{{if isset($getInfo["key"]) && $getInfo["key"]==$key}}selected{{/if}}>{{$item}}</option>
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
				状态
			</th>
			<th>
				日期
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
		<tr>
			<td align="center" data-id="{{$item.uid1}}">
				<img src="{{$item.thumb1}}">
			</td>
			<td>
				{{$item.name1}}<br>

			</td>
			<td>
				<div class="note">
					{{if $item.oKey==3}}
						{{$item.name2}}<b>给</b>{{$item.name1}}<b>点赞</b>
					{{/if}}
					{{if $item.oKey==1}}
						{{$item.name2}}<b>给</b>{{$item.name1}}<b>点赞</b><br>
						已领卡
					{{/if}}
					{{if $item.oKey==8}}
						领卡
					{{/if}}
				</div>
			</td>
			<td class="modMp" data-id="{{$item.uid1}}" data-name="{{$item.name2}}">
				{{if $item.thumb2}}<img src="{{$item.thumb2}}">{{/if}}
			</td>
			<td>
				{{$item.name2}}<br>

			</td>
			<td>
					<span class="co">
					{{$item.key_text}}
					</span>
			</td>
			<td>
				{{$item.oDate}}
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
		return;
		var self = $(this);
		$sls.id = self.attr("data-id");
		$sls.name = self.attr("data-name");
		$sls.src = self.find("img").attr("src");
		location.href = "/site/searchnet?id=" + $sls.id;
		//$('#modModal').modal('show');
	});
</script>
{{include file="layouts/footer.tpl"}}