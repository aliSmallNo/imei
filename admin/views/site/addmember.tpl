{{include file="layouts/header.tpl"}}
<style>
	#cResult li {
		cursor: pointer;
	}

</style>
<div class="row">
	<h4>【{{$info.rTitle}}】添加群成员</h4>
</div>
<div class="row">
	<div class="col-sm-12">
		<div class="col-sm-4">
			<input type="hidden" id="cRID" value="{{$info.rId}}">
			<div class="form-group">
				<label>搜索用户</label>
				<div class="form-group input-group">
					<input type="text" class="form-control" name="name" id="searchName" required placeholder="(必填)">
					<span class="input-group-btn">
								<button class="btn btn-default" type="button">
									<i class="fa fa-search"></i>
								</button>
							</span>
				</div>
			</div>
			<div class="form-group">
				<label>待进群用户</label>
				<div class="form-control-static seek-wrapper" id="cThumbC">
					<ul class="seek-goods-result">

					</ul>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="seek-wrapper search-wrap">

			</div>
		</div>
	</div>
</div>

<div style="height:4em"></div>
<div class="m-bar-bottom">
	<a href="javascript:;" class="opSave btn btn-primary">确定保存</a>
</div>

<script>
	var searchFlag = 0;
	$(document).on('input', '#searchName', function () {
		var self = $(this);
		var keyWord = self.val();
//		var reg = /^[\u4e00-\u9fa5]+$/i;
//		if (reg.test(keyWord)) {
		if (searchFlag) {
			return;
		}
		searchFlag = 1;
		layer.load();
		$.post("/api/user",
			{
				tag: "searchnet",
				subtag: "dummy",
				keyword: keyWord
			},
			function (resp) {
				layer.closeAll();
				searchFlag = 0;
				if (resp.code === 0) {
					$(".search-wrap").html(Mustache.render($("#tpl_goods").html(), resp));
				}
			}, "json");
//		}
	});

	$(document).on('click', 'li[tag]', function () {
		var self = $(this);
		$("#cThumbC").find("ul").append(this);
	});

	$(".opSave").click(function () {
		var uids = [];
		$("#cThumbC ul li").each(function () {
			var self = $(this);
			var uid = self.attr("tag").trim();
			if (uid) {
				uids.push(uid);
			}
		});
		console.log(uids);
		console.log(uids.length);
		if (uids.length < 1) {
			layer.msg("请选择一个用户");
			return;
		}
		// console.log(uids);console.log(uids.length);
		if (searchFlag) {
			return;
		}
		searchFlag = 1;
		$.post("/api/room", {
			tag: "addmember",
			rid: $("#cRID").val().trim(),
			uids: JSON.stringify(uids)
		}, function (resp) {
			searchFlag = 1;
			if (resp.code == 0) {
				location.href = "/site/rooms";
			} else {
				layer.msg("参数错误~");
			}
		}, "json")
	})

</script>

<script type="text/template" id="tpl_goods">
	<ul class="seek-goods-result">
		{[#data]}
		<li tag="{[id]}">
			<div class="img"><img src="{[avatar]}"></div>
			<div class="title">
				<b>{[uname]}</b>
				<span>{[phone]}</span>
			</div>
		</li>
		{[/data]}
		{[^data]}
		<li><b>没有找到匹配结果 (┬＿┬)</b></li>
		{[/data]}
	</ul>
</script>
{{include file="layouts/footer.tpl"}}