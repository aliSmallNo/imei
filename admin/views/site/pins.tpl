<script src="//webapi.amap.com/maps?v=1.3&key=91beaaedf2dfe666c6afbe8a566ccc4b&plugin=AMap.ToolBar"></script>
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
		margin: 0;
		padding: 0;
		background-color: #fff;
	}

	.menu_body li {
		display: flex;
		padding: 3px 16px 3px 3px;
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
		flex: 0 0 40px;
		text-align: center;
		align-self: center;
	}

	.menu_body li .avatar img {
		width: 36px;
		height: 36px;
		vertical-align: middle;
		border-radius: 3px;
		border: 1px solid #E8E8E8;
	}

	.menu_body li .content {
		flex: 1;
		font-size: 12px;
		justify-content: center;
		align-items: center;
		align-self: center;
		padding-left: 4px;
	}

	.menu_body li .content .dt {
		font-size: 10px;
		color: #999;
		text-align: right;
	}

	.av-sm {
		position: relative;
		border: 1px solid #fff;
		border-radius: 17px;
		width: 32px;
		height: 32px;
		overflow: hidden;
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
</style>
<div id="page-wrapper">
	<div class="leftBox">
		<ul class="menu_body">
			{{foreach from=$items key=k item=user}}
			<li data-lat="{{$user.lat}}" data-lng="{{$user.lng}}" data-idx="{{$k+1}}">
				<div class="seq">{{$k+1}}.</div>
				<div class="avatar"><img src="{{$user.thumb}}" alt=""></div>
				<div class="content">{{$user.name}} {{$user.phone}}
					<div class="dt">{{$user.dt}}</div>
				</div>
			</li>
			{{/foreach}}
		</ul>
	</div>
	<div id="mapContainer" class="rightBox"></div>
</div>
<script>
	var map = new AMap.Map("mapContainer", {
		resizeEnable: true,
		center: [120.320353, 32.845766],
		zoom: 14
	});
	var toolBar = new AMap.ToolBar({
		visible: true
	});
	map.addControl(toolBar);

	var maxzIndex = 100;
	$(".menu_body li").on('click', function () {
		var self = $(this);
		var lat = self.attr('data-lat');
		var lng = self.attr('data-lng');
		var lnglat = lng + '-' + lat;
		var coordsArr = lnglat.split('-');
		map.setZoomAndCenter(14, coordsArr);
		if (Markers[lnglat]) {
			maxzIndex++;
			Markers[lnglat].setzIndex(maxzIndex);
			Markers[lnglat].setAnimation('AMAP_ANIMATION_DROP');
		}
	});

	var Markers ={};

	function switchMarkers(isShow, items) {
		var links = items;
		if (!links) {
			links = $('.menu_body li');
		}
		$.each(links, function () {
			switchSingleMarker(isShow, $(this));
		});
		map.setFitView();
	}

	function switchSingleMarker(isShow, link) {
		var lat = link.attr('data-lat');
		var lng = link.attr('data-lng');
		var lnglat = lng + '-' + lat;
		var html = link.html();
		var arr = html.split(' ');
		if (arr.length < 2) {
			return;
		}
		var marker = Markers[lnglat];
		if (!marker) {
			var div = document.createElement('label');
			div.className = 'av-sm';
			var src = link.find('img').attr('src');
			div.innerHTML = '<img src="' + src + '"><span>' + link.attr('data-idx') + '</span>';
			marker = new AMap.Marker({
				map: map,
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

	$(document).ready(function () {
		switchMarkers(1);
//		showUnlocated();
	});
</script>