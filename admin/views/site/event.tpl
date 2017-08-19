{{include file="layouts/header.tpl"}}

<style>
	.o-images {
		margin: 0;
		padding: 0;
		list-style: none;
	}

	.o-images li {
		float: left;
		width: 50px;
		text-align: center;
		margin-right: 6px;
		position: relative;
	}

	.o-images li img {
		width: 100%;
		height: auto;
	}

</style>
<div id="page-wrapper">
	<div class="row">
		<div class="col-lg-6">
			<h4>{{if isset($entity.iId)}}修改活动{{else}}添加活动{{/if}}
			</h4>
		</div>
		<div class="col-lg-6">
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
	<form class="form-horizontal" id="editForm" method="post" enctype="multipart/form-data" action="/trade/detail">
		<div class="row">
			<input type="hidden" name="sign" value="1">
			<input type="hidden" name="iId" value="{{$queryId}}">
			<input type="hidden" id="cItems" name="cItems">
			<input type="hidden" id="cFeatures" name="cFeatures">
			<div class="col-lg-6">
				<div class="form-group">
					<label class="col-sm-3 control-label">活动名称:</label>
					<div class="col-sm-8">
							<textarea class="form-control" rows="3" name="eTitle" required
												placeholder="(必填)">{{if isset($entity.eTitle)}}{{$entity.eTitle}}{{/if}}</textarea>
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-3 control-label">开始时间:</label>

					<div class="col-sm-8">
						<input type="text" name="eDateFrom" required placeholder="(必填)" class="form-control my-date-input"
									 value="{{if isset($entity.eDateFrom)}}{{$entity.eDateFrom}}{{/if}}">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">截止时间:</label>
					<div class="col-sm-8">
						<input type="text" class="form-control my-date-input" name="eDateTo"
									 value="{{if isset($entity.eDateTo)}}{{$entity.eDateTo}}{{/if}}">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">活动地址:</label>
					<div class="col-sm-8">
						<input name="eAddress" autocomplete="off" class="form-control"
									 value="{{if isset($entity.eAddress)}}{{$entity.eAddress}}{{/if}}">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">联系人:</label>
					<div class="col-sm-8">
						<input name="eContact" placeholder="例如 卢明：13344445555"
									 autocomplete="off" class="form-control"
									 value="{{if isset($entity.eContact)}}{{$entity.eContact}}{{/if}}">
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						<i class="fa fa-cog fa-fw"></i> 设置活动规格或价格
						<div class="pull-right">
							<a href="javascript:;" class="addSpecs btn btn-outline btn-primary btn-xs">新增项</a>
						</div>

					</div>
					<div class="panel-body">
						<table class="table table-striped table-bordered">
							<thead>
							<tr>
								<th>
									活动规则
								</th>
								<th></th>
							</tr>
							</thead>
							<tbody id="specsItems">
							{{foreach from=$specs item=prod}}
							<tr data-id="">
								<td>
									<input type="text" value="{{$prod.name}}" placeholder="(必填)" class="itemName form-control">
								</td>
								<td><a href="javascript:;" class="delItem btn btn-outline btn-danger btn-xs">删除</a>
								</td>
							</tr>
							{{/foreach}}
							</tbody>
						</table>
						<p class="help-block">
							例如: 价格：1人60元，2人100元，3人120元;
						</p>
					</div>
				</div>
			</div>
			<div class="col-lg-6">

				<div class="panel panel-default">
					<div class="panel-heading">
						<i class="fa fa-cog fa-fw"></i> 详情描述
						<div class="pull-right">
							<a href="javascript:;" class="add-image btn btn-outline btn-primary btn-xs">新增图片</a>
							<a href="javascript:;" class="add-text btn btn-outline btn-primary btn-xs">新增文本</a>
						</div>
						<p class="help-block">
							提示：拖动图片或文本的左上角可以移动排序
						</p>
					</div>
					<div class="panel-body">
						<ul class="features"></ul>
					</div>
				</div>
			</div>
		</div>
	</form>
	<div style="height: 5em"></div>
	<div class="m-bar-bottom">
		<a href="javascript:;" class="opSave btn btn-primary">保存活动</a>
	</div>
</div>
<script type="text/template" id="tpl_feature_item">
	{[#items]}
	{[#image]}
	<li class="image">
		<div class="file-upload-box">
			<div class="file-wrapper">
				<input type="file" value="添加 +" name="featureImage[]" class="file-uploader" accept="image/jpg, image/jpeg">
				<input type="hidden" name="featureImageVal" value="{[val]}">
			</div>
			<div class="file-review" {[^val]}style="display: none"{[/val]}>
				<img src="{[val]}" alt="">
			</div>
			<a href="javascript:;" class="m-del"></a>
		</div>
	</li>
	{[/image]}
	{[^image]}
	<li class="text">
		<textarea name="featureText">{[val]}</textarea>
		<a href="javascript:;" class="m-del"></a>
	</li>
	{[/image]}
	{[/items]}
</script>
<script type="text/html" id="tpl_specs">
	<tr data-id="">
		<td>
			<input type="text" placeholder="(必填)" class="itemName form-control">
		</td>
		<td>
			<a href="javascript:;" class="delItem btn btn-outline btn-danger btn-xs">删除</a>
		</td>
	</tr>
</script>


<script>

	var mUploaderTmp = '<input type="file" value="添加 +" name="featureImage[]" class="file-uploader" accept="image/jpg, image/jpeg">' +
		'<input type="hidden" name="featureImageVal">';

	$(document).on("click", "li.image .m-del", function () {
		var row = $(this).closest("li");
		var input = row.find("input");
		if (input.val()) {
			input.val("");
			row.find("img").attr("src", "");
			row.find(".file-review").hide();
			row.find(".file-wrapper").html(mUploaderTmp);
		} else {
			row.remove();
		}
	});

	$(document).on("click", "li.text .m-del", function () {
		var row = $(this).closest("li");
		row.remove();
	});

	$(document).on("click", ".delItem", function () {
		if ($(this).closest("tbody").find("tr").length > 1) {
			$(this).closest("tr").remove();
		}
	});

	$(document).on("change", ".file-uploader", function () {
		var docObj = this;
		var row = $(this).closest(".file-upload-box");
		var imgObjPreview = row.find("img").get(0);
		if (docObj.files && docObj.files[0]) {
			imgObjPreview.src = window.URL.createObjectURL(docObj.files[0]);
			row.find(".file-review").show();
		} else {
			docObj.select();
			var imgSrc = document.selection.createRange().text;
			try {
				imgObjPreview.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale)";
				imgObjPreview.filters.item("DXImageTransform.Microsoft.AlphaImageLoader").src = imgSrc;
				row.find(".file-review").show();
			} catch (e) {
				layer.msg("您上传的图片格式不正确，请重新选择!");
				return false;
			}
			imgObjPreview.style.display = 'none';
			document.selection.empty();
		}
		row.find("[type=hidden]").val("");
		return false;
	});

	var mSpecsItems = $("#specsItems");
	var mSpecsTmp = $("#tpl_specs").html();
	$(".addSpecs").on("click", function () {
		mSpecsItems.append(mSpecsTmp);
		$(this).blur();
	});

	$(".opSave").on("click", function () {
		var items = getSpecs();
		if (items.length < 1) {
			layer.msg("请至少要设置一种活动规格，且必填项不能留空");
			return;
		}
		$("#cItems").val(JSON.stringify(items));
		var features = getFeatures();
		$("#cFeatures").val(JSON.stringify(features));
		layer.load();
		console.log(items);
		console.log(features);
		// $("#editForm").submit();
	});

	function getSpecs() {
		var items = [];
		$.each(mSpecsItems.find("tr"), function () {
			var row = $(this);
			var name = row.find(".itemName").val();
			// var price = row.find(".itemPrice").val();// && $.isNumeric(price)
			// var id = row.attr("data-id");
			if (name && name.length ) {
				items.push({
					name: name,
					// price: price,
				});
			}
		});
		return items;
	}

	var mFeaturesInfo = {{$stringFeatures}};
	var mFeatures = $(".features");
	var mFeatureTmp = $("#tpl_feature_item").html();
	$(".add-image").on("click", function () {
		var html = Mustache.render(mFeatureTmp, {items: [{image:1}]});
		mFeatures.append(html);
		$(this).blur();
	});

	$(".add-text").on("click", function () {
		var html = Mustache.render(mFeatureTmp, {items: [{image:0}]});
		mFeatures.append(html);
		$(this).blur();
	});

	function getFeatures() {
		var features = [];
		mFeatures.find("li").each(function () {
			var row = $(this);
			if (row.hasClass("image")) {
				features[features.length] = {
					image: 1,
					val: $.trim(row.find("[type=hidden]").val())
				};
			} else {
				features[features.length] = {
					image: 0,
					val: row.find("textarea").val()
				};
			}
		});
		return features;
	}

	$(function () {
		if ($('.alert-success').length > 0) {
			setTimeout(function () {
				// location.href = "/site/events";
			}, 600);
		}
		mFeatures.html(Mustache.render(mFeatureTmp, {items: mFeaturesInfo}));
		mFeatures.sortable({
			revert: true
		});
	});
</script>
{{include file="layouts/footer.tpl"}}