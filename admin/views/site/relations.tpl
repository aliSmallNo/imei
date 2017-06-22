{{include file="layouts/header.tpl"}}
<style>

</style>
<div id="page-wrapper">
	<div class="row">
		<h4>用户关系列表</h4>
	</div>
	<div class="row">
		<form action="/site/net" method="get" class="form-inline">
			<div class="form-group">
				<input class="form-control" placeholder="用户(主)名称" type="text" name="name"
							 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
				<input class="form-control" placeholder="用户(主)手机" type="text" name="phone"
							 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
			</div>
			<div class="form-group">
				<select class="form-control" name="relation">
					<option value="">用户操作</option>
					{{foreach from=$relations key=key item=item}}
					<option value="{{$key}}"
									{{if isset($getInfo["relation"]) && $getInfo["relation"]==$key}}selected{{/if}}>{{$item}}</option>
					{{/foreach}}
				</select>
			</div>
			<input class="form-control" placeholder="用户(从)名称" type="text" name="sname"
						 value="{{if isset($getInfo['sname'])}}{{$getInfo['sname']}}{{/if}}"/>
			<input class="form-control" placeholder="用户(从)手机" type="text" name="sphone"
						 value="{{if isset($getInfo['sphone'])}}{{$getInfo['sphone']}}{{/if}}"/>
			<button class="btn btn-primary">查询</button>
			<span class="space"></span>
		</form>
	</div>

	<div class="row-divider"></div>
	<div class="row">
		<table class="table table-striped table-bordered">
			<thead>
			<tr>
				<th class="col-sm-1">
					头像(主)
				</th>
				<th>
					用户(主)
				</th>
				<th class="col-sm-1">
					头像(从)
				</th>
				<th>
					用户(从)
				</th>
				<th>
					操作
				</th>
				<th class="col-sm-3">
					描述
				</th>
				<th>
					时间
				</th>
			</tr>
			</thead>
			<tbody>
			{{foreach from=$list item=item}}
			<tr>
				<td align="center">
					<img src="{{$item.avatar}}" style="width: 65px;height: 65px;">
				</td>
				<td>
					{{$item.uname}}<br>
					{{$item.phone}}
				</td>
				<td>
					<img src="{{$item.savatar}}" style="width: 65px;height: 65px;">
				</td>
				<td>
					{{$item.sname}}<br>
					{{$item.sphone}}
				</td>
				<td>
					<span class="co">
					{{$item.rText}}<br>
						{{if $item.nRelation==140}}{{$item.sText}}{{/if}}
					</span>
				</td>
				<td>
					{{$item.text}}
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
{{include file="layouts/footer.tpl"}}