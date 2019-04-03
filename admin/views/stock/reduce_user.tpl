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
			上个月有操作 这个月没有操作 的用户
		</h4>
	</div>
</div>
<div class="row">
	<form action="/stock/reduce_user" method="get" class="form-inline">
		<input class="form-control autoW beginDate my-date-input" placeholder="开始时间" name="dt" value="{{$dt}}">
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
			<th>BD</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
			<tr>
				<td>{{$item.uName}}</td>
				<td>{{$item.uPhone}}</td>
				<td>{{$item.uPtName}}</td>
			</tr>
		{{/foreach}}
		</tbody>
	</table>
</div>

{{include file="layouts/footer.tpl"}}