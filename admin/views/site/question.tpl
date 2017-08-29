{{include file="layouts/header.tpl"}}
<style>

</style>
<div id="page-wrapper">
	<div class="row">
		<div class="col-sm-6">
			<h4>{{if not $userInfo}}
				添加选题
				<a href="javascript:;" class="questionAdd btn btn-outline btn-primary btn-xs">再来一题</a>
				{{else}}修改选题{{/if}}</h4>
		</div>
		<div class="col-sm-6">
			{{if $success}}
			<div class="alert alert-success alert-dismissable">
				<button type="button" class="close alert-close" data-dismiss="alert" aria-hidden="true">×</button>
				{{$success}}
			</div>
			{{/if}}
			{{if $error}}
			<div class="alert alert-danger alert-dismissable">
				<button type="button" class="close alert-close" data-dismiss="alert" aria-hidden="true">×</button>
				{{foreach from=$error item=prod}}
				{{$prod}}
				{{/foreach}}
			</div>
			{{/if}}
		</div>
	</div>

	<form action="/site/question" method="post">
		<input type="hidden" name="data" id="postData" value=''>
		<input type="hidden" name="sign" value="1">
		<div class="row qlist">
			{{foreach from=$data item=item}}
			<div class="col-sm-6 qitem">
				<div class="panel panel-default">
					<div class="panel-heading">
						<i class="fa fa-cog fa-fw"></i> 选题
						<div class="pull-right">
							<a href="javascript:;" class="optAdd btn btn-outline btn-primary btn-xs" data-index="0">增加选项</a>
							<a href="javascript:;" class="qItemDel btn btn-outline btn-danger btn-xs" data-index="0">删除此题</a>
						</div>
					</div>
					<div class="panel-body">
						<div class="col-sm-12 form-horizontal">
							<div class="form-group">
								<label class="col-sm-2 control-label">类别:</label>
								<div class="col-sm-9">
									<select data-tag="qCategory" class="form-control">
										{{foreach from=$cats key=key item=cat}}
										<option value="{{$key}}" {{if $item.cat==$key}}selected{{/if}}>{{$cat}}</option>
										{{/foreach}}
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label">题干:</label>
								<div class="col-sm-9">
									<input data-tag="qTitle" required class="form-control" value="{{$item.title}}" placeholder="(必填)">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label">选项:</label>
								<div class="col-sm-9 opt-list">
									{{if isset($item["options"])}}
									{{foreach from=$item.options item=opt}}
									<div class="input-group">
										<div class="input-group-addon">{{$opt.opt}}</div>
										<input data-option="{{$opt.opt}}" required class="form-control" value="{{$opt.text}}"
													 placeholder="(必填)">
										<div class="optDel input-group-addon btn btn-outline btn-xs">删除</div>
									</div>
									{{/foreach}}
									{{/if}}
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label">答案:</label>
								<div class="col-sm-9">
									<input data-tag="answer" required value="{{if isset($item["answer"])}}{{$item.answer}}{{/if}}" class="form-control" placeholder="(必填)">
								</div>
							</div>

						</div>
					</div>
				</div>
			</div>
			{{/foreach}}


		</div>
	</form>

	<div style="height:5em"></div>
	<div class="m-bar-bottom">
		<a href="javascript:;" class="opSave btn btn-primary" data-id="">确认保存</a>
	</div>
</div>
<script type="text/html" id="tpl_question">
	<div class="col-sm-6 qitem">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-cog fa-fw"></i> 选题
				<div class="pull-right">
					<a href="javascript:;" class="optAdd btn btn-outline btn-primary btn-xs" data-index="0">增加选项</a>
					<a href="javascript:;" class="qItemDel btn btn-outline btn-danger btn-xs" data-index="0">删除此题</a>
				</div>
			</div>
			<div class="panel-body ">
				<div class="col-sm-12 form-horizontal">
					<div class="form-group">
						<label class="col-sm-2 control-label">类别:</label>
						<div class="col-sm-9">
							<select data-tag="qCategory" class="form-control">
								{{foreach from=$cats key=key item=cat}}
								<option value="{{$key}}">{{$cat}}</option>
								{{/foreach}}
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">题干:</label>
						<div class="col-sm-9">
							<input data-tag="qTitle" required class="form-control" placeholder="(必填)">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">选项:</label>
						<div class="col-sm-9 opt-list">

							<div class="input-group">
								<div class="input-group-addon">A</div>
								<input data-option="A" required class="form-control" placeholder="(必填)">
								<div class="optDel input-group-addon btn btn-outline btn-xs">删除</div>
							</div>

						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">答案:</label>
						<div class="col-sm-9">
							<input data-tag="answer" required class="form-control" placeholder="(必填)">
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="tpl_opt">
	<div class="input-group">
		<div class="input-group-addon">{[opt]}</div>
		<input data-option="{[opt]}" required class="form-control" placeholder="(必填)">
		<div class="optDel input-group-addon btn btn-outline btn-xs">删除</div>
	</div>
</script>
<script>
	var optItems = ["A", "B", "C", "D", "E", "F", "G"];
	$(document).on("click", ".optAdd", function () {
		var self = $(this);
		var i = parseInt(self.attr("data-index")) + 1;
		if (i + 1 > optItems.length) {
			return;
		}
		self.attr("data-index", i);
		var html = Mustache.render($("#tpl_opt").html(),{opt:optItems[i]});
		self.closest(".qitem").find(".opt-list").append(html);
	});
	$(document).on("click", ".optDel", function () {
		var self = $(this);
		var iObj = self.closest(".qitem").find("[data-index]");
		var v = parseInt(iObj.attr("data-index"));
		console.log(v);
		if (v > 0) {
			self.closest(".opt-list").find(".input-group:last").remove();
			iObj.attr("data-index", v - 1)
		}
	});
	$(document).on("click", ".qItemDel", function () {
		$(this).closest(".qitem").remove();
	});

	$(".opSave").on("click", function () {
		var postData = [];
		var err = 0;
		$(".qitem").each(function () {
			err = 0;
			var self = $(this);
			var item ={}, options = [];
			var tObj = self.find("[data-tag=qTitle]");
			var title = $.trim(tObj.val());
			if (!title) {
				layer.msg("题干还没填写哦！");
				tObj.focus();
				err = 1;
				return false;
			}
			item["title"] = title;
			var cat = parseInt(self.find("[data-tag=qCategory]").val());
			item["cat"] = cat;

			if ($.inArray(cat, [100, 110]) >= 0) {
				self.find("[data-option]").each(function () {
					var opt = $(this).attr("data-option");
					var text = $.trim($(this).val());
					if (!text) {
						layer.msg("必填项不能为空！");
						err = 1;
						$(this).focus();
						return false;
					}
					var option ={opt:opt,text:text};
					options.push(option);
				});
				if (err) {
					return false;
				}
				item["options"] = options;

				var aObj = self.find("[data-tag=answer]");
				var answer = $.trim(aObj.val());
				if (!answer) {
					layer.msg("答案填写错误！");
					aObj.focus();
					err = 1;
					return false;
				}
				item["answer"] = answer;
			}
			postData.push(item);
		});

		if (err) {
			return;
		}
		//console.log(postData);
		$("#postData").val(JSON.stringify(postData));
		$("form").submit();
	});

	$(".questionAdd").on("click", function () {
		$(".qlist").append($("#tpl_question").html());
	});

	$(function () {
		if ($('.alert-success').length > 0) {
			setTimeout(function () {
				location.href = "/site/questions";
			}, 1000);
		}
	})

</script>
{{include file="layouts/footer.tpl"}}