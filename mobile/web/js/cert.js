require.config({
	paths: {
		"jquery": "/assets/js/jquery-3.2.1.min",
		"layer": "/assets/js/layer_mobile/layer",
	}
});
require(["layer"],
	function (layer) {
		"use strict";
		var kClick = 'click';
		var $sls = {
			curFrag: "cert",
			cork: $(".app-cork"),
			wxString: $("#tpl_wx_info").html(),
			certFlag: $("#certFlag").val(),
			localId: '',
			serverId: '',
			uploadImgFlag: 0,
		};

		$(document).on(kClick, "a.choose-img", function () {
			if ($sls.certFlag == 1) {
				showMsg("您已通过实名认证~");
				return;
			}
			wx.chooseImage({
				count: 1,
				sizeType: ['original', 'compressed'],
				sourceType: ['album', 'camera'],
				success: function (res) {
					var localIds = res.localIds;
					if (localIds && localIds.length) {
						$sls.localId = localIds[0];
						wxUploadImages();
					}
				}
			});
		});

		function wxUploadImages() {
			if ($sls.uploadImgFlag) {
				return;
			}
			layer.open({
				type: 2,
				content: '正在上传中...'
			});
			$sls.uploadImgFlag = 1;
			wx.uploadImage({
				localId: $sls.localId.toString(),
				isShowProgressTips: 0,
				success: function (res) {
					$sls.serverId = res.serverId;
					uploadImage();
				},
				fail: function () {
					$sls.serverId = "";
					showMsg("上传失败！");
					$sls.uploadImgFlag = 0;
				}
			});
		}

		function uploadImage() {
			$.post("/api/user", {
				tag: "cert",
				id: $sls.serverId
			}, function (resp) {
				showMsg(resp.msg);
				if (resp.code == 0) {
					location.href = "/wx/single#sme";
				} else {
					showMsg(resp.msg);
				}
				$sls.uploadImgFlag = 0;
			}, "json");
		}

		function showMsg(title, sec) {
			var delay = sec || 3;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		}

		$(function () {
			$("body").addClass("bg-color");
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