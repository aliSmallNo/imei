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

</style>
<script src="/js/amrnb.js"></script>
<div id="page-wrapper">
	<div class="row">
		<h4>与 {{$nickName}} 的聊天
			<small>
				{{if isset($regInfo) && $regInfo}}
				<ul class="reg">
					<li>注册信息: {{$regInfo.name}} {{$regInfo.phone}}</li>
					<li>注册地址: {{foreach from=$regInfo.location item=item}}{{$item.text}} {{/foreach}} </li>
					<li>当前状态: {{$regInfo.status_t}} </li>
				</ul>
				{{else}}
				<ul class="reg">
					<li>没有找到注册信息</li>
				</ul>
				{{/if}}
			</small>
		</h4>
	</div>
	<div class="row">
		<form class="form-horizontal form" action="/site/reply2wx" method="post">
			<input type="hidden" name="openId" value="{{$openId}}">
			<input type="hidden" name="pid" value="{{$pid}}">
			<div class="form-group">
				<label class="control-label">我来回答这个问题</label>
				<textarea class="form-control" name="content" placeholder="写下给微信用户的话，请注意礼貌用语。"></textarea>
				<div class="btn-divider2"></div>
				<input type="submit" class="btn btn-primary" value="发送消息">
			</div>
		</form>
	</div>

	<div class="message_area">
		<h5>最近20条聊天记录</h5>
		<ul class="message_list" id="listContainer">
			{{foreach from=$list item=item}}
			<li class="message_item ">
				<div class="message_info">
					<div class="message_status"><em class="tips">已回复</em></div>
					<div class="message_time">{{$item.dt}}</div>
					<div class="user_info">
						<span class="remark_name">{{$item.nickname}}</span>
						<span class="nickname"></span>
						<span class="avatar"><img src="{{$item.avatar}}"></span>
					</div>
				</div>
				<div class="message_content text">
					<div class="wxMsg">
						{{if $item.type=="image"}}
						<img src="{{$item.txt}}">
						{{elseif $item.type=="voice"}}
						<button data-src="{{$item.txt}}" class="play"><i class="fa fa-volume-up"></i> 播放语音</button>
						{{else}}
						{{$item.txt}}
						{{/if}}
					</div>
				</div>
			</li>
			{{/foreach}}
		</ul>
	</div>
	<div class="row-divider">&nbsp;</div>
</div>
<script>
	$(document).on("click", ".play", function () {
		var self = $(this);
		var xhr = new XMLHttpRequest();
		xhr.open('GET', self.attr("data-src"));
		xhr.setRequestHeader("Access-Control-Allow-Origin", "*");
		xhr.responseType = 'blob';
		xhr.onload = function () {
			playAmrBlob(this.response);
		};
		xhr.send();
	});

	function playAmrBlob(blob, callback) {
		readBlob(blob, function (data) {
			playAmrArray(data);
		});
	}

	function readBlob(blob, callback) {
		var reader = new FileReader();
		reader.onload = function (e) {
			var data = new Uint8Array(e.target.result);
			callback(data);
		};
		reader.readAsArrayBuffer(blob);
	}

	function playAmrArray(array) {
		var samples = AMR.decode(array);
		if (!samples) {
			alert('Failed to decode!');
			return;
		}
		playPcm(samples);
	}

	function playPcm(samples) {
		var ctx = new AudioContext();
		var src = ctx.createBufferSource();
		var buffer = ctx.createBuffer(1, samples.length, 8000);
		buffer.copyToChannel(samples, 0, 0);
		src.buffer = buffer;
		src.connect(ctx.destination);
		src.start();
	}
</script>
{{include file="layouts/footer.tpl"}}