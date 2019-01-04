{{if isset($pjax) && $pjax}}
{{else}}
</div>
<script src="/assets/js/mustache.min.js"></script>
<script src="/js/sb-admin-2.js"></script>
<script src="/assets/js/iscroll.js"></script>
<script src="/assets/js/countUp.js"></script>
<script src="/assets/js/jquery.pjax.js?v=1.1.1"></script>
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
<script src="/assets/lib/My97DatePicker/WdatePicker.js"></script>
<script src="/js/footer.js?v=1.6.7"></script>
</body>
</html>
{{/if}}