{{include file="layouts/header.tpl"}}
<style>
	.o-no-wrapper {
		max-height: 5.4em;
		height: 5.4em;
		overflow-x: hidden;
		overflow-y: auto;
		border: 1px solid #C8C8C8;
		border-radius: 5px;
		padding: 2px 5px;
	}
</style>
<div id="page-wrapper">
	<div class="row">
		<h4>充值记录列表</h4>
	</div>
	<div class="row">
		<form action="/site/recharges" method="get" class="form-inline">
			<div class="form-group">
				<input class="form-control" placeholder="用户名称" type="text" name="name"
							 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
			</div>
			<div class="form-group">
				<select name="orders" id="" class="form-control">
					<option value="1">按时间(默认)</option>
					<option value="2">按充值总数</option>
					<option value="3">按当前余额</option>
				</select>
			</div>
			<button class="btn btn-primary">查询</button>
			<span class="space"></span>
			<label>充值合计 <span class="text-danger">￥{{$paid|string_format:"%.2f"}}</span></label>
		</form>
	</div>

	<div class="row-divider"></div>
	<div class="row">
		<table class="table table-striped table-bordered">
			<thead>
			<tr>
				<th>
					头像
				</th>
				<th class="col-lg-3">
					用户
				</th>
				<th>
					充值金额
				</th>
				<th>
					媒瑰花
				</th>
				<th>
					充值类型
				</th>
				<th>
					充值时间
				</th>
			</tr>
			</thead>
			<tbody>
			{{foreach from=$items item=item}}
			<tr>
				<td align="center">
					<img src="{{$item.avatar}}" style="width: 65px;height: 65px;">
				</td>
				<td>{{$item.uname}}<br>
					累计充值: {{$item.amts|string_format:"%.2f"}}<br>
					当前余额: {{$item.remain|string_format:"%.2f"}}
				</td>
				<td align="right">
					{{$item.amt/100|string_format:"%.2f"}}
				</td>
				<td align="right">
					{{$item.flower}}
				</td>
				<td>
					{{$item.cat}}
				</td>
				<td>
					{{$item.date}}
				</td>
			</tr>
			{{/foreach}}
			</tbody>
		</table>
		{{$pagination}}
	</div>
</div>
{{include file="layouts/footer.tpl"}}