<div id="page-wrapper">
	<div style="padding: 10px">
		<input type="text" id="cText">
		<a href="javascript:;" class="btn-send">发送</a>
	</div>
</div>
<input type="hidden" id="cUNI" value="{{$uni}}"/>
<input type="hidden" id="cRoomId" value="{{$room_id}}"/>
<input type="hidden" id="cWSUrl" value="{{$wsUrl}}"/>
<script src="/assets/js/socket.io.js"></script>
<script>
	var NoticeUtil = {
		socket: null,
		timer: 0,
		uni: $('#cUNI').val(),
		board: $('.m-notice'),
		list: $('.menu_body'),
		rid: $('#cRoomId').val(),
		url: $('#cWSUrl').val(),
		init: function () {
			var util = this;
			util.uni = $('#cUNI').val();
			util.socket = io.connect(util.url + '/chatroom');
			util.socket.on('connect', function () {
				util.socket.emit('room', util.rid, util.uni);
			});

			util.socket.on("waveup", function (resp) {
				console.log(resp);
			});

			util.socket.on("wavedown", function (resp) {
				console.log(resp);
			});
			util.socket.on("msg", function (resp) {
				console.log(resp);
			});
		},
		send: function (text) {
			var util = this;
			var params = {
				key: util.rid,
				senderUId: util.uni,
				content: text
			};
			util.socket.emit('msg', params);
		}
	};

	var mText = $('#cText');
	$(function () {
		NoticeUtil.init();
		$('.btn-send').on('click', function () {
			NoticeUtil.send(mText.val());
			mText.val('');
		});
	});

</script>
