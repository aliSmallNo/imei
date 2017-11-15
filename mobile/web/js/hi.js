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
			index: 1,
			count: 8,
			timer: 0,
			speed: 100,
			times: 0,
			cycle: 60,
			prize: -1,
			msg: '',
			title: '',
			loading: false,
			list: $('.m-crew'),
			tmp: $('#tpl_crew').html(),
			init: function () {
				var util = this;
				$(document).on(kClick, '.btn-switch', function () {
					util.reload();
				});
			},
			move: function () {
				var util = this;
				util.table.find(".unit").removeClass("active");
				util.index++;
				if (util.index >= util.count) {
					util.index = 0;
				}
				util.table.find(".unit-" + util.index).addClass("active");
				return false;
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
						id: util.page
					}, function (resp) {
						if (resp.code < 1) {
							var html = Mustache.render(util.tmp, resp.data);
							util.list.html(html);
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
		});
	});