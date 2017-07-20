if (document.location.hash === "" || document.location.hash === "#") {
	document.location.hash = "#cert";
}
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
			localId: '',
			serverId: '',
			uploadImgFlag: 0,

		};

		$(document).on(kClick, "a.choose-img", function () {
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

			function wxUploadImages() {
				if ($sls.uploadImgFlag) {
					return;
				}
				$sls.uploadImgFlag = 1;
				wx.uploadImage({
					localId: $sls.localId.toString(),
					isShowProgressTips: 1,
					success: function (res) {
						$sls.serverId = res.serverId;
						$sls.uploadImage();
					},
					fail: function () {
						$sls.serverId = "";
						showMsg("上传失败！");
						$sls.uploadImgFlag = 0;
					}
				});
			}

			function uploadImage() {
				showMsg("上传中...");
				$.post("/api/user", {
					tag: "cert",
					id: $sls.serverId
				}, function (resp) {
					showMsg(resp.msg);
					if (resp.data) {
						alert(resp.data);
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

			function locationHashChanged() {
				var hashTag = location.hash;
				hashTag = hashTag.replace("#!", "");
				hashTag = hashTag.replace("#", "");
				switch (hashTag) {
					case 'cert':

						break;
					default:
						break;
				}
				if (!hashTag) {
					hashTag = 'cert';
				}
				$sls.curFrag = hashTag;
				var title = $("#" + hashTag).attr("data-title");
				if (title) {
					$(document).attr("title", title);
					$("title").html(title);
					var iFrame = $('<iframe src="/blank.html" class="g-blank"></iframe>');
					iFrame.on('load', function () {
						setTimeout(function () {
							iFrame.off('load').remove();
						}, 0);
					}).appendTo($("body"));
				}
				layer.closeAll();
			}

			$(function () {
				$("body").addClass("bg-color");
				window.onhashchange = locationHashChanged;
				var wxInfo = JSON.parse($sls.wxString);
				wxInfo.debug = false;
				wxInfo.jsApiList = ['hideOptionMenu', 'hideMenuItems','chooseImage', 'previewImage', 'uploadImage'];
				wx.config(wxInfo);
				wx.ready(function () {
					wx.hideOptionMenu();
				});
				locationHashChanged();
				$sls.cork.hide();

			});
		});