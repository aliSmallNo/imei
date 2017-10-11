{{include file="layouts/header.tpl"}}
<style>
	.note {
		font-size: 14px;
		font-weight: 400;
	}

	.sm {
		font-size: 14px;
		font-weight: 300;
	}

	.note b {
		padding-left: 2px;
		padding-right: 2px;
		font-size: 15px;
		font-weight: 400;
	}

	.note i {
		font-size: 13px;
		font-weight: 300;
		font-style: normal;
	}

	td img {
		width: 64px;
		height: 64px;
	}
</style>
<div class="row">
	<h4>评论列表</h4>
</div>
<form action="/site/comments" class="form-inline">
	<input class="form-control" placeholder="用户名称" name="name"
				 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
	<input class="form-control" placeholder="用户手机" name="phone"
				 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
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
			<th class="col-sm-2">
				用户
			</th>
			<th class="col-sm-1">
				类型
			</th>
			<th class="col-sm-3">
				内容
			</th>
			<th class="col-sm-1">
				头像
			</th>
			<th class="col-sm-2">
				用户
			</th>
			<th>
				审核
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
		<tr>
			<td align="center">
				<img src="{{$item.avatar1}}">
			</td>
			<td>
				{{$item.name1}}<br>
				{{$item.phone1}}
			</td>
			<td>
				{{$item.cat}}
			</td>
			<td>
				<div class="note"><i>{{$item.dt}}</i><br></span>{{$item.cComment}}</div>
			</td>
			<td>
				<img src="{{$item.avatar2}}">
			</td>
			<td>
				{{$item.name2}}<br>
				{{$item.phone2}}
			</td>
			<td>
				{{if $item.cStatus==0}}
				<a href="javascript:;" class="btn btn-outline btn-primary btn-xs">审核通过</a>
				{{else}}
				<div class="note">审核时间<br>{{$item.cStatusDate}}</div>
				{{/if}}
			</td>
		</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>
{{include file="layouts/footer.tpl"}}