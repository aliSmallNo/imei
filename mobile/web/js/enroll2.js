require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		'mustache': '/assets/js/mustache.min',
		"layer": "/assets/js/layer_mobile/layer",
	}
});
require(["jquery", "mustache", "layer"],
	function ($, Mustache, layer) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "cert",
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			certFlag: $("#certFlag").val(),
			localId: [],
			serverId: [],
			uploadImgFlag: 0,
		};

		$(document).on(kClick, ".j-photo", function () {
			/*if ($sls.certFlag == 1) {
				showMsg("您已通过实名认证~");
				return false;
			}*/
			var self = $(this);
			wx.chooseImage({
				count: 1,
				sizeType: ['original', 'compressed'],
				sourceType: ['album', 'camera'],
				success: function (res) {
					var localIds = res.localIds;
					if (localIds && localIds.length) {
						var rid = localIds[0];
						self.attr("data-id", rid).html('<img src="' + rid + '">');
					}
				}
			});
			return false;
		});

		$(document).on(kClick, ".j-next", function () {
			$sls.localId = [];
			var err = 0;
			$(".j-photo").each(function () {
				var self = $(this);
				var tag = self.attr("data-tag");
				var localId = self.attr("data-id");
				if (!localId) {
					showMsg('请上传' + self.attr("title"));
					err = 1;
					return false;
				}
				if (localId) {
					$sls.localId.push({id: localId, tag: tag});
				}
			});
			if (err) {
				return false;
			}
			$sls.serverId = [];
			layer.open({
				type: 2,
				content: '正在上传中...'
			});
			uploadImages();
			return false;
		});

		var uploadImages = function () {
			var temp = $sls.localId.pop();
			var localId = temp.id;
			var tag = temp.tag;
			if (localId.indexOf('http') === 0) {
				$sls.serverId.push(temp);
				if ($sls.localId.length > 0) {
					uploadImages();
				} else {
					submitItem();
				}
			} else {
				wx.uploadImage({
					localId: localId.toString(),
					isShowProgressTips: 0,
					success: function (res) {
						$sls.serverId.push({id: res.serverId, tag: tag});
						if ($sls.localId.length > 0) {
							uploadImages();
						} else {
							submitItem();
						}
					},
					fail: function () {
						showMsg("上传照片信息失败！");
					}
				});
			}
		};

		var submitItem = function () {
			// alert(JSON.stringify($sls.serverId));return;
			if ($sls.uploadImgFlag) {
				return;
			}
			$sls.uploadImgFlag = 1;

			$.post("/api/user", {
				tag: "enroll2",
				certs: JSON.stringify($sls.serverId)
			}, function (resp) {
				showMsg(resp.msg);
				if (resp.code < 1) {
					location.href = "/wx/single#slook";
				} else {
					showMsg(resp.msg);
				}
				layer.closeAll();
				$sls.uploadImgFlag = 0;
			}, "json");
		};

		var showMsg = function (title, sec) {
			var delay = sec || 3;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		};

		$(function () {
			var wxInfo = JSON.parse($sls.wxString);
			wxInfo.debug = false;
			wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems', 'chooseImage', 'previewImage', 'uploadImage'];
			wx.config(wxInfo);
			wx.ready(function () {
				wx.hideOptionMenu();
			});
			$sls.cork.hide();
		});
	});