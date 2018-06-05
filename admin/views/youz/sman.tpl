{{include file="layouts/header.tpl"}}

<div class="row">
	<h4>反馈消息列表</h4>
</div>
<div class="row">
	<form action="/youz/sman" method="get" class="form-inline">

		<div class="form-group">
			<input class="form-control" placeholder="严选师名称" type="text" name="name"
						 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
			<input class="form-control" placeholder="严选师手机" type="text" name="phone"
						 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
		</div>
		<input class="form-control" placeholder="邀请方名称" type="text" name="sname"
					 value="{{if isset($getInfo['sname'])}}{{$getInfo['sname']}}{{/if}}"/>
		<input class="form-control" placeholder="邀请方手机" type="text" name="sphone"
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
				严选师头像
			</th>
			<th>
				严选师信息
			</th>
			<th>
				行为
			</th>
			<th class="col-sm-1">
				邀请方
			</th>
			<th>
				邀请方信息
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
		{{foreach from=$items item=item}}
		<tr>
			<td align="center">
					<span>
						<img src="{{$item.uAvatar}}" style="width: 65px;height: 65px;">
					</span>
			</td>
			<td>
				{{$item.uName}}<br>
				{{$item.uPhone}}
			</td>
			<td>

			</td>
			<td>
				<img src="{{$item.favatar}}" style="width: 65px;height: 65px;">
			</td>
			<td>
				{{$item.fname}}<br>
				{{$item.fphone}}
			</td>
			<td>

			</td>
			<td>

			</td>
			<td>
				{{$item.uCreateOn}}
			</td>
		</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>
{{include file="layouts/footer.tpl"}}