{{include file="layouts/header.tpl"}}
<style>
	.note {
		font-size: 14px;
		font-weight: 300;
	}

	.note b {
		padding-left: 2px;
		padding-right: 2px;
		font-size: 15px;
		font-weight: 500;
	}

	td img {
		width: 64px;
		height: 64px;
	}
</style>
<div id="page-wrapper">
	<div class="row">
		<h4>用户操作统计</h4>
	</div>
	<form action="/site/netstat" class="form-inline">
		<input class="form-control my-date-input" placeholder="开始时间" name="sdate"
					 value="{{if isset($getInfo['sdate'])}}{{$getInfo['sdate']}}{{/if}}"/>至
		<input class="form-control my-date-input" placeholder="截止时间" name="edate"
					 value="{{if isset($getInfo['edate'])}}{{$getInfo['edate']}}{{/if}}"/>

		<button class="btn btn-primary">查询</button>
	</form>
	<div class="row-divider"></div>
	<div class="row">
		<table class="table table-striped table-bordered">
			<thead>
			<tr>
				<th>
					用户
				</th>
				<th>
					扫码数
				</th>
				<th>
					扫码关注数
				</th>
				<th>
					取消关注
				</th>
				<th>
					关注并注册
				</th>
				<th>
					注册成功
				</th>

			</tr>
			</thead>
			<tbody>
			{{foreach from=$scanStat item=stat}}
			<tr>
				<td>
					{{$stat.name}}
				</td>
				<td>
					{{$stat.scan}}
				</td>
				<td>
					{{$stat.subscribe}}
				</td>
				<td>
					{{$stat.unsubscribe}}
				</td>
				<td>
					{{$stat.reg}}
				</td>
				<td>
					{{$stat.mps}}
				</td>
			</tr>
			{{/foreach}}
			<tr>
				<td colspan="6" style="font-size: 12px;color: #777">1.每个人的扫码的数
					2.每个人的扫码关注的数
					3.每个人关注 并注册的用户
					4.每个人注册成功（我们成为XX媒婆的数据）
					5. 取消关注用户数</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>

<script>

</script>
{{include file="layouts/footer.tpl"}}