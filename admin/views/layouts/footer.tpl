<script src="/js/mustache/2.2.1/mustache.min.js"></script>
<script src="/My97DatePicker/WdatePicker.js"></script>
<script src="/js/sb-admin-2.js"></script>
<script src="/js/lib/iscroll/5.1.3/iscroll.js"></script>

<script type="text/html" id="cModPwdTmp">
	<div class="form-horizontal">
		<div class="row-divider"></div>
		<div class="form-group">
			<label class="col-sm-3 control-label">现在的密码</label>

			<div class="col-sm-8">
				<input type="password" class="form-control" id="modPwd_curPwd" placeholder="请输入现在的密码"/>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">设置新的密码</label>

			<div class="col-sm-8">
				<input type="password" class="form-control" id="modPwd_newPwd" placeholder="请输入新的6-14位密码"/>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">重复新的密码</label>

			<div class="col-sm-8">
				<input type="password" class="form-control" id="modPwd_newPwd2" placeholder="请再次输入新的密码"/>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label"></label>

			<div class="col-sm-8">
				<button class="btn btn-default modPwd_button">确定保存</button>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="cModProfileTmp">
	<div class="form-horizontal">
		<div class="row-divider"></div>
		<div class="form-group">
			<label class="col-sm-3 control-label">公司简称</label>

			<div class="col-sm-8">
				<input type="text" class="form-control" id="modProfile_name" placeholder="请输入公司简称" value="{{$adminBranchInfo.bName}}"/>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">公司全称</label>

			<div class="col-sm-8">
				<input type="text" class="form-control" id="modProfile_fullname" placeholder="请输入公司全称"
							 value="{{if isset($adminBranchInfo.bFullName)}}{{$adminBranchInfo.bFullName}}{{/if}}"/>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label">客服电话</label>

			<div class="col-sm-8">
				<input type="text" class="form-control" id="modProfile_phone" placeholder="请输入客服电话" value="{{if isset($adminBranchInfo.bPhone)}}{{$adminBranchInfo.bPhone}}{{/if}}"/>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-3 control-label"></label>

			<div class="col-sm-8">
				<button class="btn btn-default modProfile_btn">确定保存</button>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="admin_todo_tpl">
	{[#items]}
	<li class="{[light]}">
		<a href="{[action]}">
			<div>
				<span class="badge">{[no]}</span><b>{[title]}</b>
				<span class="pull-right text-muted"><em>{[time]}</em></span>
			</div>
			<div>{[tip]}</div>
		</a>
	</li>
	{[#show_divider]}
	<li class="divider"></li>{[/show_divider]}
	{[/items]}
</script>
<script type="text/html" id="admin_wxmsg_tpl">
	{[#items]}
	<li>
		<a href="/info/wxreply?id={[bFrom]}">
			<div>
				<b>{[wNickName]}</b>
				<span class="pull-right text-muted"><em>{[dt]}</em></span>
			</div>
			<div>{[bContent]}</div>
		</a>
	</li>
	<li class="divider"></li>
	{[/items]}
	<li>
		<a class="text-center" href="/info/listwx">
			<strong>更多微信公众号消息</strong>
			<i class="fa fa-angle-right"></i>
		</a>
	</li>
</script>
<script src="/js/countUp.js"></script>
<script src="/js/footer.min.js?v={{#gVersion#}}"></script>
</body>
</html>