<script src="https://webapi.amap.com/maps?v=1.3&key=ec9efaff78c90c42b5996b542b899f2d&plugin=AMap.ToolBar"></script>
<style>

	.leftBox {
		display: block;
		position: fixed;
		width: 20%;
		top: 0;
		left: 0;
		bottom: 0;
		padding-bottom: 44px;
		overflow-x: hidden;
		overflow-y: auto;
		border-top: 1px solid #E4E4E4;
	}

	.rightBox {
		display: block;
		position: fixed;
		left: 20%;
		right: 0;
		bottom: 0;
		top: 0;
		border-left: 1px solid #bbb;
	}

	.menu_body {
		padding: 0 0 0 26px;
		margin: 0;
		background-color: #fff;
		list-style: decimal;
	}

	.menu_body li {
		position: relative;
		padding: 4px 2px;
		margin: 0;
		border-bottom: 1px solid #E8E8E8;
		cursor: pointer;
	}

	.menu_body li .seq {
		flex: 0 0 20px;
		text-align: left;
		font-size: 12px;
		align-self: center;
	}

	.menu_body li .avatar {
		display: inline-block;
		width: 40px;
		text-align: center;
		align-self: center;
		position: absolute;
		top: 4px;
		left: 2px;
	}

	.menu_body li .avatar img {
		width: 36px;
		height: 36px;
		vertical-align: middle;
		border-radius: 4px;
		/*border: 1px solid #E8E8E8;*/
	}

	.menu_body li .content {
		font-size: 12px;
		justify-content: center;
		align-items: center;
		align-self: center;
		padding-left: 44px;
	}

	.menu_body li .content .name {
		text-align: left;
	}

	.menu_body li .content b {
		font-weight: 400;
	}

	.menu_body li .content .dt {
		font-size: 10px;
		color: #999;
		text-align: right;
	}

	img.female {
		border: 2px solid #f06292;
	}

	img.male {
		border: 2px solid #007aff;
	}

	img.mei {
		border: 2px solid #51c332;
	}

	.av-sm {
		position: relative;
		/*border: 1px solid #fff;*/
		width: 32px;
		height: 32px;
		overflow: hidden;
		border-radius: 16px;
	}

	.av-sm.female {
		border: 1px solid #f06292;
	}

	.av-sm.male {
		border: 1px solid #007aff;
	}

	.av-sm.mei {
		border: 1px solid #51c332;
	}

	.av-sm img {
		width: 100%;
		height: 100%;
	}

	.av-sm span {
		color: #fff;
		background: rgba(0, 0, 0, .2);
		display: block;
		font-size: 10px;
		position: absolute;
		left: 0;
		right: 0;
		bottom: 0;
		text-align: center;
		font-weight: 300;
		padding: 0;
		margin: 0;
	}

	.pin-title {
		text-align: center;
		background: #fff;
		line-height: 24px;
	}

	.pin-title label {
		margin-right: 10px;
		font-weight: 400;
		font-size: 14px;
	}

	.pin-title label i {
		display: inline-block;
		width: 10px;
		height: 10px;
		border-radius: 2px;
		margin-left: 2px;
	}

	.i-mark-male {
		background: #007aff;
	}

	.i-mark-female {
		background: #f06292;
	}

	.i-mark-mei {
		background: #51c332;
	}

	.online::after {
		content: '';
		position: absolute;
		right: 5px;
		top: 5px;
		width: 12px;
		height: 12px;
		background: url(/images/am_online.gif) no-repeat center center;
		background-size: 100% 100%;
	}
</style>
<div id="page-wrapper">
	<div class="leftBox">
		<div class="pin-title">
			<label>男士<i class="i-mark-male"></i></label>
			<label>女士<i class="i-mark-female"></i></label>
			<label>媒婆<i class="i-mark-mei"></i></label>
		</div>
		<ol class="menu_body">
			{{foreach from=$items key=k item=user}}
				<li data-lat="{{$user.lat}}" data-lng="{{$user.lng}}" data-idx="{{$k+1}}" data-uni="{{$user.uni}}">
					<div class="avatar">
						<img src="{{$user.thumb}}" alt="" class="{{$user.mark}}" data-mark="{{$user.mark}}">
					</div>
					<div class="content">
						<div class="name"><b>{{$user.phone}}</b> {{$user.name}}</div>
						<div class="dt">{{$user.dt}}</div>
					</div>
				</li>
			{{/foreach}}
		</ol>
	</div>
	<div id="mapContainer" class="rightBox"></div>
</div>
<input type="hidden" id="cUNI" value="{{$uni}}"/>
<input type="hidden" id="cWSUrl" value="{{$wsUrl}}"/>
<script src="/assets/js/socket.io.js"></script>
<script>
	var mLevel = 13, mMap;
	var maxzIndex = 100;

	$(".menu_body li").on('click', function () {
		var self = $(this);
		var lat = self.attr('data-lat');
		var lng = self.attr('data-lng');
		var lnglat = lng + '-' + lat;
		var coordsArr = lnglat.split('-');
		mMap.setZoomAndCenter(mLevel, coordsArr);
		if (Markers[lnglat]) {
			maxzIndex++;
			Markers[lnglat].setzIndex(maxzIndex);
			Markers[lnglat].setAnimation('AMAP_ANIMATION_DROP');
		}
	});

	var Markers = {};

	function switchMarkers(isShow, items) {
		mMap = new AMap.Map("mapContainer", {
			resizeEnable: true,
			center: [120.320353, 32.845766],
			zoom: mLevel
		});
		var toolBar = new AMap.ToolBar({
			visible: true
		});
		mMap.addControl(toolBar);

		var links = items;
		if (!links) {
			links = $('.menu_body li');
		}
		$.each(links, function () {
			switchSingleMarker(isShow, $(this));
		});
		mMap.setFitView();
	}

	function switchSingleMarker(isShow, link) {
		var lat = link.attr('data-lat');
		var lng = link.attr('data-lng');
		var lnglat = lng + '-' + lat;
		if (lnglat.length < 5) {
			return;
		}
		var html = link.html();
		var arr = html.split(' ');

		if (arr.length < 2) {
			return;
		}
		var marker = Markers[lnglat];
		if (!marker) {
			var div = document.createElement('label');
			var image = link.find('img');
			var src = image.attr('src');
			var mark = image.attr('data-mark');
			div.className = 'av-sm ' + mark;
			div.innerHTML = '<img src="' + src + '"><span>' + link.attr('data-idx') + '</span>';
			marker = new AMap.Marker({
				map: mMap,
				icon: "http://webapi.amap.com/images/marker_sprite.png",
				position: [lng, lat],
				topWhenClick: true,
				topWhenMouseOver: true,
				content: div
			});
			Markers[lnglat] = marker;
		}
		if (isShow) {
			marker.show();
			link.prev('input').prop('checked', true);
		} else {
			marker.hide();
			link.prev('input').prop('checked', false);
			link.closest('div').prev('p.menu_head').find('input.ckHeader').prop('checked', false);
		}
	}

	var NoticeUtil = {
		socket: null,
		timer: 0,
		uni: $('#cUNI').val(),
		board: $('.m-notice'),
		list: $('.menu_body'),
		url: $('#cWSUrl').val(),
		init: function () {
			var util = this;
			util.socket = io(util.url + '/house');
			util.socket.on('connect', function () {
				util.socket.emit('house', util.uni);
			});
			var row;
			util.socket.on("buzz", function (resp) {
				switch (resp.tag) {
					case 'login':
						if (!resp.uid) {
							return false;
						}
						row = $('li[data-uni=' + resp.uid + ']');
						if (row.length) {
							row.addClass('online').insertBefore('.menu_body li:first');
							util.upgrade(resp.uid, resp.tag);
						}
						break;
					case 'logout':
						if (!resp.uid) {
							return false;
						}
						row = $('li[data-uni=' + resp.uid + ']');
						if (row.length) {
							row.removeClass('online');
						}
						util.upgrade(resp.uid, resp.tag);
						break;
					case 'users':
						$.each(resp.users, function () {
							var id = this;
							var row = $('li[data-uni=' + id + ']');
							if (row.length) {
								row.addClass('online').insertBefore('.menu_body li:first');
							}
						});
						break;
				}
			});
		},
		upgrade: function (uid, tag) {
			$.post('/api/user', {
				tag: tag,
				id: uid
			}, function (resp) {
				if (resp.code < 1) {
					$('li[data-uni=' + uid + '] .dt').html(resp.data.dt);
				}
			}, 'json');
		}
	};

	$(function () {

		NoticeUtil.init();
		switchMarkers(1);

	});

</script>
