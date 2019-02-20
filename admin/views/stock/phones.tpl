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
	<form action="/stock/phones" method="get" class="form-inline">
		<select class="form-control" name="cat">
			<option value="">请选择来源</option>
			{{foreach from=$cats item=source key=key}}
				<option value="{{$key}}" {{if $key==$cat}}selected{{/if}}>{{$source}}</option>
			{{/foreach}}
		</select>
		<input class="form-control autoW beginDate my-date-input" placeholder="开始时间" name="sdate" value="{{$st}}">
		至
		<input class="form-control autoW endDate my-date-input" placeholder="截止时间" name="edate" value="{{$et}}">
		<button class="btn btn-primary">查询</button>
		<span class="space"></span>
	</form>
</div>
<div class="row-divider"></div>
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