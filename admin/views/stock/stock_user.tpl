{{include file="layouts/header.tpl"}}

<div class="row">
	<h4>用户列表</h4>
</div>
<div class="row">
	<form action="/stock/stock_user" method="get" class="form-inline">
		<div class="form-group">
			<input class="form-control" placeholder="用户名" type="text" name="name"
						 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
			<input class="form-control" placeholder="用户手机" type="text" name="phone"
						 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
		</div>
		<button class="btn btn-primary">查询</button>
		<span class="space"></span>
	</form>
</div>

<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th>用户名</th>
			<th>手机</th>
			<th>备注</th>
			<th>时间</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
		<tr>
			<td>{{$item.uName}}</td>
			<td>{{$item.uPhone}}</td>
			<td>{{$item.uNote}}</td>
			<td>{{$item.uAddedOn}}</td>
		</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>
{{include file="layouts/footer.tpl"}}