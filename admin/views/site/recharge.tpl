{{include file="layouts/header.tpl"}}
<style>
	.f-tip {
		font-size: 12px;
		color: #666;
		font-weight: 300;
	}

	.uname {
		font-size: 12px;
		font-weight: 400;
	}
</style>
<div class="row">
	<h4>充值账户记录列表</h4>
</div>
<div class="row">
	<form action="/site/recharges" class="form-inline">
		<div class="form-group">
			<input class="form-control" placeholder="用户名称" name="name"
						 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}">
			<input class="form-control" placeholder="手机号" name="phone"
						 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}">
		</div>
		<div class="form-group">
			<select class="form-control" name="cat">
				<option value="">记录类型</option>
				{{foreach from=$catDict key=key item=item}}
				<option value="{{$key}}" {{if isset($getInfo['cat']) && $getInfo['cat']==$key}}selected{{/if}}>
					{{$item}}</option>
				{{/foreach}}
			</select>
		</div>
		<button class="btn btn-primary">查询</button>
	</form>
</div>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			{{foreach from=$balance item=item}}
			<th>{{$item.title}}</th>
			{{/foreach}}
		</tr>
		</thead>
		<tbody>
		<tr>
			{{foreach from=$balance item=item}}
			<td>{{$item.amt}} {{$item.unit_name}}</td>
			{{/foreach}}
		</tr>
		</tbody>
		<tfoot>
		<tr>
			<td colspan="{{$balance|@count}}"><b class="f-tip">10朵媒桂花 = 1元</b></td>
		</tr>
		</tfoot>
	</table>
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
				账户余额
			</th>
			<th>
				类型
			</th>
			<th>
				数量/金额
			</th>
			<th>
				媒桂花/金额
			</th>
			<th>
				时间
			</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$items item=item}}
		<tr>
			<td>
				<img src="{{$item.avatar}}" style="width: 65px;height: 65px;">
				<div class="uname">{{$item.uname}}<br>
					{{$item.phone}}</div>
			</td>
			<td>
				{{foreach from=$item.details key=key item=detail}}
				{{if $key=='bal'}}
				{{$detail.title}}: {{$detail.amt}}{{$detail.unit_name}}
				{{if $detail.amt2}}+{{$detail.amt2}}{{$detail.unit_name2}}{{/if}}
				{{if $detail.amt3}}+{{$detail.amt3}}{{$detail.unit_name3}}{{/if}}<br>
				{{else}}
				{{$detail.title}}: {{$detail.amt}}{{$detail.unit_name}}<br>
				{{/if}}
				{{/foreach}}
			</td>
			<td>
				{{$item.tcat}}
			</td>
			<td>
				{{if $item.amt}}￥{{$item.amt/100|string_format:"%.2f"}}{{/if}}
			</td>
			<td>
				{{$item.amt_title}}
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
{{include file="layouts/footer.tpl"}}