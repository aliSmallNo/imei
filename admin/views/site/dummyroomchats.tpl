{{include file="layouts/header.tpl"}}
<style>
	ul {
		list-style: none;
		padding-left: 0;
	}

	.form {
		padding: 10px 25px;
	}

	.reg {
		margin-top: 6px;
	}

	.reg li {
		padding: 3px 0;
	}

	.title img {
		width: 26px;
		height: 26px;
		vertical-align: middle;
	}

</style>
<div class="row title">
	<h4><span class="m-dummy">稻草人</span> <img src="{{$uInfo.uThumb}}"> {{$uInfo.uName}}
		在 <img src="{{$rInfo.rLogo}}"> {{$rInfo.rTitle}} 密聊中...</h4>
</div>
<div class="row">
	<input type="hidden" name="uid" id="UID" value="{{$uInfo.uId}}">
	<input type="hidden" name="rid" id="RId" value="{{$rInfo.rId}}">
	<div class="form-group">
		<label class="control-label">我来密聊</label>
		<textarea class="form-control content" name="content" placeholder="写下群聊的话，注意礼貌用语~"></textarea>
		<div class="btn-divider2"></div>
		<a href="javascript:;" class="btn btn-primary btn-send">确定发送</a>
	</div>
</div>
<div class="message_area">
	<h5>密聊记录</h5>
	<ul class="message_list" id="listContainer"></ul>
</div>
<div class="row-divider">&nbsp;</div>

<input type="hidden" id="cRoomId" value="{{$roomId}}">
<input type="hidden" id="cAdminId" value="{{$admin_id}}">
<script type="text/html" id="tpl_message">
	{[#items]}
	<li class="message_item ">
		<div class="message_info">
			<div class="message_time">{[dt]}</div>
			<div class="user_info">
				{[&getDummy]}
				<span class="remark_name">{[getName]}</span>
				<span class="avatar"><img src="{[avatar]}"></span>
			</div>
		</div>
		<div class="message_content text">
			<div class="wxMsg">{[&getContent]}</div>
		</div>
	</li>
	{[/items]}
</script>
<script src="/assets/js/socket.io.js"></script>
<script>
	var mRoomId = $('#cRoomId').val();
	var mUID = $('#cUID').val();
	var mTmp = $('#tpl_message').html();
	var mDummyId = $('#UID').val();
	var mList = $('.message_list');
	var mContent = $('.content');
	var chatFlag = 0;
	$('.btn-send').on('click', function () {
		var text = mContent.val().trim();
		if (!text) return false;
		if (chatFlag) return false;
		chatFlag = 1;
		$.post('/api/room',
			{
				tag: 'dummysend',
				text: text,
				rid: mRoomId,
				uid: mDummyId
			},
			function (resp) {
				chatFlag = 0;
				mContent.val('');
				if (resp.code < 1) {
					NoticeUtil.broadcast(resp.data);
					//reloadData();
				}
			}, 'json');
	});

	function reloadData() {
		$.post('/api/room',
			{
				tag: 'list',
				rid: mRoomId
			},
			function (resp) {
				chatFlag = 0;
				if (resp.code < 1) {
					var html = Mustache.render(mTmp, {
						items: resp.data,
						getDummy: function () {
							return this.dummy == 1 ? '<span class="m-dummy">稻草人</span>' : '';
						},
						getName: function () {
							return this.aName ? this.name + ' (' + this.aName + ')' : this.name;
						},
						getContent: function () {
							return this.type == 110 ? '<img src="' + this.content + '" alt="">' : this.content;
						}
					});
					mList.html(html);
				}
			}, 'json');
	}

	var NoticeUtil = {
		ioChat: null,
		timer: 0,
		roomId: 0,
		uni: $('#cUNI').val(),
		board: $('.m-notice'),
		init: function () {
			var util = this;
			util.uni = $('#cAdminId').val();

			util.ioChat = io('https://nd.meipo100.com/chatroom');
			util.ioChat.on("msg", function (info) {
				if (info.rid == util.roomId) {
					reloadData();
				}
			});
		},
		broadcast: function (info) {
			var util = this;
			if (info.items) {
				info.items.dir = 'left';
			}
			util.ioChat.emit('broadcast', info);
		},
		join: function (gid) {
			var util = this;
			util.roomId = gid;
			util.ioChat.emit('room', util.roomId, util.uni);
		}
	};

	$(function () {
		reloadData();
		NoticeUtil.init();
		NoticeUtil.join(mRoomId);
	});
</script>
{{include file="layouts/footer.tpl"}}