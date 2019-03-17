{{include file="layouts/header.tpl"}}
<style>
	.autoW {
		width: auto;
		display: inline-block;
	}

	.dt {
		font-size: 12px;
		color: #888;
		font-weight: 400;
		padding: 10px 0;
	}
</style>
<div class="row">
	<div class="col-sm-6">
		<h4>
			"再之前3天"与"最近3天"对比
			<p class="dt">{{$dts[0]}}与{{$dts[1]}}对比</p>
		</h4>
	</div>

</div>

<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th>用户名|手机</th>
			<th>再之前3天</th>
			<th>最近3天</th>
			<th>相差</th>
			<th>相差百分比</th>
			<th>状态</th>
			<th>BD</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
			<tr>
				<td>{{$item.oName}}-{{$item.oPhone}}</td>
				<td>{{$item.loan_amt}}</td>
				<td>{{$item.left_amt}}</td>
				<td>{{$item.diff_loan}}</td>
				<td>{{$item.percent}}</td>
				<td>{{$item.text}}</td>
				<td>{{$item.uPtName}}</td>
			</tr>
		{{/foreach}}
		</tbody>
	</table>
</div>

{{include file="layouts/footer.tpl"}}