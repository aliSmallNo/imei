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
		<h4> 对 {{$name}} 的跟进</h4>
		<img src="{{$avatar}}" style="width: 64px;height: 64px">
		<div>{{$phone}}</div>
	</div>
	<div class="row">
		<form class="form-horizontal form" action="/site/follow2u" method="post">
			<input type="hidden" name="uid" value="{{$uid}}">
			<div class="form-group">
				<label class="control-label">我来跟进</label>
				<textarea class="form-control" name="content" placeholder="写下跟进记录的话。"></textarea>
				<div class="btn-divider2"></div>
				<input type="submit" class="btn btn-primary" value="确定跟进">
			</div>
		</form>
	</div>

	<div class="message_area">
		<h5>跟进记录</h5>
		<ul class="message_list" id="listContainer">
			{{if $list}}
			{{foreach from=$list item=item}}
			<li class="message_item ">
				<div class="message_info">
					<div class="message_status"><em class="tips">已回复</em></div>
					<div class="message_time">{{$item.tAddedOn}}</div>
					<div class="user_info">
						<span class="remark_name">微媒100-{{$item.aName}}</span>
						<span class="nickname"></span>
						<span class="avatar"><img src="/images/im_default_g.png"></span>
					</div>
				</div>
				<div class="message_content text">
					<div class="wxMsg">
						{{$item.tNote}}
					</div>
				</div>
			</li>
			{{/foreach}}
			{{/if}}
		</ul>
	</div>
	<div class="row-divider">&nbsp;</div>
</div>
<script>
	//	$(document).on("click", ".play", function () {
	//		var self = $(this);
	//		var xhr = new XMLHttpRequest();
	//		xhr.open('GET', self.attr("data-src"));
	//		xhr.setRequestHeader("Access-Control-Allow-Origin", "*");
	//		xhr.responseType = 'blob';
	//		xhr.onload = function () {
	//			playAmrBlob(this.response);
	//		};
	//		xhr.send();
	//	});
	//
	//	function playAmrBlob(blob, callback) {
	//		readBlob(blob, function (data) {
	//			playAmrArray(data);
	//		});
	//	}
	//
	//	function readBlob(blob, callback) {
	//		var reader = new FileReader();
	//		reader.onload = function (e) {
	//			var data = new Uint8Array(e.target.result);
	//			callback(data);
	//		};
	//		reader.readAsArrayBuffer(blob);
	//	}
	//
	//	function playAmrArray(array) {
	//		var samples = AMR.decode(array);
	//		if (!samples) {
	//			alert('Failed to decode!');
	//			return;
	//		}
	//		playPcm(samples);
	//	}
	//
	//	function playPcm(samples) {
	//		var ctx = new AudioContext();
	//		var src = ctx.createBufferSource();
	//		var buffer = ctx.createBuffer(1, samples.length, 8000);
	//		buffer.copyToChannel(samples, 0, 0);
	//		src.buffer = buffer;
	//		src.connect(ctx.destination);
	//		src.start();
	//	}
</script>
{{include file="layouts/footer.tpl"}}