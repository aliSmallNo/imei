<link rel="stylesheet" href="/css/summary.css?v={{#gVersion#}}">
<div id="page-wrapper">
	<div class="row" style="display: none">
		<h4>账户概览
		</h4>
	</div>
	<div class="row data-box" style="display: none">
		<div class="data-hd">
			<h3>基本信息</h3>
		</div>
		<div class="data-bd">
			<div class="form mini-space">
				<div class="form-group">
					<div class="form-item">
						<label class="label">登录ID</label>

						<div class="element">
							<span class="txt-box">{{$adminInfo.aName}}</span>
						</div>
					</div>
					<div class="form-item">
						<label class="label">用户名</label>

						<div class="element">
							<span class="txt-box">{{$adminInfo.aNote}}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row data-box">
		<div class="col-lg-9">
			<div class="data-hd" id="cChartWrapper" {{if $adminInfo.aId==1954185 || $hideChart}}style="display: none" {{/if}}>
				<h3>今天数据和趋势
					<small style="font-size: 13px">
						{{if $adminInfo.aIsBpdj==1}}
						<span class="space"></span>
						<select id="cSearchBranchId" style="font-size: 14px">
							<option value="1000">全网数据</option>
							{{foreach from=$branches key=key item=item}}
							<option value="{{$key}}" {{if $key==$branchId}}selected{{/if}} >{{$item}}</option>
							{{/foreach}}
						</select> (奔跑到家员工可选择)
						{{else}}
						<input type="hidden" id="cSearchBranchId" value="{{$branchId}}">
						{{/if}}
					</small>
				</h3>
			</div>
			<div class="chart-box" {{if $adminInfo.aId==1954185 || $hideChart}}style="display: none" {{/if}}>
				<div class="chart-tab">
					<ul class="clr">
						<li class="selected" chart-tag="amt">
							<div class="trend-remark">今天成交金额(元)</div>
							<div class="trend-amount"></div>
							<div class="trend-compare">
								<span class="gray">对比昨天 </span><i class="fa"></i><span class="m-ratio"></span>
							</div>
							<div class="trend-arrow"></div>
						</li>
						<li chart-tag="cnt">
							<div class="trend-remark">今天下单人数(人)</div>
							<div class="trend-amount"></div>
							<div class="trend-compare">
								<span class="gray">对比昨天 </span><i class="fa"></i><span class="m-ratio"></span>
							</div>
							<div class="trend-arrow"></div>
						</li>
						<li chart-tag="avg">
							<div class="trend-remark">今天人均金额(元)</div>
							<div class="trend-amount"></div>
							<div class="trend-compare">
								<span class="gray">对比昨天 </span><i class="fa"></i><span class="m-ratio"></span>
							</div>
							<div class="trend-arrow"></div>
						</li>
					</ul>
				</div>
				<div class="chart-cnt" id="hourChart">
					<div class="chart-container" id="cCharts">

					</div>
				</div>
			</div>
			<div class="data-hd" {{if $hideChart}}style="display: none" {{/if}}>
				<h3>商户钱包</h3>
			</div>
			<div class="data-bd" {{if $hideChart}}style="display: none" {{/if}}>
				<div class="form mini-space">
					<div class="form-group">
						<div class="form-item">
							<label class="label">账户余额</label>

							<div class="element">
								<span class="t-rmb">{{$balance}}</span>
							</div>
						</div>
						<div class="form-item">
							<div class="element">
								<a href="/site/mchargelist" class="btnAccount btn btn-outline btn-primary btn-sm">查看明细</a>
								<a href="javascript:;" class="btnRecharge btn btn-outline btn-danger btn-sm">钱包充值</a>
								<a href="javascript:;" class="btnCash btn btn-outline btn-success btn-sm">提现</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			{{if $adminInfo.aIsBpdj==1}}
			<div {{if $adminInfo.aIsBpdj==0}}style="display: none" {{/if}}>
				<div class="data-hd">
					<h3>加盟商分布图
						<small style="font-size: 12px">(奔跑到家员工可见)</small>
					</h3>
				</div>
				<div class="itemCon">
					<div id="ChinaMap"></div>
					<div id="stateTip"
							 style="margin-left: -207px; margin-top: -67px; position: absolute; top: 0; left: 100%;text-align: left;display: inline;"></div>
				</div>
				<div id="mapTipContent" style="width: 450px;margin: 0 auto;display: none">
					{{foreach from=$items item=prov}}
					<div class="mapTipText mapTipText{{$prov.id}}">
						<div class="mapTipImg"><img src="/images/city/{{$prov.image}}" alt=""></div>
						<div class="mapTipList">
							<h2><a href="">{{$prov.name}} <em>({{$prov.count}})</em></a></h2>
							<ul>
								{{foreach from=$prov.items item=city}}
								{{if $city.enabled==1}}
								<li><a href="/site/branch?bid={{$city.id}}">{{$city.title}}</a>
									{{else}}
								<li><span>{{$city.title}}</span>
									{{/if}}
									{{/foreach}}
							</ul>
						</div>
					</div>
					{{/foreach}}
				</div>
			</div>
			{{/if}}

			{{if isset($adminInfo.branches) && $adminInfo.branches|@count > 1 && $adminInfo.branches|@count < 3}}
			<div class="row data-box with-padding ">
				<div class="data-hd">
					<h3>可管理加盟商</h3>
				</div>

				<div class="data-bd branches-flat">
					{{if isset($adminInfo.branches)}}
					{{foreach from=$adminInfo.branches key=cKey item=cItem}}
					<a href="/site/branch?bid={{$cKey}}"
						 {{if $cKey == $adminInfo.branch}}class="text-orange"{{/if}}>{{$cItem}}{{if $cKey == $adminInfo.branch}}
						<i class="fa fa-check"></i>{{/if}}</a>
					{{/foreach}}
					{{/if}}
				</div>
			</div>
			{{/if}}
			<div class="data-hd">
				<h3>微信公众号</h3>
			</div>
			<div class="data-bd">
				<div class="micro-group">
					<p class="group-txt">扫描二维码加入微信公众号，订货版，司机版，供应商版，这些全都有啊~</p>

					<div class="group-img">
						<div><img src="/images/qr_bpdj430.jpg" alt="奔跑到家微信公众号" style="width: 130px"></div>

						<div style="padding-top: 5px">进入PC网页订货端 <a href="https://mall.bpbhd.com" target="_blank">https://mall.bpbhd.com</a></div>
					</div>
				</div>
			</div>
			<div class="row-divider"></div>
		</div>
		<div class="col-lg-3">
			<div class="data-hd">
				<h3>常用功能</h3>
			</div>
			<div class="data-bd">
				<ul class="often-menus">
					{{foreach from=$usedMenus key=k item=prod}}
					<li><a href="{{$prod.url}}">{{if isset($prod.icon)}}<i class="fa {{$prod.icon}} fa-fw"></i>{{/if}} {{$prod.name}}</a></li>
					{{/foreach}}
				</ul>
			</div>
			<div class="data-hd">
				<h3>站内信</h3>
			</div>
			<div class="data-bd">
				<ul class="mails">
					{{foreach from=$mails key=key item=mail}}
					{{if $key<10}}
					<li><a href="/mail/detail?id={{$mail.secretid}}" title="{{$mail.brief}}"><span class="time">[{{$mail.briefDate}}]</span> <span
											class="name">{{$mail.brief}}</span><i class="fa fa-envelope-o"></i></a></li>
					{{/if}}
					{{/foreach}}
				</ul>
				{{if $mails|@count==0}}
				<div class="text-muted" style="text-align: center; font-size: 13px">暂无站内信</div>
				{{/if}}
				<p class="mails-more"><a href="/mail/list" title="更多">更多&gt;&gt;</a></p>
			</div>

		</div>
	</div>
</div>
<div class="modal fade" id="modModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">商户充值</h4>
			</div>
			<div class="modal-body">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
				<button type="button" class="btn btn-primary" id="btnSaveMod">马上充值</button>
			</div>
		</div>
	</div>
</div>
<input type="hidden" id="cBranchId" value="{{$branchId}}">
<script src="/js/highcharts/highcharts.js"></script>
<script src="/js/raphael.min.js"></script>
<script src="/js/chinaMapConfig.js?v=1.1.1"></script>
<script src="/js/map-min.js"></script>
<script type="text/html" id="cRechargeTmp">
	<div class="m-wallet-wrap">
		<div class="title">请选择充值金额</div>
		<ul>
			<li class="active">
				<div><em>300</em>元</div>
			</li>
			<li>
				<div><em>500</em>元</div>
			</li>
			<li>
				<div><em>800</em>元</div>
			</li>
			<li>
				<div><em>1000</em>元</div>
			</li>
			<li>
				<div><em>1500</em>元</div>
			</li>
			<li>
				<div><em>2000</em>元</div>
			</li>
			<li>
				<div><em>3000</em>元</div>
			</li>
			<li><input type="text" class="other-amt" placeholder="其他金额" autocomplete="off" maxlength="6"></li>
		</ul>
	</div>
</script>
<script type="text/html" id="cCashTmp">
	<div class="form-horizontal cash-wrap">
		<div class="form-group">
			<label class="col-sm-4 control-label">银行账户名:</label>
			<div class="col-sm-5">
				<input class="form-control" id="cBankUser" tag="name" required placeholder="(必填)例如：张三">
			</div>
			<div class="col-sm-3" {[^accts]}style="display: none"{[/accts]}>
				<div class="btn-group">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						选择 <span class="caret"></span>
					</button>
					<ul class="dropdown-menu bank-list" role="menu">
						{[#accts]}
						<li><a href="javascript:;" class="bank-opt">
								<b>{[name]}</b> <i>{[phone]}</i>
								<em style="display: block">{[bank]}</em>
								<div>{[acct]}</div>
							</a></li>
						{[/accts]}
					</ul>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">银行账户号:</label>

			<div class="col-sm-7">
				<input class="form-control" id="cBankNO" tag="account" required placeholder="(必填)例如：6222100099900011">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">银行名称/支行:</label>

			<div class="col-sm-7">
				<input class="form-control" id="cBankName" tag="bank" required placeholder="(必填)例如：工商银行北京花园路支行">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">联系手机号:</label>

			<div class="col-sm-7">
				<input class="form-control" id="cBankPhone" tag="phone" required placeholder="(必填)收款人的手机号，便于核实身份">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">当前余额:</label>
			<div class="col-sm-7">
				<p id="cBankBal" class="form-control-static" style="color: #f70">{[bal]}</p>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-4 control-label">提现金额:</label>

			<div class="col-sm-5">
				<input class="form-control" type="number" tag="amount" min="100" max="100000" id="cBankAmt" required placeholder="(必填)不可以超过您的余额">
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="cWXQRPayTmp">
	<div class="cBuyConf cWechatConf cWXScan" style="min-width: 22em;">
		<ul style="padding: 0; margin: 0;">
			<li style="text-align: left;">
				<img src="/images/WePayLogo.png" class="banner">
			</li>
			<li>
				<span id="qrPayDesc">{[&payDesc]}</span>
			</li>
			<li>
				<img src="data:image/png;base64,{[qrCode]}" class="qrCode">
			</li>
			<li>
				<img src="/images/wePayDesc.png" class="tips">
			</li>
		</ul>
		<div style="height: 1px; background-color: #ddd;margin: 18px 0 2px 0; "></div>
	</div>
</script>
<script type="text/html" id="cHourData">
	{{$hourData}}
</script>

{{if $adminInfo.aIsBpdj==1}}
<script>
	$(function () {
		$('#ChinaMap').SVGMap({
			mapWidth: 550,
			mapHeight: 400
		});
	});
</script>
{{/if}}

<script>
	var mLoadedFlag = false;
	var mChartData = null;
	var mTitles = {
		amt: "成交金额",
		cnt: "成交笔数",
		avg: "人均金额"
	};
	$(document).on("click", "li[chart-tag]", function () {
		var self = $(this);
		if (!mChartData) {
			return;
		}
		var tabs = self.closest("ul");
		tabs.find("li").removeClass("selected");
		self.addClass("selected");
		cleanup();
		var tag = self.attr("chart-tag");
		initChart("cCharts", mChartData[tag], mTitles[tag]);
	});

	function reloadData(cData) {
		if ($("#cChartWrapper").css("display") == "none") {
			return;
		}
		cleanup();
		if (cData) {
			mChartData = cData;
			initChart("cCharts", mChartData["amt"], mTitles["amt"]);
			initAmount();
			return;
		}
		var bId = $("#cSearchBranchId").val();
		layer.load();
		$.post("/api/bigdata/chart",
			{
				tag: "hourly",
				bId: bId,
				dt: ""
			},
			function (resp) {
				layer.closeAll();
				if (resp.code == 0) {
					mChartData = resp.data;
					initChart("cCharts", mChartData["amt"], mTitles["amt"]);
					initAmount();
					mLoadedFlag = true;
				}
			}, "json");
	}

	function initAmount() {
		var tabs = $("ul.clr");
		tabs.find("li").removeClass("selected");
		tabs.find("li").eq(0).addClass("selected");

		var fields = ["amt", "cnt", "avg"];
		for (var k = 0; k < fields.length; k++) {
			var field = fields[k];
			var row = $("li[chart-tag=" + field + "]");
			row.find(".trend-amount").html(mChartData["cur-" + field]);
			row.find(".gray").html("对比昨天 " + mChartData["last-" + field]);
			var ratio = mChartData["ratio-" + field];
			var cls = ratio > 0 ? "fa-arrow-up" : "fa-arrow-down";
			var divCls = ratio > 0 ? "m-green" : "m-red";
			ratio = Math.abs(ratio);
			row.find(".m-ratio").html(ratio + "%");
			row.find("i").removeClass("fa-arrow-down").removeClass("fa-arrow-up").addClass(cls);
//			row.find("i").removeClass("fa-arrow-up");
//			row.find("i").addClass(cls);
			row.find(".trend-compare").removeClass("m-green").removeClass("m-red").addClass(divCls);
//			row.find(".trend-compare").removeClass("m-red");
//			row.find(".trend-compare").addClass(divCls);
		}
	}

	function cleanup() {
		$("#cCharts").html("");
	}

	var dates = [], todayData = [], yesterdayData = [];
	function initChart(pid, data, title) {
		dates = [];
		todayData = [];
		yesterdayData = [];
		for (var i = 0; i < data.length; i++) {
			dates[i] = data[i]['date'];
			todayData.push([
				data[i]['date'],
				data[i]['今天']
			]);
			yesterdayData.push([
				data[i]['date'],
				data[i]['昨天']
			]);
		}

		$('#' + pid).highcharts({
			chart: {
				type: 'spline',
				marginTop: 25
			},
			title: {
				text: null
			},
			colors: ['#ccc', '#44b549'],
			tooltip: {
				shared: true,
				crosshairs: {
					width: 1,
					color: '#b8b8b8',
					dashStyle: 'Solid'
				}
			},
			xAxis: {
				type: 'category',
				tickInterval: 1,
				tickWidth: 0,
				labels: {
					rotation: -45,
					style: {
						fontSize: '10px'
					}
				},
				gridLineColor: '#e8e8e8',
				gridLineWidth: 1,
				gridLineDashStyle: 'ShortDash'
			},
			yAxis: {
				min: 0,
				title: {
					text: null
				},
				gridLineDashStyle: 'ShortDash'
			},
			plotOptions: {
				series: {
					marker: {
						states: {
							hover: {
								enabled: true,
								lineWidthPlus: 1,
								radiusPlus: 4,
								//radius: 4,
								fillColor: '#fff',
								lineColor: '#b8b8b8',
								lineWidth: 1,
							}
						},
						radius: 1,
						symbol: 'circle'
					},
					lineWidth: 3
				}
			},
			legend: {
				enabled: true,
				align: 'center',
				//verticalAlign:'middle'
			},
			series: [
				{
					name: '昨天',
					data: yesterdayData,
				},
				{
					name: '今天',
					data: todayData,
					marker: {
						states: {
							hover: {
								lineColor: '#44b549'
							}
						}
					}
				}
			]
		});
	}
		{{if $adminInfo.aIsBpdj==1}}
	$("#cSearchBranchId").on("change", function () {
		reloadData();
	});
		{{/if}}

	var mRechargeTmp = $('#cRechargeTmp').html();
	$(document).on("click", ".btnRecharge", function () {
		$('div.modal-body').html(mRechargeTmp);
		$('#myModalLabel').html('商户充值');
		$('#btnSaveMod').attr({
			tag: "recharge",
			cid: ""
		}).html("马上充值");
		$('#btnRemove').hide();
		$('#modModal').modal('show');
	});

	var mCashTmp = $('#cCashTmp').html();
	$(document).on("click", ".btnCash", function () {
		$.post("/api/branch/wallet", {
				tag: "balance"
			},
			function (resp) {
				if (resp.code == 0) {
					$(".t-rmb").html(resp.data.bal);
					var limit = resp.data.limit;
					if (resp.data.bal < limit) {
						layer.msg("余额不足" + limit + "元，不可以提现哦~");
						return;
					}
					var popupHtml = Mustache.render(mCashTmp, resp.data);
					$('div.modal-body').html(popupHtml);
					$('#myModalLabel').html('商户提现');
					$('#btnSaveMod').attr({
						tag: "withdraw",
						cid: ""
					}).html("确定提现");
					$('#btnRemove').hide();
					$('#modModal').modal('show');
				}
			}, "json");
	});

	$(document).on("click", "a.bank-opt", function () {
		var self = $(this);
		$("#cBankUser").val(self.find("b").html());
		$("#cBankName").val(self.find("em").html());
		$("#cBankNO").val(self.find("div").html());
		$("#cBankPhone").val(self.find("i").html());
	});

	$(document).on("click", ".m-wallet-wrap>ul>li", function () {
		var self = $(this);
		var ul = self.parent("ul");
		ul.find("li").removeClass("active");
		self.addClass("active");
		if (!self.find(".other-amt").length) {
			$(".other-amt").blur();
		}
	});

	var mWXPayTmp = $("#cWXQRPayTmp").html();
	$(document).on("click", "#btnSaveMod", function () {
		var self = $(this);
		var tag = self.attr("tag");
		switch (tag) {
			case "recharge":
				prePay();
				break;
			case "withdraw":
				withDraw();
				break;
		}
	});

	function withDraw() {
		var values = {
			tag: "withdraw"
		};
		var hasErr = false;
		var arr = $(".cash-wrap").find("[required]").toArray().reverse();
		$(arr).each(function () {
			var self = $(this);
			var tag = self.attr("tag");
			var val = $.trim(self.val());
			values[tag] = val;
			if (val.length == 0) {
				layer.tips("输入不能为空!", self.get(0));
				hasErr = true;
				return false;
			}
			if ((tag == "amount" || tag == "account" || tag == "phone") && !$.isNumeric(val)) {
				layer.tips("输入必须是数字，不能有空格!", self.get(0));
				hasErr = true;
				return false;
			}
			if (tag == "account" && val.length < 15) {
				layer.tips("输入的银行账号格式不正确，至少15位数字!", self.get(0));
				hasErr = true;
				return false;
			}
			if (tag == "phone" && val.length < 11) {
				layer.tips("输入手机号格式不正确，必须11位数字!", self.get(0));
				hasErr = true;
				return false;
			}
		});
		if (hasErr) {
			return false;
		}
		var cAmt = $("#cBankAmt");
		var amt = parseFloat(values["amount"]);
		var bal = parseFloat($("#cBankBal").html());
		if (amt > 100000) {
			layer.tips("每次提现金额不能超过10万，您可以多提现几次试试。", cAmt.get(0));
			return false;
		}
		if (amt > bal) {
			var msg = "提现金额不能超过余额 " + bal + "元";
			layer.tips(msg, cAmt.get(0));
			return false;
		}
		layer.load();
		$.post('/api/branch/wallet',
			values,
			function (resp) {
				if (resp.code == 0) {
					$(".t-rmb").html(resp.data.bal);
				}
				layer.closeAll();
				layer.msg(resp.msg);
				$('#modModal').modal('hide');
			}, "json");
		return true;
	}

	function prePay() {
		$(".other-amt").blur();
		var amount = 0;
		var wrapper = $(".m-wallet-wrap");
		var activeLi = wrapper.find("li.active");
		if (activeLi.length > 0) {
			amount = activeLi.find("em").text();
		}
		if (amount < 1) {
			var val = $(".other-amt").val();
			if (val && val.length > 0 && $.isNumeric(val)) {
				amount = val;
			} else {
				layer.msg("请输入正确的数字格式！");
				return;
			}
		}
		if (!amount || amount < 1) {
			layer.msg("请选择或者输入充值金额！");
			return;
		}
		if (amount > 10000) {
			layer.msg("老板，充值金额不要超过10000哦~");
			return;
		}
		$('#modModal').modal('hide');

		layer.load();

		$.post('/api/mall/prepay',
			{
				tag: "recharge",
				amt: parseInt(amount)
			},
			function (resp) {
				layer.closeAll();
				if (resp.code == 0) {
					layer.open({
						title: 0,
						shadeClose: false,
						btn: ['已经完成支付', '支付遇到问题'],
						content: Mustache.render(mWXPayTmp, resp.data),
						yes: function () {
							location.reload();
						},
						no: function () {
							location.reload();
						}
					});
				} else {
					layer.msg(resp.msg);
				}
			}, "json");
	}

	$(document).ready(function () {
		var cData = JSON.parse($("#cHourData").html());
		reloadData(cData);
	});

</script>
<script src="/js/jquery-ui.min.js"></script>