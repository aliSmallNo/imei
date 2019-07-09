{{include file="layouts/header.tpl"}}

<div class="row">
	<div class="col-sm-6">
		<h4>个人贡献汇总
		</h4>
	</div>
</div>
<div class="row">
	<form action="/stock/contribute_income" method="get" class="form-inline" style="display: none">
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
<div class="row">
	<ul class="nav nav-tabs">
		{{foreach from=$mouths key=key item=mouth}}
			<li class="ng-scope {{if $dt== $mouth}} active{{/if}}">
				<a href="/stock/contribute_income?dt={{$mouth}}" class="ng-binding">{{$mouth}}</a>
			</li>
		{{/foreach}}
	</ul>
</div>
<div class="row-divider"></div>
<div class="row">
	<div>个人贡献总收入：{{$sum_contribute}}</div>
</div>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th>日期</th>
			<th>用户数</th>
			<th>总借款</th>
			<th>个人贡献收入</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$list item=item}}
		<tr>
			<td>
				<a href="/stock/stock_order?dt={{$item.ym}}">{{$item.ym}}</a>
			</td>
			<td>{{$item.user_amt}}</td>
			<td>{{$item.user_loan_amt}}</td>
			<td>{{$item.contribute_amt}}</td>
		</tr>
		{{/foreach}}
		</tbody>
	</table>

</div>
<script>

</script>
{{include file="layouts/footer.tpl"}}