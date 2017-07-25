{{include file="layouts/header.tpl"}}
<style>
	#cResult li {
		cursor: pointer;
	}

	#cThumbC img {
		height: 100px;
		width: auto;
	}
</style>
<div id="page-wrapper">
	<div class="row">
		<h4>【{{$info.uName}}】添加媒婆</h4>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div class="col-sm-4">
				<input type="hidden" name="sign" value="sign">
				<input type="hidden" id="myId" value="{{$info.uId}}">
				<input type="hidden" id="mpId" value="">
				<div class="form-group">
					<label>搜索媒婆</label>
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
					<label>媒婆头像</label>
					<div class="form-control-static " id="cThumbC">
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="seek-wrapper">

				</div>
			</div>
		</div>
	</div>

	<div style="height:4em"></div>
	<div class="m-bar-bottom">
		<a href="javascript:;" class="opSave btn btn-primary">确定保存</a>
	</div>
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
				keyword: keyWord
			},
			function (resp) {
				layer.closeAll();
				searchFlag = 0;
				if (resp.code === 0) {
					$(".seek-wrapper").html(Mustache.render($("#tpl_goods").html(), resp));
				}
			}, "json");
//		}
	});

	$(document).on('click', 'li[tag]', function () {
		var self = $(this);
		$("#searchName").val(self.find('b').text());
		$("#cThumbC").html(self.find('.img').html());
		$("#mpId").val(self.attr('tag'));
	});

	$(".opSave").click(function () {
		var uid = $("#mpId").val();
		var subuid = $("#myId").val();
		if (!uid || !subuid) {
			layer.msg("请选择一个媒婆");
			return;
		}
		$.post("/api/user", {
			tag: "savemp",
			relation: 120,
			uid: uid,
			subuid: subuid
		}, function (resp) {
			if (resp.code == 0) {
				location.href = "/site/net"
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