{{include file="layouts/header.tpl"}}
<style>

</style>
<div class="row">
	<h4>有赞分销员列表</h4>
</div>
<form action="/youz/salesman" class="form-inline">
	<button class="btn btn-primary">查询</button>
</form>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th class="col-sm-1">
				手机号
			</th>
			<th class="col-sm-1">
				昵称
			</th>
			<th class="col-sm-1">
				标识码
			</th>
			<th class="col-sm-1">
				邀请方
			</th>
			<th class="col-sm-1">
				累计成交笔数
			</th>
			<th>
				累计成交金额
			</th>
			<th>
				粉丝Id
			</th>
			<th>
				加入时间
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$items item=item}}
			<tr>
				<td align="center">
					{{$item.mobile}}
				</td>
				<td>
					{{$item.nickname}}
				</td>
				<td>
					{{$item.seller}}
				</td>
				<td>
					{{$item.from_buyer_mobile}}
				</td>
				<td>
					{{$item.order_num}}
				</td>
				<td>
					{{$item.money}}
				</td>
				<td>
					{{$item.fans_id}}
				</td>
				<td>
					{{$item.created_at}}
				</td>
			</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>

{{include file="layouts/footer.tpl"}}