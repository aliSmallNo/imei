{{include file="layouts/header.tpl"}}
<style xmlns="">
	td img {
		width: 64px;
		height: 64px;
	}

	td img.small {
		width: 32px;
		height: 32px;
		margin: 5px;
	}

	.st_WAIT_BUYER_PAY,
	.st_WAIT_CONFIRM,
	.st_WAIT_SELLER_SEND_GOODS,
	.st_WAIT_BUYER_CONFIRM_GOODS,
	.st_TRADE_SUCCESS,
	.st_TRADE_CLOSED {
		color: #fff;
		border-radius: 3px;
		display: inline-block;
		font-size: 10px;
		padding: 0 2px;
		border: none;
		margin: 0;
	}

	.st_WAIT_SELLER_SEND_GOODS {
		background: #ee021b;
	}

	.st_WAIT_BUYER_PAY, .st_WAIT_CONFIRM {
		background: #fbc02d;
	}

	.st_WAIT_BUYER_CONFIRM_GOODS {
		background: #953b39;
	}

	.st_TRADE_SUCCESS {
		background: #0f9d58;
	}

	.st_TRADE_CLOSED {
		background: #777;
	}

</style>
<div class="row">
	<h4>对账信息</h4>
</div>
<div class="row">
	<form action="/youz/finance" class="form-inline">
		<input class="form-control beginDate my-date-input" placeholder="开始时间" name="sdate"
					 value="{{if isset($getInfo['sdate'])}}{{$getInfo['sdate']}}{{/if}}">
		至
		<input class="form-control endDate my-date-input" placeholder="截止时间" name="edate"
					 value="{{if isset($getInfo['edate'])}}{{$getInfo['edate']}}{{/if}}">
		<select class="form-control" name="bd">
			<option value="">-=请选择=-</option>
			{{foreach from=$bds item=bd key=key}}
				<option value="{{$key}}"
								{{if isset($getInfo['bd']) && $getInfo['bd']==$key}}selected{{/if}}>{{$bd}}</option>
			{{/foreach}}
		</select>
		<button class="btn btn-primary">查询</button>
	</form>
</div>
<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th class="col-sm-1">用户信息</th>
			<th class="col-sm-1">商品图片</th>
			<th class="col-sm-2">订单信息</th>
			<th class="col-sm-2">截图信息</th>
			<th class="col-sm-2">时间</th>
		</tr>
		</thead>
		<tbody>
		{{foreach from=$items item=item}}
			<tr>
				<td>
					{{$item.od_fans_nickname}}<br>
					{{$item.od_receiver_name}}({{$item.od_receiver_tel}})
				</td>
				<td>
					<img src="{{$item.od_pic_path}}" alt="">
				</td>
				<td>
					{{$item.od_title}}<br>
					<span class="st_{{$item.od_status}}">{{$item.status_str}}</span><br>
					买家支付：{{$item.od_payment}}<br>
				</td>
				<td>
					{{$item.aName}}支付：{{$item.f_pay_amt/100}}元({{$item.f_pay_note}})<br>
					<div>
						{{foreach from=$item.pay_pic item=pic}}
							<img src="{{$pic[0]}}" bsrc="{{$pic[1]}}" class="i-av small">
						{{/foreach}}
					</div>
				</td>
				<td></td>
			</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}
</div>
<script>
	$(document).on("click", ".i-av", function () {
		var self = $(this);
		var photos = {
			title: '头像大图',
			data: [{
				src: self.attr("bsrc")
			}]
		};
		console.log(photos)
		showImages(photos);
	});

	function showImages(imagesJson, idx) {
		if (idx) {
			imagesJson.start = idx;
		}
		layer.photos({
			photos: imagesJson,
			shift: 5,
			tab: function (info) {
				console.log(info);
			}
		});
	}
</script>
{{include file="layouts/footer.tpl"}}