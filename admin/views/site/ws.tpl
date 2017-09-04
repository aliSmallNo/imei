<style>
	.main-top {
		height: 30px;
		background-color: #3d3d3d;
		text-indent: 15px;
		color: #ffffff;
		font-size: 16px;
		line-height: 30px;
	}

	.main-body {
		background-color: #efeff4;
		position: absolute;
		top: 30px;
		bottom: 50px;
		width: 100%;
		overflow-y: scroll;
		scrollbar-3dlight-color:;
	}

	.chatRoomInfo {
		padding: 10px;
		font-size: 12px;
		color: #666;
	}

	.chatRoomTip {
		text-align: center;
		padding: 10px;
		font-size: 12px;
		color: #444;
	}

	.user {
		width: 100%;
		min-height: 38px;
		min-width: 36px;
		margin-bottom: 15px;
	}

	.user span {
		float: right;
	}

	.user div {
		float: right;
		min-height: 38px;
		min-width: 38px;
		max-width: 70%;
		line-height: 38px;
		padding: 0 15px;
		color: #FFFFFF;
		margin-right: 10px;
		word-break: break-all;
		background-color: #007aff;
		position: relative;
		border-radius: 5px;
	}

	.user div:after {
		content: "";
		position: absolute;
		right: -5px;
		top: 4px;
		width: 0;
		height: 0;
		border-top: solid transparent;
		border-left: 7px solid #007aff;
		border-bottom: 4px solid transparent;
	}

	.server {
		width: 100%;
		min-height: 38px;
		min-width: 36px;
		margin-bottom: 15px;
	}

	.server span {
		float: left;
	}

	.server div {
		float: left;
		min-height: 38px;
		min-width: 38px;
		max-width: 70%;
		line-height: 38px;
		padding: 0 15px;
		color: #FFFFFF;
		margin-left: 10px;
		word-break: break-all;
		background-color: #007aff;
		position: relative;
		border-radius: 5px;
	}

	.server div:after {
		content: "";
		position: absolute;
		left: -5px;
		top: 4px;
		width: 0;
		height: 0;
		border-top: solid transparent;
		border-right: 7px solid #007aff;
		border-bottom: 4px solid transparent;
	}

	.main-footer {
		position: absolute;
		bottom: 0;
		width: 100%;
		height: 50px;
	}

	.input {
		float: left;
		width: 80%;
		height: 40px;
		margin-top: 5px;
		margin-left: 1%;
		margin-right: 1%;
		border: 1px solid #666666;
	}

	.input input {
		width: 100%;
		height: 40px;
		outline: none;
		border: none;
		font-size: 14px;
		color: #333;
	}

	.send {
		float: left;
		width: 16%;
		height: 40px;
		margin-top: 5px;
		margin-left: 1%;
		border: none;
		background-color: #e8e8e8;
		color: #007aff;
		outline: none;
	}
</style>
<div class="main">
	<div class="main-top">
		socket.io demo
	</div>
	<div class="main-body">
		<section class="chatRoomInfo">
			<div class="info">当前共有<span class="chatNum">0</span>人在线。在线列表:&nbsp;<span class="chatList"></span></div>
		</section>
		<!--<section class="chatRoomTip">
				<div>子木加入到聊天室</div>
		</section>
		<section class="user clearfix">
				<span>子木</span>
				<div>
						测试测试测试测试测试测试测试测试测试试测试测试测试测试测试测试测试测试测试测试测试
				</div>
		</section>
		<section class="server clearfix">
				<span>子木</span>
				<div>
						测试测试测试
				</div>
		</section>-->
	</div>
	<div class="main-footer clearfix">
		<div class="input">
			<input name="msg" id="msg">
		</div>
		<button type="button" class="send">发送</button>
	</div>
</div>
<script src="/assets/js/socket.io.js"></script>
<script>
	//	var socket = io('http://ws.meipo100.com');
	var socket = io('http://localhost:3000');
	socket.emit("message", {
		name: 'imei_rain',
		msg: "hello world"
	});

	socket.on("message", function (obj) {
		console.log(obj);
	});

	socket.on('connect', function () {
		socket.emit('join', 102, 'dashixiong');
	});

	var mMsg = $('#msg');
	$('.send').on('click', function () {
		var msg = mMsg.val();
		if (msg) {
			socket.send(msg);
			mMsg.val('');
		}
		return false;
	});

	/*$('.send').on('click', function () {
		$.ajax({
			type: 'get',
			url: 'http://admin.meipo100.com/api/foo',
			dataType: 'jsonp',
			data: 'jsonpcallback=',
			jsonp: 'jsonpcallback',
			success: function (resp) {
				console.log(resp);
			}
		});

	});*/
</script>