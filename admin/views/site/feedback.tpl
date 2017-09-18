{{include file="layouts/header.tpl"}}

<div class="row">
	<h4>反馈消息列表</h4>
</div>
<div class="row">
	<form action="/site/feedback" method="get" class="form-inline">
		<div class="form-group">
			<select class="form-control" name="cat">
				<option value="">行为类型</option>
				{{foreach from=$cats key=key item=item}}
				<option value="{{$key}}"
								{{if isset($getInfo["cat"]) && $getInfo["cat"]==$key}}selected{{/if}}>{{$item}}</option>
				{{/foreach}}
			</select>
		</div>
		<div class="form-group">
			<input class="form-control" placeholder="反馈者名称" type="text" name="name"
						 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
			<input class="form-control" placeholder="反馈者手机" type="text" name="phone"
						 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
		</div>
		<input class="form-control" placeholder="被举报者名称" type="text" name="sname"
					 value="{{if isset($getInfo['sname'])}}{{$getInfo['sname']}}{{/if}}"/>
		<input class="form-control" placeholder="被举报者手机" type="text" name="sphone"
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
				反馈者头像
			</th>
			<th>
				反馈者信息
			</th>
			<th>
				行为
			</th>
			<th class="col-sm-1">
				被举报者
			</th>
			<th>
				被举报者信息
			</th>
			<th class="col-sm-2">
				举报原因
			</th>
			<th class="col-sm-2">
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
					<span>
						<img src="{{$item.iavatar}}" style="width: 65px;height: 65px;">
					</span>
			</td>
			<td>
				{{$item.iname}}<br>
				{{$item.iphone}}
			</td>
			<td>
				{{$item.catDict}}
			</td>
			<td>
				{{if {{$item.uavatar}}}}
				<img src="{{$item.uavatar}}" style="width: 65px;height: 65px;">
				{{/if}}
			</td>
			<td>
				{{$item.uname}}<br>
				{{$item.uphone}}
			</td>
			<td>
					<span class="co">
					{{$item.fReason}}<br>
					</span>
			</td>
			<td>
				{{$item.fNote}}
			</td>
			<td>
				{{$item.fDate}}
			</td>
		</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>
{{include file="layouts/footer.tpl"}}