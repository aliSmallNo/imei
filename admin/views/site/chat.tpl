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
	<h4>用户聊天列表</h4>
</div>
<form action="/site/chat" class="form-inline">
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
			<th class="col-sm-3">
				用户
			</th>
			<th class="col-sm-3">
				内容
			</th>
			<th class="col-sm-1">
				头像
			</th>
			<th class="col-sm-3">
				用户
			</th>
			<th>
				详情
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
				<div class="sm">&larr;{{$item.cnt1}}句 | {{$item.cnt2}}句&rarr;</div>
				<div class="note"><i>{{$item.dt}}</i><span class="space"></span>{{$item.content}}</div>
			</td>
			<td class="modMp">
				<img src="{{$item.avatar2}}">
			</td>
			<td>
				{{$item.name2}}<br>
				{{$item.phone2}}
			</td>
			<td>
				<a href="/site/chatdes?gid={{$item.gId}}" class="btn btn-outline btn-primary btn-xs">详情</a>
			</td>
		</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>
{{include file="layouts/footer.tpl"}}