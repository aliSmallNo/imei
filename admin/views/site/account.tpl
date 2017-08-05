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
		<div class="col-sm-6">
			<h4>{{if not $userInfo}}添加用户{{else}}修改用户{{/if}}</h4>
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

	<form action="/site/account" method="post" enctype="multipart/form-data">
		<div class="row">
			<div class="col-sm-6 form-horizontal">
				<div class="form-group">
					<label class="col-sm-4 control-label">昵称:</label>
					<div class="col-sm-8">
						<input data-tag="uName" required class="form-control" placeholder="(必填)">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">手机:</label>
					<div class="col-sm-8">
						<input data-tag="uPhone" class="form-control" placeholder="(非必填)">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">密码:</label>
					<div class="col-sm-8">
						<input data-tag="uPassword" class="form-control" placeholder="(非必填)">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">邮件:</label>
					<div class="col-sm-8">
						<input data-tag="uEmail" class="form-control" placeholder="(非必填)">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的性别:</label>
					<div class="col-sm-8">
						<select data-tag="uGender" class="form-control">
							<option value="">请选择</option>
							{{foreach from=$gender key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">用户状态:</label>
					<div class="col-sm-8">
						<select data-tag="uStatus" class="form-control">
							{{foreach from=$status key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-4 control-label">头像:</label>
					<div class="col-sm-8 imglist">
						<input type="file" class="form-control-static inputFile" name="uAvatar[]" accept=".jpg,.jpeg,.png">
						<p class="help-block">（最好上传<b>宽480px高480px</b>的jpg图片）</p>
						<ul class="o-images desc">
							<li>
								<img src="">
							</li>
						</ul>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的出生年份:</label>
					<div class="col-sm-8">
						<select data-tag="uBirthYear" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$year key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的星座:</label>
					<div class="col-sm-8">
						<select data-tag="uHoros" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$sign key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的身高:</label>
					<div class="col-sm-8">
						<select data-tag="uHeight" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$height key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的体重:</label>
					<div class="col-sm-8">
						<select data-tag="uWeight" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$weight key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的位置:</label>
					<div class="col-sm-8">
						<div class="col-sm-6" style="padding: 0">
							<select data-location="uLocation-p" class="form-control">
								<option value="">请选择</option>
							</select>
						</div>
						<div class="col-sm-6" style="padding: 0">
							<select data-location="uLocation-c" class="form-control">
								<option value="">请选择</option>
							</select>
						</div>

					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的角色:</label>
					<div class="col-sm-8">
						<select data-tag="uRole" class="form-control">
							{{foreach from=$role key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">婚否:</label>
					<div class="col-sm-8">
						<select data-tag="uMarital" class="form-control">
							{{foreach from=$marital key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
			</div>
			<div class="col-sm-6 form-horizontal">
				<div class="form-group">
					<label class="col-sm-4 control-label">您的教育程度:</label>
					<div class="col-sm-8">
						<select data-tag="uEducation" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$edu key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的行业:</label>
					<div class="col-sm-8">
						<select data-tag="uScope" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$scope key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的职业:</label>
					<div class="col-sm-8">
						<select data-tag="uProfession" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$job key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的收入:</label>
					<div class="col-sm-8">
						<select data-tag="uIncome" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$income key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的房产:</label>
					<div class="col-sm-8">
						<select data-tag="uEstate" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$house key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您购车情况:</label>
					<div class="col-sm-8">
						<select data-tag="uCar" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$car key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的健身习惯:</label>
					<div class="col-sm-8">
						<select data-tag="uFitness" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$workout key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的饮食:</label>
					<div class="col-sm-8">
						<select data-tag="uDiet" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$diet key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的作息习惯:</label>
					<div class="col-sm-8">
						<select data-tag="uRest" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$rest key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的抽烟情况:</label>
					<div class="col-sm-8">
						<select data-tag="uSmoke" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$smoke key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的饮酒习惯:</label>
					<div class="col-sm-8">
						<select data-tag="uAlcohol" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$drink key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的信仰:</label>
					<div class="col-sm-8">
						<select data-tag="uBelief" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$belief key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-4 control-label">您养宠物吗:</label>
					<div class="col-sm-8">
						<select data-tag="uPet" class="form-control">
							<option value="0">请选择</option>
							{{foreach from=$pet key=key item=item}}
							<option value="{{$key}}">{{$item}}</option>
							{{/foreach}}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">你的兴趣爱好:</label>
					<div class="col-sm-8">
						<textarea data-tag="uInterest" required class="form-control" placeholder="(必填)"></textarea>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的内心独白:</label>
					<div class="col-sm-8">
						<textarea data-tag="uIntro" required class="form-control" placeholder="(必填)"></textarea>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">您的备注:</label>
					<div class="col-sm-8">
						<textarea data-tag="uNote" class="form-control" placeholder="(非必填)"></textarea>
					</div>
				</div>

			</div>

			<input type="hidden" data-tag="uId" name="id">
			<input type="hidden" name="data" id="postData" value=''>
			<input type="hidden" name="sign" value="1">
		</div>
	</form>

	<div style="height:5em"></div>
	<div class="m-bar-bottom">
		<a href="javascript:;" class="opSave btn btn-primary" data-id="">确认保存</a>
	</div>
</div>
<script>
	var mProvinces = {{$provinces}};
	var userInfo = {{$userInfo}};
	var mprofessions = {{$professions}};
</script>
<script>
	var $sls = {
		mProvincesObj: $("[data-location=uLocation-p]"),
		mcityObj: $("[data-location=uLocation-c]"),
		mcityVal: null,
		job: "",
		role: userInfo && userInfo.uRole ? userInfo.uRole : 10
	};

	var fieldsText = {
		"uName": "呢称",
		"uInterest": "兴趣爱好",
		"uIntro": "内心独白",
		"uLocation": "您的位置",
		"uGender": "性别",
		"uBirthYear": "出生年份",
		"uHoros": "星座",
		"uHeight": "身高",
		"uWeight": "体重",
		"uEducation": "教育程度",
		"uScope": "行业",
		"uProfession": "职业",
		"uIncome": "收入",
		"uEstate": "房产",
		"uCar": "购车情况",
		"uFitness": "健身习惯",
		"uDiet": "饮食",
		"uRest": "作息习惯",
		"uSmoke": "抽烟情况",
		"uAlcohol": "饮酒习惯",
		"uBelief": "信仰",
		"uPet": "您养宠物",
	};
	$(".opSave").on("click", function () {
		$sls.role = $("[data-tag=uRole]").val();
		var err = [];
		var postData = {};
		$("[data-tag]").each(function () {
			var self = $(this);
			var field = self.attr("data-tag");
			var val = self.val();
			var req = self.attr("required");
			if (req && !val && $sls.role == 10) {
				self.focus();
				err.push(field);
				layer.msg(fieldsText[field] + "还没填写哦~");
				return false;
			}
			postData[field] = val;
		})
		if (err.length > 0) {
			return;
		}

		if (!$(".inputFile").val() &&
			$(".inputFile").closest(".imglist").find("img").length == 0) {
			layer.msg("还没上传头像哦~");
		}

		var location = [];
		$("[data-location]").each(function () {
			var self = $(this).find("option:selected");
			var key = self.val();
			var text = self.text();
			var item = {
				key: key,
				text: text
			};
			location.push(item)
		});
		if ($sls.mcityObj.find("option:selected").text() == "请选择" && $sls.role == 10) {
			layer.msg(fieldsText["uLocation"] + "还没填写哦");
			return;
		}
		postData["uLocation"] = JSON.stringify(location);

		console.log(postData);
		$("#postData").val(JSON.stringify(postData));
		$("form").submit();
	});

	$("[data-tag=uScope]").on("change", function () {
		var val = $(this).val();
		changeScope(val);
	});

	function changeScope(val) {
		var items = [];
		for (var i = 0; i < mprofessions[val].length; i++) {
			var item = {
				key: i, name: mprofessions[val][i]
			};
			items.push(item);
		}
		var profObj = $("[data-tag=uProfession]");
		profObj.html('<option value="">请选择</option>');
		profObj.append(Mustache.render('{[#items]}<option value="{[key]}">{[name]}</option>{[/items]}',{items:items}));
		if ($sls.job != "" || $sls.job) {
			profObj.val($sls.job);
		}
	}

	$(function () {
		if ($('.alert-success').length > 0) {
			setTimeout(function () {
				location.href = "/site/accounts";
			}, 1000);
		}

		var optionTmp = '{[#items]}<option value="{[key]}">{[name]}</option>{[/items]}'
		var html = Mustache.render(optionTmp, {items: mProvinces});
		$sls.mProvincesObj.html(html);
		$sls.mProvincesObj.change(function () {
			var pid = $sls.mProvincesObj.find("option:selected").val();
			console.log(pid);
			$.post('/api/config', {
				tag: 'cities',
				id: pid
			}, function (resp) {
				if (resp.code == 0) {
					$sls.mcityObj.html(Mustache.render(optionTmp, resp.data));
					if ($sls.mcityVal) {
						$sls.mcityObj.val($sls.mcityVal)
					}
				}
			}, 'json');
		});

		console.log(userInfo);
		// 赋默认值
		$.each(userInfo, function (k, v) {
			// console.log(k);
			if (k == "uLocation" && v) {
				var location = JSON.parse(v);
				$sls.mProvincesObj.val(parseInt(location[0].key));
				$sls.mProvincesObj.trigger("change");
				$sls.mcityVal = parseInt(location[1].key)
			} else if (k == "uAvatar") {
				$(".o-images").html('<li><img src="' + v + '"></li>');
			} else if (k == "uScope") {
				$("[data-tag=" + k + "]").val(v);
				console.log(k);
				console.log(v);
				if (v) {
					changeScope(v);
				}

			} else if (k == "uProfession") {
				$sls.job = v;
				$("[data-tag=uProfession]").val(v);
			} else {
				$("[data-tag=" + k + "]").val(v);
			}
		})
	})

</script>
{{include file="layouts/footer.tpl"}}