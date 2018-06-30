{{include file="layouts/header.tpl"}}
<style>
	.note {
		font-size: 14px;
		font-weight: 300;
	}

	.note b {
		padding-left: 2px;
		padding-right: 2px;
		font-size: 15px;
		font-weight: 500;
	}

	td img {
		width: 64px;
		height: 64px;
	}

	.tip {
		font-weight: 300;
		font-size: 13px;
	}

	.person {
		display: -ms-flexbox;
		display: flex;
		border: none;
	}

	.person .avatar {
		-ms-flex: 0 0 40px;
		flex: 0 0 40px;
		text-align: left;
	}

	.person .avatar img {
		width: 90%;
		height: auto;
		border-radius: 3px;
	}

	.person .title {
		-webkit-box-flex: 1
		-webkit-flex: 1
		-ms-flex: 1
		flex: 1
		padding-left: 10px;
	}

	.type_1, .type_3 {
		font-size: 12px;
		font-weight: 800;
		color: #777;
		padding: 0;
		background: initial
	}

	.type_3 {
		color: #0f6cf2;
		cursor: pointer;
	}

	h4 span {
		font-size: 12px;
		color: #777;
		font-weight: 400;
		padding: 10px 0;
	}
</style>
<div class="row">
	<h4>批量发货
	</h4>
</div>
<form class="form-horizontal col-sm-10" action="/youz/deliver" method="post" enctype="multipart/form-data">
	<input type="hidden" name="sign" value="sign">
	<div class="form-group">
		<label class="col-sm-5 control-label">未发货订单xls文件:</label>
		<div class="col-sm-7">
			<input type="file" name="deliver_excel" class="form-control-static" accept=".xls,.xlsx,.csv">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-5 control-label"></label>
		<div class="col-sm-7">
			<input type="submit" id="btnUpload" class="btn btn-primary" value="上传文件">
		</div>
	</div>
</form>

<script>

	var mBeginDate = $('.beginDate');
	var mEndDate = $('.endDate');
	$('.j-scope').click(function () {
		var self = $(this);
		var sdate = self.attr('data-from');
		var edate = self.attr('data-to');
		mBeginDate.val(sdate);
		mEndDate.val(edate);
		location.href = "/youz/deliver?flag=sign&sdate=" + sdate + "&edate=" + edate;
	});

</script>
{{include file="layouts/footer.tpl"}}