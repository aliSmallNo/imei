{{include file="layouts/header.tpl"}}
<style>
	.tree {
		min-height: 20px;
		padding: 19px;
		margin-bottom: 20px;
		background-color: #fbfbfb;
		border: 1px solid #999;
		-webkit-border-radius: 4px;
		-moz-border-radius: 4px;
		border-radius: 4px;
		-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
		-moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
		box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05)
	}

	.tree li {
		list-style-type: none;
		margin: 0;
		padding: 10px 5px 0 5px;
		position: relative
	}

	.tree li::before, .tree li::after {
		content: '';
		left: -20px;
		position: absolute;
		right: auto
	}

	.tree li::before {
		border-left: 1px solid #999;
		bottom: 50px;
		height: 100%;
		top: 0;
		width: 1px
	}

	.tree li::after {
		border-top: 1px solid #999;
		height: 20px;
		top: 25px;
		width: 25px
	}

	.tree li span {
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
		border: 1px solid #999;
		border-radius: 5px;
		display: inline-block;
		padding: 3px 8px;
		text-decoration: none
	}

	.tree li.parent_li > span {
		cursor: pointer
	}

	.tree > ul > li::before, .tree > ul > li::after {
		border: 0
	}

	.tree li:last-child::before {
		height: 30px
	}

	.tree li.parent_li > span:hover, .tree li.parent_li > span:hover + ul li span {
		background: #eee;
		border: 1px solid #94a0b4;
		color: #000
	}

	[class^="icon-"], [class*=" icon-"] {
		display: inline-block;
		width: 14px;
		height: 14px;
		margin-top: 1px;
		line-height: 14px;
		vertical-align: text-top;
		background-image: url(/images/glyphicons-halflings.png);
		background-position: 14px 14px;
		background-repeat: no-repeat;
	}

	.icon-minus-sign {
		background-position: -24px -96px;
	}

	.icon-plus-sign {
		background-position: 0 -96px;
	}

	tr td img {
		width: 60px;
		height: 60px;
	}

	tr td div {
		font-size: 12px;
	}

	.font10 {
		font-size: 8px;
		color: #0f6cf2;
	}

</style>
<div class="row">
	<h4>用户关系链
	</h4>
</div>
<div class="row">
	<form action="/youz/{{if $is_partner}}chain_one{{else}}chain{{/if}}" method="get" class="form-inline">
		<div class="form-group">
			{{if !$is_partner}}
				<input class="form-control" placeholder="严选师名称" type="text" name="name"
							 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
				<input class="form-control" placeholder="严选师手机" type="text" name="phone"
							 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
			{{/if}}
			<input class="form-control beginDate my-date-input" placeholder="订单开始时间" name="sdate"
						 value="{{if isset($getInfo['sdate'])}}{{$getInfo['sdate']}}{{/if}}">
			至
			<input class="form-control endDate my-date-input" placeholder="订单截止时间" name="edate"
						 value="{{if isset($getInfo['edate'])}}{{$getInfo['edate']}}{{/if}}">
		</div>
		<button class="btn btn-primary">查询</button>
		<span class="space"></span>
	</form>
</div>

<div class="row-divider"></div>
<div class="row">
	<div class="tree well">
		<ul>
			{{foreach from=$items item=item}}
				<li class="{{$item.cls}}" data-phone="{{$item.uPhone}}" data-fans-id="{{$item.uYZUId}}">
					<span data-phone="{{$item.uPhone}}"><i class="{{$item.cls_ico}}"></i>{{$item.uPhone}}({{$item.amt}})</span>
					<em>{{$item.uname}}</em>
					<a href="javascript:;" data-tag="self" data-num="{{$item.self_order_amt}}">我的订单数:{{$item.self_order_amt}}</a>
					<a href="javascript:;" data-tag="next"
						 data-num="{{$item.next_order_amt}}">下级严选师订单数:{{$item.next_order_amt}}</a>
					<a href="javascript:;" data-tag="all" data-num="{{$item.all_order_amt}}">推广订单数:{{$item.all_order_amt}}</a>
					<strong style="display: none">支付总金额:{{$item.sum_payment}}</strong>
					{{if !$is_partner}}<a href="javascript:;" class="add_yxs_next btn btn-primary btn-xs">添加关系</a>{{/if}}
				</li>
			{{/foreach}}
		</ul>
	</div>
</div>

<div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">订单</h4>
				<p>xxxx,xxx</p>
			</div>
			<div class="modal-body">
				<table class="table table-striped table-bordered">
					<thead>
					<tr>
						<th>
							收件人信息
						</th>
						<th class="col-sm-1">
							商品
						</th>
						<th>
							商品信息
						</th>
						<th>
							订单信息
						</th>
						<th class="col-sm-1">
							状态
						</th>
						<th>
							下单时间
						</th>
					</tr>
					</thead>
					<tbody>
					<tr>

					</tr>
					</tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="yxsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body" style="overflow:hidden">
				<div class="col-sm-12 form-horizontal">

					<div class="form-group">
						<label class="col-sm-2 control-label">严选师:</label>
						<div class="col-sm-8">
							<select class="form-control" data-yxs="fans_id">
								<option value="">-=请选择=-</option>
								{{foreach from=$peak_yxs item=item }}
									<option value="{{$item.uYZUId}}">{{$item.uName}}({{$item.uPhone}})</option>
								{{/foreach}}
							</select>
						</div>
					</div>

				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-primary" data-tag="cat-chat" id="yxs_btnSave">确定保存</button>
			</div>
		</div>
	</div>
</div>
<script>

	$sls = {
		loadflag: 0,
		phone: 0,
		sdate: $(".beginDate"),
		edate: $(".endDate"),
		page: 1,
		modal: $("#orderModal"),
		stat: $("#orderModal .modal-header p"),
		ul: $("#orderModal .modal-body tbody"),
		modal_title: $("#orderModal .modal-title"),

		yxs_title: $("#yxsModal").find('.modal-title'),
		yxs_name: '',
		yxs_phone: '',
		yxs_fans_id: '',
		from_fans_id: '',
	};

	$(document).on('click', "a.add_yxs_next", function () {
		var self = $(this).closest("li");
		$sls.yxs_phone = self.attr('data-phone');
		$sls.from_fans_id = self.attr('data-fans-id');
		$sls.yxs_name = self.find('em').html();
		$sls.yxs_title.html('请选择【' + $sls.yxs_name + $sls.yxs_phone + '】的下级严选师');
		$("#yxsModal").modal("show");
	});

	$(document).on("click", "#yxs_btnSave", function () {
		var err = 0;
		var yxs_fans_id = $("[data-yxs=fans_id]").val();
		var postData = {tag: "mod_yxs_from_fansid", fans_id: yxs_fans_id, from_fans_id: $sls.from_fans_id};
		if (!yxs_fans_id) {
			layer.msg('请选择严选师');
			return;
		}
		console.log(postData);
		if ($sls.loadflag) {
			return;
		}
		$sls.loadflag = 1;
		$.post("/api/youz",
			postData,
			function (resp) {
				$sls.loadflag = 0;
				if (resp.code == 0) {
					layer.msg('已提交审核~');
					$("#yxsModal").modal("hide");
				} else {
					layer.msg(resp.msg);
				}
			}, "json");
	});

	$(function () {
		$('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');

		$(document).on('click', '.tree li.parent_li > span', function (e) {
			var self = $(this);
			var li = self.parent('li.parent_li');
			var children = li.find(' > ul > li');
			if (children.length > 0) {
				toggle_li(children, self);
			} else {
				$sls.phone = $(this).attr('data-phone');
				reload(li, self);
			}
			e.stopPropagation();
		});
	});

	function toggle_li(children, self) {
		if (children.is(":visible")) {
			children.hide('fast');
			self.find(' > i').removeClass('icon-minus-sign').addClass('icon-plus-sign');
		} else {
			children.show('fast');
			self.find(' > i').removeClass('icon-plus-sign').addClass('icon-minus-sign');
		}
	}

	function reload(li, self) {
		if ($sls.loadflag) {
			return;
		}
		$sls.loadflag = 1;
		$.post("/api/youz",
			{
				tag: 'chain_by_phone',
				phone: $sls.phone,
				sdate: $sls.sdate.val(),
				edate: $sls.edate.val(),
			},
			function (resp) {
				$sls.loadflag = 0;
				if (resp.code == 0) {
					var html = Mustache.render($('#chain_tpl').html(), resp.data)
					li.append(html);
					self.find(' > i').removeClass('icon-plus-sign').addClass('icon-minus-sign');
				} else {
					layer.msg(resp.msg);
				}
			}, "json");
	}

	$(document).on("click", "a[data-tag]", function () {
		$sls.page = 1;
		var self = $(this);
		var num = parseInt(self.attr('data-num'));
		if (!num) {
			return;
		}
		$sls.phone = self.closest("li").attr('data-phone');
		$sls.flag = self.attr('data-tag');
		var flag_text = {self: '', next: '下一级'};
		var name = self.closest("li").find('em').html();
		$sls.modal_title.html(name + flag_text[$sls.flag] + '的订单');
		order_list();
	});

	function order_list() {
		if ($sls.loadflag || !$sls.page) {
			return;
		}
		$sls.loadflag = 1;
		$.post("/api/youz",
			{
				tag: 'order_list_by_phone',
				flag: $sls.flag,
				phone: $sls.phone,
				page: $sls.page,
				sdate: $sls.sdate.val(),
				edate: $sls.edate.val(),
			},
			function (resp) {
				$sls.loadflag = 0;
				if (resp.code == 0) {
					var html = Mustache.render($('#order_tpl').html(), resp.data)
					var stat = resp.data.stat;
					$sls.stat.html("订单数:" + stat.co + " 总共支付: " + stat.payment);
					if ($sls.page == 1) {
						$sls.ul.html(html);
					} else {
						$sls.ul.append(html);
					}
					$sls.page = resp.data.nextpage;
					$sls.modal.modal('show');
				} else {
					layer.msg(resp.msg);
				}
			}, "json");
	}

	$sls.modal.on("scroll", function () {
		if ($sls.page > 0 && $sls.loadflag == 0) {
			$sls.lastRow = $sls.ul.find('tr:last');
			// console.log(eleInScreen($sls.lastRow, 300));
			if ($sls.lastRow && eleInScreen($sls.lastRow, 100) && $sls.page > 0) {
				order_list();
			}
		}
	});

	function eleInScreen($ele, $offset) {

		return $ele && $ele.length > 0 && $ele.offset().top + $offset < $sls.modal.scrollTop() + $sls.modal.height();
	}


</script>
<script type="text/html" id="chain_tpl">
	<ul>
		{[#data]}
		<li class="{[cls]}" data-phone="{[uPhone]}" data-fans-id="{[uYZUId]}">
			<span data-phone="{[uPhone]}"><i class="{[cls_ico]}"></i>{[uPhone]}({[amt]})</span>
			<em>{[uname]}</em>
			<a href="javascript:;" data-tag="self" data-num="{[self_order_amt]}">我的订单数:{[self_order_amt]}</a>
			<a href="javascript:;" data-tag="next" data-num="{[next_order_amt]}">下级严选师订单数:{[next_order_amt]}</a>
			<a href="javascript:;" data-tag="all" data-num="{[all_order_amt]}">推广订单数:{[all_order_amt]}</a>
			<strong style="display: none">支付总金额:{[sum_payment]}</strong>
			{{if !$is_partner}}<a href="javascript:;" class="add_yxs_next btn btn-primary btn-xs">添加关系</a>{{/if}}
		</li>
		{[/data]}
	</ul>
</script>


<script type="text/html" id="order_tpl">
	{[#data]}
	{[#orders]}
	<tr>
		{[#key_flag]}
		<td rowspan="{[#rowspan_flag]}{[co]}{[/rowspan_flag]}">
			<div>{[uName]}</div>
			<div>{[o_receiver_name]}</div>
			<div>{[o_receiver_tel]}</div>
		</td>
		{[/key_flag]}
		<td>
			<img src="{[pic_path]}">
		</td>
		<td>
			<div>{[title]}</div>
			<div>
				{[#sku_properties_name_arr]}
				{[k]}:{[v]}
				{[/sku_properties_name_arr]}
			</div>
			<div class="font10">
				{[o_tid]}
			</div>
		</td>
		<td>
			<div>单价/数量:{[price]}*{[num]}={[total_fee]}</div>
			<div>实付金额:{[#o_pay_time]}{[payment]}{[/o_pay_time]}{[^o_pay_time]}0.00{[/o_pay_time]}</div>
		</td>

		{[#key_flag]}
		<td rowspan="{[#rowspan_flag]}{[co]}{[/rowspan_flag]}">
			<div>{[status_str]}</div>
		</td>
		<td rowspan="{[#rowspan_flag]}{[co]}{[/rowspan_flag]}">
			<div>{[o_created]}</div>
		</td>
		{[/key_flag]}
	</tr>
	{[/orders]}
	{[/data]}
</script>

{{include file="layouts/footer.tpl"}}