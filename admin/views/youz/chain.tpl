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

	tr td img {
		width: 60px;
		height: 60px;
	}

	tr td div {
		font-size: 12px;
	}
</style>
<div class="row">
	<h4>用户关系链</h4>
</div>
<div class="row">
	<form action="/youz/chain" method="get" class="form-inline">
		<div class="form-group">
			<input class="form-control" placeholder="严选师名称" type="text" name="name"
						 value="{{if isset($getInfo['name'])}}{{$getInfo['name']}}{{/if}}"/>
			<input class="form-control" placeholder="严选师手机" type="text" name="phone"
						 value="{{if isset($getInfo['phone'])}}{{$getInfo['phone']}}{{/if}}"/>
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
				<li class="{{$item.cls}}" data-phone="{{$item.uPhone}}">
					<span data-phone="{{$item.uPhone}}"><i class="icon-folder-open"></i>{{$item.uPhone}}({{$item.amt}})</span>
					<em>{{$item.uName}}</em>
					<a href="javascript:;" data-tag="self">订单数:{{$item.self_order_amt}}</a>
					<a href="javascript:;" data-tag="next">下级订单数:{{$item.next_order_amt}}</a>
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
			</div>
			<div class="modal-body">
				<table class="table table-striped table-bordered">
					<thead>
					<tr>
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
						<th>
							收件人信息
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
<script>

	$sls = {
		loadflag: 0,
		phone: 0,
		page: 1,
		modal: $("#orderModal"),
		ul: $("#orderModal .modal-body tbody"),
		modal_title: $("#orderModal .modal-title"),
	};

	$(function () {
		$('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');

		$(document).on('click', '.tree li.parent_li > span', function (e) {
			var li = $(this).parent('li.parent_li');
			var children = li.find(' > ul > li');
			console.log(children.length);
			if (children.length > 0) {
				if (children.is(":visible")) {
					children.hide('fast');
					$(this).attr('title', 'Expand this branch').find(' > i').addClass('icon-plus-sign').removeClass('icon-minus-sign');
				} else {
					children.show('fast');
					$(this).attr('title', 'Collapse this branch').find(' > i').addClass('icon-minus-sign').removeClass('icon-plus-sign');
				}
			} else {
				$sls.phone = $(this).attr('data-phone');
				reload(li);
			}
			e.stopPropagation();
		});
	});

	function reload(li) {
		if ($sls.loadflag) {
			return;
		}
		$sls.loadflag = 1;
		$.post("/api/youz",
			{
				tag: 'chain_by_phone',
				phone: $sls.phone
			},
			function (resp) {
				$sls.loadflag = 0;
				if (resp.code == 0) {
					var html = Mustache.render($('#chain_tpl').html(), resp.data)
					li.append(html);
				} else {
					layer.msg(resp.msg);
				}
			}, "json");
	}

	$(document).on("click", "a[data-tag]", function () {
		$sls.page = 1;
		var self = $(this);
		$sls.phone = self.closest("li").attr('data-phone');
		$sls.flag = self.attr('data-tag');
		var flag_text = {self: '', next: '下家'};
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
			},
			function (resp) {
				$sls.loadflag = 0;
				if (resp.code == 0) {
					var html = Mustache.render($('#order_tpl').html(), resp.data)
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
		<li class="{[cls]}" data-phone="{[uPhone]}">
			<span data-phone="{[uPhone]}"><i class="icon-leaf"></i>{[uPhone]}({[amt]})</span>
			<em>{[uName]}</em>
			<a href="javascript:;" data-tag="self">订单数:{[self_order_amt]}</a>
			<a href="javascript:;" data-tag="next">下级订单数:{[next_order_amt]}</a>
		</li>
		{[/data]}
	</ul>
</script>


<script type="text/html" id="order_tpl">
	{[#data]}
	<tr>
		<td>
			<img src="{[_pic_path]}">
		</td>
		<td>
			<div>{[_title]}</div>
			<div>
				{[#_sku_properties_name]}
				{[k]}:{[v]}
				{[/_sku_properties_name]}
			</div>
		</td>
		<td>
			<div>单价/数量:{[o_price]}({[o_num]}件)</div>
			<div>实付金额:{[o_payment]}</div>
		</td>
		<td>
			<div>{[status_str]}</div>
		</td>
		<td>
			<div>{[o_created]}</div>
		</td>
		<td>
			<div>{[uName]}</div>
			<div>{[o_receiver_name]}</div>
			<div>{[o_receiver_tel]}</div>
		</td>
	</tr>
	{[/data]}
</script>

{{include file="layouts/footer.tpl"}}