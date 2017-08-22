{{include file="layouts/header.tpl"}}
<style>
	ul, li {
		list-style: none;
	}

	.questionlist {
		padding-left: 0;
		padding-right: 50px;
	}

	.seek-question li, .questionlist li {
		border-bottom: 1px solid #eee;
		position: relative;
	}

	.questionlist li a {
		position: absolute;
		top: 5px;
		right: 5px;
	}

	.seek-question li .title, .questionlist li .title {
		font-size: 16px;
		font-weight: 800;
	}

</style>
<div id="page-wrapper">
	<div class="row">
		<h4>添加题组</h4>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div class="col-sm-6">
				<input type="hidden" name="sign" value="sign">
				<input type="hidden" id="mpId" value="">
				<div class="form-group">
					<label>搜索题目</label>
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
					<label>活动类别</label>
					<div class="form-group input-group">
						<select name="cat" class="form-control">
							{{foreach from=$catDict key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label>选择题目</label>
					<ul class="form-control-static questionlist" id="questionlist">

					</ul>
				</div>
			</div>
			<div class="col-sm-6">
				<div class="seek-wrapper">
					<ul class="seek-question">

					</ul>
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
			var reg = /^[\u4e00-\u9fa5]+$/i;
			if (reg.test(keyWord)) {
				if (searchFlag) {
					return;
				}
				searchFlag = 1;
				layer.load();
				$.post("/api/question",
					{
						tag: "searchquestion",
						keyword: keyWord
					},
					function (resp) {
						layer.closeAll();
						searchFlag = 0;
						if (resp.code === 0) {
							$(".seek-question").html(Mustache.render($("#tpl_question").html(), resp));
						}
					}, "json");
			}
		}
	);

	$(document).on('click', ".seek-question li", function () {
		var self = $(this);
		var chooseFlag = self.attr("data-use") == "used";
		if (chooseFlag) {
			layer.msg("该题已选了~");
			return;
		}
		var title = self.find(".title").html();
		var options = self.find(".options").html();
		var answer = self.find(".answer").html();
		var tag = self.attr("tag");
		var Vhtml = Mustache.render($("#tpl_qItem").html(),{title:title,options:options,answer:answer,tag:tag});
		$("#questionlist").append(Vhtml);
		self.attr("data-use", "used");
	});
	$(document).on("click", ".delQue", function () {
		var li = $(this).closest("li")
		var tag = li.attr("tag");
		$(".seek-question").find("li[tag=" + tag + "]").attr("data-use", "");
		li.remove();
	});

	$(".opSave").click(function () {
		var ids = [];
		$(".questionlist").find("li").each(function () {
			var id = $(this).attr("tag");
			ids.push(id);
		});
		if (ids.length == 0) {
			layer.msg("至少要选择一题哦~");
			return;
		}
		$.post("/api/question", {
			tag: "savegroup",
			cat: $("[name=cat]").val(),
			ids: JSON.stringify(ids),
		}, function (resp) {
			if (resp.code == 0) {
				// location.href = "/site/net"
				layer.msg(resp.msg);
			} else {
				layer.msg(resp.msg);
			}
		}, "json")
	})

</script>

<script type="text/template" id="tpl_question">
	{[#data]}
	<li tag="{[qId]}">
		<div class="title">{[qTitle]}</div>
		<div class="options">
			{[#options]}
			{[opt]}:{[text]}
			{[/options]}
		</div>
		<div class="answer">
			答案:{[answer]}
		</div>
	</li>
	{[/data]}
	{[^data]}
	<li><b>没有找到匹配结果 (┬＿┬)</b></li>
	{[/data]}

</script>
<script id="tpl_qItem" type="text/html">
	<li tag="{[tag]}">
		<div class="title">{[title]}</div>
		<div class="options">
			{[options]}
		</div>
		<div>{[answer]}</div>
		<a class="delQue">移除</a>
	</li>
</script>
{{include file="layouts/footer.tpl"}}