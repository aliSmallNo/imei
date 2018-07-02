{{include file="layouts/header.tpl"}}
<style>
	.img_sm img{
		width: 50px;
		height: 50px;
	}
</style>
<div class="row">
	<h4>严选师线索商品
		{{if $cid}}<a class="addClue btn btn-primary btn-xs">添加线索商品</a>{{/if}}
	</h4>
</div>

<div class="row-divider"></div>
<div class="row">
	<table class="table table-striped table-bordered table-hover">
		<thead>
		<tr>
			<th>地区</th>
			<th class="col-sm-2">
				姓名/手机
			</th>
			<th class="col-sm-2">
				商品名|品牌
			</th>
			<th>
				规格
			</th>
			<th class="col-sm-1">
				价格|库存
			</th>
			<th class="col-sm-2">
				图片
			</th>
			<th >
				周期
			</th>
			<th>
				时间
			</th>
		</tr>
		</thead>
		<tbody>

		{{foreach from=$items item=prod}}
			<tr>
				<td>
					{{$prod.cProvince}} - {{$prod.cCity}}
				</td>
				<td>
					{{$prod.cName}}({{$prod.cPhone}})
				</td>
				<td>
					{{$prod.gName}}<br>
					{{$prod.gBrand}}<br>
				</td>
				<td>
					{{$prod.gStandards}}<br>
				</td>
				<td>
					{{$prod.gStore}}<br>
					{{$prod.gPrice}}<br>
				</td>
				<td class="img_sm">
					{{foreach from=$prod.images item=image}}
						<img src="{{$image[1]}}">
					{{/foreach}}
				</td>
				<td >
					{{$prod.gCycle}}
				</td>
				<td>
					{{$prod.gAddedDate}}
				</td>
			</tr>
		{{/foreach}}
		</tbody>
	</table>
	{{$pagination}}

</div>

<div class="modal fade" id="modModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
									aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">分配BD信息</h4>
			</div>
			<div class="modal-body">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
				<button type="button" class="btn btn-danger" id="btnRemove">删除线索</button>
				<button type="button" class="btn btn-primary" id="btnSaveMod">确定保存</button>
			</div>
		</div>
	</div>
</div>
<script type="text/html" id="tpl_change">
	<div class="form-horizontal">
		<div class="form-group">
			<label class="col-sm-3 control-label">严选师/电话:</label>
			<div class="col-sm-7 form-control-static">
				<span class="client_name"></span> <span class="client_phone"></span>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">所属城市:</label>
			<div class="col-sm-7 form-control-static">
				<span class="client_prov"></span> <span class="client_city"></span>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">转移给:</label>
			<div class="col-sm-7">
				<select class="form-control clue_bd">
					<option value="0">放入公海</option>

					<input type="hidden" id="client_status">
			</div>
		</div>
	</div>
</script>

<input type="hidden" id="CID" value="{{$cid}}">

<script type="text/html" id="cClueTmp">
	<div class="form-horizontal">
		<div class="form-group">
			<label class="col-sm-3 control-label">商品名称:</label>
			<div class="col-sm-7">
				<input type="text" class="form-control clue_goods_name" data-tip="商品名称">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">商品品牌:</label>
			<div class="col-sm-7">
				<input type="text" class="form-control clue_goods_brand" data-tip="商品品牌">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">商品价格:</label>
			<div class="col-sm-7">
				<input type="text" class="form-control clue_goods_price" data-tip="商品价格">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">商品周期:</label>
			<div class="col-sm-7">
				<input type="text" class="form-control clue_goods_cycle" data-tip="商品周期">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">商品规格:</label>
			<div class="col-sm-7">
				<textarea class="form-control clue_goods_standards " data-tip="商品规格"></textarea>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">商品库存:</label>
			<div class="col-sm-7">
				<input type="text" class="form-control clue_goods_store" data-tip="商品库存">
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">商品图片:</label>
			<div class="col-sm-7">
				<input type="file" class=" clue_goods_image" multiple accept=".jpg,.jpeg,.png">
			</div>
		</div>

	</div>
</script>
<script type="text/html" id="jsonItems">

</script>
<script>

	$sls = {
		loadflag: 0,
	};
	$(document).on("click", "button.close", function () {
		var form = $("form");
		form.find(".form-control").val("");
		form.submit();
	});

	$(document).on('click', '#btnSaveMod', function () {
		var self = $(this);
		var formData = new FormData();
		var tag = self.attr('tag');
		var postData = null;
		var url = '/api/youz';
		console.log(tag);
		var fmap = ['clue_goods_name', 'clue_goods_brand', 'clue_goods_standards', 'clue_goods_store', 'clue_goods_cycle', 'clue_goods_price'];
		switch (tag) {
			case "yxs_clue_goods_edit":
				formData.append("tag", tag);
				postData = {
					clue_goods_name: $.trim($('.clue_goods_name').val()),
					clue_goods_brand: $.trim($('.clue_goods_brand').val()),
					clue_goods_standards: $.trim($('.clue_goods_standards').val()),
					clue_goods_store: $.trim($('.clue_goods_store').val()),
					clue_goods_cycle: $.trim($('.clue_goods_cycle').val()),
					clue_goods_price: $.trim($('.clue_goods_price').val()),
					id: self.attr("cid")
				};
				var err = false;
				$.each(postData, function (k, v) {
					var tip;
					if ($.inArray(k, fmap) > -1) {
						if (!v) {
							err = true;
							tip = $('.' + k).attr('data-tip');
							layer.msg(tip + '未填写');
							return false;
						}
					}
				});
				formData.append("data", JSON.stringify(postData));
				console.log(postData);
				if (err) {
					return;
				}

				var goods_image = $('.clue_goods_image');
				console.log(goods_image);
				if (goods_image[0].files.length) {
					if (goods_image[0].files.length > 3) {
						layer.msg('图片不可以超过三张');
						return;
					}
					$.each(goods_image[0].files, function (k, v) {
						formData.append("clue_goods_image[]", v)
					});
				} else {
					layer.msg('请选择图片');
					return;
				}
				url = '/api/youz';
				break;
		}
		if (postData) {
			if ($sls.loadFlag) {
				return;
			}
			//$sls.loadFlag = 1;
			layer.load();
			$.ajax({
				url: url,
				type: "POST",
				data: formData,
				cache: false,
				processData: false,
				contentType: false,
				success: function (resp) {
					$sls.loadFlag = 0;
					BpbhdUtil.clear();
					if (resp.code < 1) {
						console.log(resp);
						//$cr.mod.modal('hide');
					} else {
						BpbhdUtil.showMsg(resp.msg);
					}
				}
			});
		}
	});


	$(document).on('click', '.addClue', function () {
		var vHtml = $('#cClueTmp').html();
		$('div.modal-body').html(vHtml);
		$('#myModalLabel').html('添加线索商品');
		$('#btnSaveMod').attr({
			tag: "yxs_clue_goods_edit",
			cid: $("#CID").val()
		});
		$('#btnRemove').hide();
		$('#modModal').modal('show');
	});

</script>
{{include file="layouts/footer.tpl"}}