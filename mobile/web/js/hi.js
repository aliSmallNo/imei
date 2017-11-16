require.config({
	paths: {
		"layer": "/assets/js/layer_mobile/layer",
	}
});

require(["layer"],
	function (layer) {
		"use strict";
		var kClick = 'click';

		function showMsg(title, sec) {
			var delay = sec || 4;
			layer.open({
				type: 99,
				content: title,
				skin: 'msg',
				time: delay
			});
		}

		var HiUtil = {
			page: 1,
			loading: false,
			list: $('.m-crew'),
			tmp: $('#tpl_crew').html(),
			init: function () {
				var util = this;
				$(document).on(kClick, '.btn-switch', function () {
					util.reload();
					return false;
				});
				$(document).on(kClick, '.j-sh', function () {
					var self = $(this);
					location.href = '/wx/sh?id=' + self.attr('data-id');
					return false;
				});
			},
			reload: function () {
				var util = this;
				if (util.loading) {
					return false;
				}
				util.loading = true;
				$.post('/api/dummy',
					{
						tag: 'hi',
						page: util.page
					}, function (resp) {
						if (resp.code < 1) {
							var idx = 0;
							$.each(util.list.find('a'), function () {
								var self = $(this);
								var item = resp.data.items[idx];
								self.attr("data-id", item.sid);
								self.css("background-image", "url(" + item.uThumb + ")");
								idx++;
							});
						} else {
							showMsg(resp.msg);
						}
						util.page = resp.data.next;
						util.loading = false;
					}, 'json');
			}
		};

		$(function () {
			HiUtil.init();
			HiUtil.reload();
		});
	});