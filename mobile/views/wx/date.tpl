<style>
	.data-bg {
		background: #f8f8f8;
	}

	.date-margintop {
		margin-top: .5rem;
	}

	.date-rate {
		display: flex;
		background: #fff;
		border-bottom: 1px solid #eee;
	}

	.date-rate a {
		flex: 1;
		position: relative;
		padding: .8rem 0 0.6rem 0;
		margin-bottom: 1.5rem;
		font-size: 1rem;
		text-align: center;
		border-bottom: .12rem solid #bbb;
	}

	.date-rate a:before {
		/*position: absolute;
		content: '';
		width: 6.4rem;
		height: .12rem;
		background: #bbb;
		top: 2.8rem;
		left: 0;*/
	}

	.date-rate a:after {
		position: absolute;
		content: '';
		background: #bbb;
		width: 1rem;
		height: 1rem;
		border-radius: 1rem;
		top: 2.1rem;
		left: 2.5rem;
		border: .2rem solid #fff;
	}

	.date-rate a.role-inactive:after {
		left: 3rem;
	}

	.date-rate a.on {
		color: #d4237a;
	}

	.date-rate a.on:before {
		background: #d4237a;
	}

	.date-rate a.on {
		border-bottom: #d4237a solid .12rem;
	}

	.date-rate a.on:after {
		background: #d4237a;
	}

	.date-nav {
		background: #fff;
		padding: 1rem 2rem;
		border-bottom: 1px solid #eee;
		display: flex;
	}

	.date-nav a {
		flex: 1;
		display: block;
		font-size: 1.4rem;
		color: #d4237a;
		position: relative;
	}

	.date-nav a.date-return:before {
		position: absolute;
		content: '';
		width: 1rem;
		height: 1rem;
		border-left: .12rem solid #d4237a;
		border-bottom: .12rem solid #d4237a;
		transform: rotate(44deg);
		left: -1rem;
		top: .4rem;
	}

	.date-nav a.date-cancel {
		text-align: right;
	}

	.date-item {
		display: flex;
		background: #fff;
		padding: .8rem .5rem;
	}

	.date-item .date-label {
		flex: 0 0 6rem;
		font-size: 1.2rem;
		align-items: center;
		justify-content: center;
		align-self: center;
	}

	.date-item .date-option {
		flex: 1;
	}

	.date-item .date-option a {
		position: relative;
		display: inline-block;
		font-size: 1.2rem;
		padding: .2rem 0.5rem;
		border-radius: .5rem;
		background: #f8f8f8;
	}

	.date-item .date-option a.on {
		background: #d4237a;
		color: #fff;
		border: 1px solid #d4237a;
	}

	.date-input {
		display: flex;
		background: #fff;
		padding: .5rem;
	}

	.date-input .date-input-label {
		flex: 0 0 6rem;
		font-size: 1.3rem;
	}

	.date-input input, .date-input p {
		font-size: 1.2rem;
		flex: 1;
		border: none;

	}

	.date-input p {
		text-align: right;
		margin-right: 2rem;
	}

	.date-textarea {
		background: #fff;
		padding: 1rem .5rem;
	}

	.date-textarea .date-textarea-label {
		font-size: 1.3rem;
		margin-bottom: .5rem;
	}

	.date-textarea textarea, .date-textarea p {
		display: block;
		width: 30rem;
		font-size: 1.2rem;
		border: none;
	}

	.date-textarea p {
		color: #777;
	}

	.flex-column {
		flex-direction: column;
	}

	.date-btn {
		position: fixed;
		left: 0;
		right: 0;
		bottom: 0;
		display: flex;
	}

	.date-btn p {
		font-size: 1.2rem;
		text-align: center;
		padding: .6rem;
		display: none;
	}

	.date-btn a.date-pay-rule {
		text-align: center;
		font-size: 1.2rem;
		color: #0012ff;
		padding: 1rem;
	}

	.date-btn a[data-tag] {
		flex: 1;
		display: block;
		background: #d4237a;
		text-align: center;
		padding: 1rem;
		color: #fff;
	}

	.date-btn a[data-tag].fail {
		background: #555;
	}

	.date-tel {
		display: flex;
		background: #fff;
		padding: .5rem 1rem;
	}

	.date-tel.hide {
		display: none;
	}

	.date-tel .date-avatar {
		flex: 0 0 3rem;
		width: 3rem;
		height: 3rem;
	}

	.date-tel .date-avatar img {
		width: 3rem;
		height: 3rem;
		border-radius: 3rem;
	}

	.date-tel .date-ta-des {
		flex: 1;
		font-size: 1.2rem;
		margin: 0 1rem;
		align-self: center;
	}

	.date-tel a {
		flex: 0 0 3rem;
		width: 3rem;
		height: 3rem;
	}

	.date-tel a img {
		width: 2rem;
		height: 2rem;
		padding: .3rem;
		border-radius: 2rem;
		border: 1px solid #d4237a;
	}
</style>

<div class="date-nav">
	<a href="/wx/single#sme" class="date-return">返回</a>
	{{if $st==100 && $role=='active'}}
	<a href="javascript:;" class="date-cancel">取消邀约</a>
	{{/if}}
</div>
<div class="date-rate date-margintop">
	{{foreach from=$stDic key=k item=item}}
	<a href="javascript:;" class="{{if $st>=$k}}on{{/if}} {{if $role=='inactive'}}role-inactive{{/if}}"
		 data-val="{{$k}}">{{$item}}</a>
	{{/foreach}}
</div>
{{if $st>110}}
<div class="date-tel date-margintop {{if $st==120}}hide{{/if}}">
	<div class="date-avatar"><img src="{{$TA.uAvatar}}"></div>
	<div class="date-ta-des">
		{{$TA.uName}} {{$TA.uPhone}}
	</div>
	<a href="tel:{{$TA.uPhone}}"><img src="/images/date_phone.png"></a>
</div>
{{/if}}
<div class="date-item date-margintop">
	<div class="date-label">约会项目</div>
	<div class="date-option" data-field="cat">
		{{foreach from=$catDic key=k item=item}}
		<a href="javascript:;" class="{{if isset($d.dCategory) && $d.dCategory==$k}}on{{/if}}" data-val="{{$k}}"
			 tag-edit="{{if $role=="active" && $st==1}}able{{/if}}">{{$item}}</a>
		{{/foreach}}
	</div>
</div>
<div class="date-item date-margintop">
	<div class="date-label">约会预算</div>
	<div class="date-option" data-field="paytype">
		<a href="javascript:;" tag-edit="{{if $role=="active" && $st==1}}able{{/if}}"
			 class="{{if isset($d['dPayType']) && $d['dPayType']!=$uid && $d['dPayType']!=$id}}on{{/if}}" data-val="aa">AA</a>
		<a href="javascript:;" tag-edit="{{if $role=="active" && $st==1}}able{{/if}}"
			 class="{{if isset($d['dPayType']) && $d['dPayType']==$id}}on{{/if}}" data-val="ta">TA买单</a>
		<a href="javascript:;" tag-edit="{{if $role=="active" && $st==1}}able{{/if}}"
			 class="{{if isset($d['dPayType']) && $d['dPayType']==$uid}}on{{/if}}" data-val="me">我买单</a>
	</div>
</div>

{{if $role=="active" && $st<=100}}
{{else}}
<div class="date-input date-margintop">
	<div class="date-input-label">约会时间</div>
	{{if $st>100}}
	<p>{{if isset($d.dDate)}}{{$d.dDate|date_format:'%Y-%m-%d'}}{{/if}}</p>
	{{else}}
	<input type="date" tag-edit="{{if $role=="active"}}readonly{{/if}}" data-input="time"
				 value="{{if isset($d.dDate)}}{{$d.dDate}}{{/if}}">
	{{/if}}
</div>
<div class="date-input date-margintop">
	<div class="date-input-label">约会地点</div>
	{{if $st>100}}
	<p>{{if isset($d.dLocation)}}{{$d.dLocation}}{{/if}}</p>
	{{else}}
	<input type="text" tag-edit="{{if $role=="active"}}readonly{{/if}}" data-input="location"
				 value="{{if isset($d.dLocation)}}{{$d.dLocation}}{{/if}}">
	{{/if}}
</div>
{{/if}}

<div class="date-textarea date-margintop">
	<div class="date-textarea-label">约会说明</div>
	{{if $role=="active" && $st==1}}
	<textarea rows="4" placeholder="写下你对这个约会的预见，期许等"
						data-input="title">{{if isset($d.dTitle)}}{{$d.dTitle}}{{/if}}</textarea>
	{{else}}
	<p>{{if isset($d.dTitle)}}{{$d.dTitle}}{{/if}}</p>
	{{/if}}
</div>
<div class="date-textarea date-margintop">
	<div class="date-textarea-label">自我介绍</div>
	{{if $role=="active" && $st==1}}
	<textarea rows="5" placeholder="写下你的个人信息，提高约会成功率"
						data-input="intro">{{if isset($d.dTitle)}}{{$d.dTitle}}{{/if}}</textarea>
	{{else}}
	<p>{{if isset($d.dIntro)}}{{$d.dIntro}}{{/if}}</p>
	{{/if}}
</div>

{{if $st==99}}
<div class="date-btn">
	<a href="javascript:;" data-tag="to-fail" class="fail">约会失败</a>
</div>
{{/if}}

{{if $role=="active"}}
{{if $st==1}}
<div class="date-btn">
	<a href="javascript:;" data-tag="start_date">发出邀请</a>
</div>
{{/if}}
{{if $st==100}}
<div class="date-btn">
	<a href="javascript:;" data-tag="wait_agree">等待对方同意</a>
</div>
{{/if}}
{{if $st==110}}
<div class="date-btn flex-column ">
	<a href="javascript:;" class="date-pay-rule">查看付款平台规则</a>
	<a href="javascript:;" data-tag="date_pay">付款平台</a>
</div>
{{/if}}
{{/if}}

{{if $st==120}}
<div class="date-btn flex-column">
	<p>对方联系方式：{{$phone}}</p>
	<a href="javascript:;" data-tag="date_phone" data-phone="{{$phone}}">申请他的联系方式</a>
</div>
{{/if}}

{{if $st==130}}
<div class="date-btn flex-column">
	<p>对方联系方式：{{$phone}}</p>
	<a href="javascript:;" data-tag="date_common">评论对方</a>
</div>
{{/if}}

{{if $role=="inactive"}}
{{if $st==100}}
<div class="date-btn">
	<a href="javascript:;" data-tag="date_fail" class="fail">残忍拒绝</a>
	<a href="javascript:;" data-tag="date_agree">欣然接受</a>
</div>
{{/if}}
{{if $st==110}}
<div class="date-btn">
	<a href="javascript:;" data-tag="wait">等待对方付款平台</a>
</div>
{{/if}}
{{/if}}

<input type="hidden" id="user_role" value="{{$role}}">
<input type="hidden" id="user_st" value="{{$st}}">
<input type="hidden" id="user_sid" value="{{$sid}}">
<input type="hidden" id="user_did" value="{{if isset($d.dId)}}{{$d.dId}}{{/if}}">
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/date.js?v=1.1.12" src="/assets/js/require.js"></script>
