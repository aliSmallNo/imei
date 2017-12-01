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
		margin-right: 20px;
		position: relative;
	}

	.o-images.album li span {
		width: 20px;
		height: 20px;
		border: 20px;
		position: absolute;
		left: -10px;
		top: -10px;
	}

	.o-images.album li span img {
		width: 20px;
		height: 20px;
		border: 20px;
	}

	.o-images li img {
		width: 50px;
		height: 50px;
	}

	.s-openid {
		font-size: 10px;
		color: #999;
	}

	.m-loc {
		display: flex;
	}

	.m-loc select {
		flex: 1;
		padding: 0;
		margin-right: 1px;
	}
</style>
<div class="row">
	<div class="col-sm-6">
		<h4>{{if $userInfo}}
			修改用户
			<small class="s-openid">{{$openid}}</small>
			{{else}}
			添加用户
			{{/if}}</h4>
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
	<input type="hidden" name="tImagesTmp" value='' id="tImagesTmp">
	<div class="row">
		<div class="col-sm-6 form-horizontal">
			<div class="form-group">
				<label class="col-sm-4 control-label">昵称:</label>
				<div class="col-sm-8">
					<input data-tag="uName" required class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">手机:</label>
				<div class="col-sm-8">
					<input data-tag="uPhone" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">密码:</label>
				<div class="col-sm-8">
					<input data-tag="uPassword" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">邮件:</label>
				<div class="col-sm-8">
					<input data-tag="uEmail" class="form-control">
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
					<ul class="avatar o-images desc">
						<li>
							<img src="">
						</li>
					</ul>
				</div>
			</div>

			<div class="form-group">
				<label class="col-sm-4 control-label">相册:</label>
				<div class="col-sm-8 imglist">
					<input type="file" class="form-control-static inputFile" name="uAvatar[]" accept=".jpg,.jpeg,.png">
					<p class="help-block">（最好上传<b>宽480px高480px</b>的jpg图片）</p>
					<ul class="album o-images desc">
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
				<label class="col-sm-4 control-label">所在城市:</label>
				<div class="col-sm-8 m-loc" data-key="uLocation">
					<select class="form-control m-province"></select>
					<select class="form-control m-city"></select>
					<select class="form-control m-district"></select>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">你的籍贯:</label>
				<div class="col-sm-8 m-loc" data-key="uHomeland">
					<select class="form-control m-province"></select>
					<select class="form-control m-city"></select>
					<select class="form-control m-district"></select>
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
					<textarea data-tag="uInterest" class="form-control"></textarea>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">您的内心独白:</label>
				<div class="col-sm-8">
					<textarea data-tag="uIntro" class="form-control"></textarea>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label">您的备注:</label>
				<div class="col-sm-8">
					<textarea data-tag="uNote" class="form-control"></textarea>
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
<script>
	var mProvinces = {{$provinces}};
	var userInfo = {{$userInfo}};
	var mProfessions = {{$professions}};
	var mLocationTmp = '<option value="">请选择</option>{[#items]}<option value="{[key]}">{[name]}</option>{[/items]}';

	var $sls = {
		job: "",
		role: userInfo && userInfo.uRole ? userInfo.uRole : 10
	};

	var fieldsText = {
		"uName": "呢称",
		"uInterest": "兴趣爱好",
		"uIntro": "内心独白",
		"uLocation": "所在城市",
		"uHomeland": "你的籍贯",
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
		var tImages = getImgList();
		$('#tImagesTmp').val(JSON.stringify(tImages));
		console.log(tImages);

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
				BpbhdUtil.showMsg(fieldsText[field] + "还没填写哦~");
				return false;
			}
			postData[field] = val;
		});
		if (err.length > 0) {
			return false;
		}

		if (!$(".inputFile").val() &&
			$(".inputFile").closest(".imglist").find("img").length == 0) {
			BpbhdUtil.showMsg("还没上传头像哦~");
		}

		$(".m-loc").each(function () {
			var self = $(this);
			var field = self.attr('data-key');
			var opts = self.find("select");
			var location = [];
			for (var m = 0; m < opts.length; m++) {
				var opt = opts.eq(m);
				console.log(opt);
				if (opt.val()) {
					location.push({
						key: opt.val(),
						text: opt.find("option:selected").text()
					});
				}
				if ($sls.role == 10 && m < 2 && !opt.val()) {
					BpbhdUtil.showMsg(fieldsText[field] + "还没填写哦");
					return false;
				}
			}
			postData[field] = JSON.stringify(location);
		});
		$("#postData").val(JSON.stringify(postData));
		$("form").submit();
	});

	function getImgList() {
		var items = [];
		$.each($('.o-images.album').find("li"), function () {
			var src = $(this).find('img').attr('src');
			items.push(src);
		});
		return items;
	}

	$(document).on("click", ".o-images.album li span", function () {
		var items = getImgList();
		if (items.length > 1) {
			$(this).closest("li").remove();
		} else {
			BpbhdUtil.showMsg("至少留一张");
		}

	});

	$("[data-tag=uScope]").on("change", function () {
		var val = $(this).val();
		changeScope(val);
	});

	function changeScope(val) {
		var items = [];
		for (var i = 0; i < mProfessions[val].length; i++) {
			var item = {
				key: i, name: mProfessions[val][i]
			};
			items.push(item);
		}
		var profObj = $("[data-tag=uProfession]");
		profObj.html('<option value="">请选择</option>');
		profObj.append(Mustache.render('{[#items]}<option value="{[key]}">{[name]}</option>{[/items]}',{items:items}));
		if ($sls.job != '' || $sls.job) {
			profObj.val($sls.job);
		}
	}

	$(document).on('change', '.m-province', function () {
		var self = $(this);
		var cityOpt = self.closest('.m-loc').find('.m-city');
		var pid = self.find("option:selected").val();
		$.post('/api/config', {
			tag: 'city',
			id: pid
		}, function (resp) {
			if (resp.code == 0) {
				cityOpt.html(Mustache.render(mLocationTmp, resp.data));
				var val = cityOpt.attr('data-val');
				if (val) {
					cityOpt.val(val);
					cityOpt.trigger('change');
					cityOpt.removeAttr('data-val');
				}
			}
		}, 'json');
	});

	$(document).on('change', '.m-city', function () {
		var self = $(this);
		var districtOpt = self.closest('.m-loc').find('.m-district');
		var pid = self.find("option:selected").val();
		$.post('/api/config', {
			tag: 'district',
			id: pid
		}, function (resp) {
			if (resp.code == 0) {
				districtOpt.html(Mustache.render(mLocationTmp, resp.data));
				var val = districtOpt.attr('data-val');
				if (val) {
					districtOpt.val(val);
					districtOpt.removeAttr('data-val');
				}
			}
		}, 'json');
	});

	$(function () {
		if ($('.alert-success').length > 0) {
			setTimeout(function () {
				location.href = "/site/accounts";
			}, 800);
		}

		var html = Mustache.render(mLocationTmp, {items: mProvinces});
		$('.m-province').html(html);

		$.each(userInfo, function (k, v) {
			switch (k) {
				case 'uLocation':
				case 'uHomeland':
					if (!v) {
						return true;
					}
					var location = JSON.parse(v);
					console.log(location);
					var bar = $('[data-key=' + k + ']');
					var provOpt = bar.find('.m-province');
					var cityOpt = bar.find('.m-city');
					var districtOpt = bar.find('.m-district');
					provOpt.val($.trim(location[0].key));
					provOpt.trigger("change");
					cityOpt.attr('data-val', $.trim(location[1].key));
					if (location.length > 2) {
						districtOpt.attr('data-val', $.trim(location[2].key));
					}
					break;
				case 'uAvatar':
					$(".o-images.avatar").html('<li><img src="' + v + '"></li>');
					break;
				case 'uAlbum':
					var album = JSON.parse(v);
					album ={album:album};
					$(".o-images.album").html(Mustache.render('{[#album]}<li><img src="{[.]}"><span><img src="/images/ico_delete.png"></span></li>{[/album]}', album));
					break;
				case 'uScope':
					$("[data-tag=" + k + "]").val(v);
					if (v) {
						changeScope(v);
					}
					break;
				case 'uProfession':
					$sls.job = v;
					$("[data-tag=uProfession]").val(v);
					break;
				default:
					$("[data-tag=" + k + "]").val(v);
					break;
			}
		});

	});

</script>
{{include file="layouts/footer.tpl"}}