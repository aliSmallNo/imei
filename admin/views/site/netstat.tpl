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

	.tip {
		font-weight: 300;
		font-size: 13px;
	}

	.person {
		display: -webkit-box;
		display: -webkit-flex;
		display: -ms-flexbox;
		display: flex;
		border: none;
	}

	.person .avatar {
		-webkit-flex: 0 0 44px;
		-ms-flex: 0 0 44px;
		flex: 0 0 44px;
		text-align: left;
	}

	.person .avatar img {
		width: 92%;
		height: auto;
	}

	.person .title {
		-webkit-box-flex: 1
		-webkit-flex: 1
		-ms-flex: 1
		flex: 1
		padding-left: 10px;
	}
</style>
<div class="row">
	<h4>推广统计</h4>
</div>
<form action="/site/netstat" class="form-inline">
	<input class="form-control beginDate my-date-input" placeholder="开始时间" name="sdate"
				 value="{{if isset($getInfo['sdate'])}}{{$getInfo['sdate']}}{{/if}}">
	至
	<input class="form-control endDate my-date-input" placeholder="截止时间" name="edate"
				 value="{{if isset($getInfo['edate'])}}{{$getInfo['edate']}}{{/if}}">
	<button class="btn btn-primary">查询</button>
	<span class="space"></span>
	<a href="javascript:;" class="j-scope" data-from="{{$today}}" data-to="{{$today}}">今天</a>
	<a href="javascript:;" class="j-scope" data-from="{{$yesterday}}" data-to="{{$yesterday}}">昨天</a>
	<a href="javascript:;" class="j-scope" data-from="{{$monday}}" data-to="{{$sunday}}">本周</a>
	<a href="javascript:;" class="j-scope" data-from="{{$firstDay}}" data-to="{{$endDay}}">本月</a>
</form>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th class="col-lg-4">
				用户
			</th>
			<!--th>
				关注数
			</th-->
			<th>
				扫推广二维码
			</th>
			<th>
				关注并注册
			</th>
			<th>
				取消关注
			</th>
			<!--th>
				注册成功
			</th-->
		</tr>
		</thead>
		<tbody>
		{{foreach from=$scanStat item=stat}}
		<tr>
			<td class="person">
				<div class="avatar">
					<img src="{{$stat.thumb}}">
				</div>
				<div class="title">
					<div>{{$stat.name}}</div>
					<div class="tip">{{$stat.phone}}</div>
				</div>
			</td>
			<!--td>
					{{$stat.focus}}
				</td-->
			<td align="right">
				{{$stat.subscribe}}
			</td>
			<td align="right">
				{{$stat.reg}}
			</td>
			<td align="right">
				{{$stat.unsubscribe}}
			</td>
			<!--td align="right">
					{{$stat.mps}}
				</td-->
		</tr>
		{{/foreach}}
		<tr>
			<td colspan="6" class="tip">1.每个人的扫码关注的数
				2.每个人关注 并注册的用户
				3.每个人注册成功（我们成为XX媒婆的数据）
				4. 取消关注用户数
			</td>
		</tr>
		</tbody>
	</table>
</div>
<script>
	var mBeginDate = $('.beginDate');
	var mEndDate = $('.endDate');
	$('.j-scope').click(function () {
		var self = $(this);
		var sdate = self.attr('data-from');
		var edate = self.attr('data-to');
		mBeginDate.val(sdate);
		mEndDate.val(edate);
		location.href = "/site/netstat?sdate=" + sdate + "&edate=" + edate;
	});
</script>
{{include file="layouts/footer.tpl"}}