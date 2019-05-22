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
		<h4>提醒短信
		</h4>
	</div>
</div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th class="col-sm-6">发送内容</th>
			<th>发送给</th>
			<th>股票(手机号_股票代码_购买数_借款金额)</th>
			<th>时间</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
			<tr>
				<td>{{$item.oAfter}}</td>
				<td>{{$item.uName}}<br>{{$item.oOpenId}}</td>
				<td>{{$item.oKey}}</td>
				<td>{{$item.oDate}}</td>

			</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>

<div class="row-divider"></div>


{{include file="layouts/footer.tpl"}}