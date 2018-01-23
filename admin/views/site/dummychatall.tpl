{{include file="layouts/header.tpl"}}
<style>
	.lit-title {
		font-size: 12px;
		color: #777;
		align-items: center;
		align-self: center;
		font-weight: 300;
		margin-left: 15px;
	}
</style>
<div class="row">
	<div class="col-lg-12">
		<h4>稻草人代聊<span class="lit-title"></span></h4>
	</div>
</div>
<div class="row">
	<form class="form-inline" action="/site/dummychatall">
		<input type="hidden" name="sign" value="1">
		<input class="form-control" name="content" placeholder="写下密聊的话" value="">
		<select class="form-control" name="male">
			<option value="">-=请选择代聊帅哥=-</option>
			{{foreach from=$dfemales key=k item=item}}
			<option value="{{$item.uId}}">{{$item.uName}}</option>
			{{/foreach}}
		</select>
		<select class="form-control" name="female">
			<option value="">-=请选择代聊美女=-</option>
			{{foreach from=$dmales key=k item=item}}
			<option value="{{$item.uId}}">{{$item.uName}}</option>
			{{/foreach}}
		</select>
		<select  class="form-control" name="tag">
			<option value="">-=选择发送用户群=-</option>
			<option value="inactive">审核通过的、关注状态的、近七天不活跃用户</option>
			<option value="reg">一周内注册用户</option>
			<option value="rose">媒桂花少于20朵</option>
		</select>
		<button class="btn btn-primary">发送</button>
	</form>
</div>
{{include file="layouts/footer.tpl"}}
