{{include file="layouts/header.tpl"}}
<style>
	.left {
		display: inline-block;
		font-size: 12px;
		font-weight: 400;
		color: #777;
	}
</style>
<div class="row">
	<div class="col-sm-6">
		<h4>手机号列表
		</h4>
	</div>
</div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th>手机号</th>
			<th>网站</th>
			<th>时间</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
			<tr>
				<td>{{$item.oOpenId}}</td>
				<td>{{$item.st_txt}}</td>
				<td>{{$item.oAfter}}</td>
			</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>

<div class="row-divider"></div>

{{include file="layouts/footer.tpl"}}