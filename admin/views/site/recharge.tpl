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

	.total-row {
	}

	.total-row ul {
		margin: 0;
		padding: 0 8px;
		list-style: none;
		border-left: 1px solid #d4d4d4;
	}

	.total-row li {
		display: flex;
	}

	.total-row li em {
		font-style: normal;
		flex: 0 0 120px;
		font-weight: 400;
	}

	.total-row li b {
		font-style: normal;
		font-weight: 400;
		flex: 1;
		text-align: right;
		color: #848484;
	}

	.total-row ul:first-child {
		border-left: none;
	}

	.prefix- {
		color: #f50 !important;
	}
</style>
<div class="row">
	<h4>充值账户记录列表</h4>
</div>
<form action="/site/recharges" class="form-inline">
	<input class="form-control" placeholder="用户名称" name="name"
	       value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}">
	<input class="form-control" placeholder="手机号" name="phone"
	       value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}">
	<select class="form-control" name="cat">
		<option value="">-=请选择=-</option>
		{{foreach from=$catDict key=key item=item}}
			<option value="{{$key}}" {{if isset($getInfo['cat']) && $getInfo['cat']==$key}}selected{{/if}}>
				{{$item}}</option>
		{{/foreach}}
	</select>
	<label><input type="checkbox" name="income" value="1"
	              {{if isset($getInfo['income']) && $getInfo['income']}}checked{{/if}}> 只看收入</label>
	<button class="btn btn-primary">查询</button>
</form>
<div class="row-divider"></div>
<div class="row total-row">
	{{foreach from=$balance item=bal}}
		<ul class="col-lg-3">
			{{foreach from=$bal item=item}}
				<li><em>{{$item.title}}</em><b class="prefix{{$item.prefix}}">{{$item.amt}}{{$item.unit_name}}</b></li>
			{{/foreach}}
		</ul>
	{{/foreach}}
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
							{{if $detail.amt3}}+{{$detail.amt3}}{{$detail.unit_name3}}{{/if}}
							{{if $detail.amt4}}+{{$detail.amt4}}{{$detail.unit_name4}}{{/if}}

							<br>
						{{else}}
							{{$detail.title}}: {{$detail.amt}}{{$detail.unit_name}}
							<br>
						{{/if}}
					{{/foreach}}
				</td>
				<td>
					{{$item.tcat}}
					<div class="f-tip">{{if $item.subtitle}}({{$item.subtitle}}){{/if}}</div>
				</td>
				<td class="prefix{{$item.prefix}}">
					{{if $item.amt}}￥{{$item.amt/100.0|string_format:"%.2f"}}{{/if}}
				</td>
				<td class="prefix{{$item.prefix}}">
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