<link rel="stylesheet" href="/assets/css/laydate.min.css">
<style>
	.date-bg {
		background: #f8f8f8;
		padding-bottom: 4rem;
	}

	.date-margintop {
		margin-top: .5rem;
	}

	.magic-radio {
		position: absolute;
		display: none;
	}

	.magic-radio + label {
		position: relative;
		display: block;
		padding-left: 2.4rem;
		cursor: pointer;
		vertical-align: middle;
		font-size: 1.2rem
	}

	.magic-radio + label:before {
		position: absolute;
		top: 0;
		left: 0;
		display: inline-block;
		width: 1.5rem;
		height: 1.5rem;
		content: '';
		border: 1px solid #c0c0c0;
	}

	.magic-radio:checked + label:before {
		animation-name: none;
	}

	.magic-radio + label:after {
		position: absolute;
		display: none;
		content: '';
	}

	.magic-radio:checked + label:after {
		display: block;
	}

	.magic-radio + label:before {
		border-radius: 50%;
	}

	.magic-radio + label:after {
		top: .4rem;
		left: .45rem;
		width: .8rem;
		height: .8rem;
		border-radius: 50%;
		background: #f06292;
	}

	.magic-radio:checked + label:before {
		border: 1px solid #f06292;
	}

	.topup-wrap .topup-opt a.active:after {
		content: initial;
	}
</style>

<div class="date-nav">
	<a href="/wx/single#sme" class="date-return">返回</a>
	{{if in_array($st,[100,105,110])}}
		<a href="javascript:;" class="date-cancel">取消约会</a>
	{{/if}}
</div>
<div class="date-rate date-margintop">
	{{foreach from=$stDic key=k item=item}}
		<a href="javascript:;"
		   class="{{if $st>=$k}}on{{/if}}{{if $k==140 && (($st==130 || st==140) && $commentFlag) }}on{{/if}} {{if $role=='inactive'}}role-inactive{{/if}}"
		   data-val="{{$k}}">{{$item}}</a>
	{{/foreach}}
</div>

<div class="date_meet_content">
	{{if $st>120}}
		<div class="date-tel date-margintop">
			<div class="date-avatar"><img src="{{$TA.uAvatar}}"></div>
			<div class="date-ta-des">
				{{$TA.uName}} {{$TA.uPhone}}
			</div>
			<a href="tel:{{$TA.uPhone}}"><img src="/images/date_phone.png"></a>
		</div>
	{{else}}
		<a class="date-tel date-margintop" href="/wx/sh?id={{$sid}}">
			<div class="date-avatar"><img src="{{$TA.uAvatar}}"></div>
			<div class="date-ta-des">
				{{$TA.uName}}
			</div>
			<span></span>
		</a>
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
		<div class="date-label">约会付费</div>
		<div class="date-option" data-field="paytype">
			<a href="javascript:;" tag-edit="{{if $role=="active" && $st==1}}able{{/if}}"
			   class="{{if isset($d['dPayType']) && $d['dPayType']!=$uid && $d['dPayType']!=$id}}on{{/if}}"
			   data-val="aa">AA</a>
			<a href="javascript:;" tag-edit="{{if $role=="active" && $st==1}}able{{/if}}"
			   class="{{if isset($d['dPayType']) && $d['dPayType']==$id}}on{{/if}}" data-val="ta">TA买单</a>
			<a href="javascript:;" tag-edit="{{if $role=="active" && $st==1}}able{{/if}}"
			   class="{{if isset($d['dPayType']) && $d['dPayType']==$uid}}on{{/if}}" data-val="me">我买单</a>
		</div>
	</div>
	{{if $st!=100}}
		{{if $role=="active" && $st<=105}}
		{{else}}
			<div class="date-input date-margintop">
				<div class="date-input-label">约会时间</div>
				{{if $st>105}}
					<p>{{if isset($d.dDate)}}{{$d.dDate|date_format:'%Y-%m-%d %H:%M'}}{{/if}}</p>
				{{else}}
					<a href="javascript:;" data-input="time" id="datetime">{{if isset($d.dDate)}}{{$d.dDate}}{{/if}}</a>
				{{/if}}
			</div>
			<div class="date-input date-margintop">
				<div class="date-input-label">约会地点</div>
				{{if $st>105}}
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
	{{/if}}
</div>

{{if $st==130}}
	<div class="date-comment" style="margin-bottom: 5rem;display: none">
		<div class="date-comment-item">
			<h4>是否见面完成</h4>
			<div class="opt-radio">
				<div class="opt">
					<input class="magic-radio" type="radio" name="name1" id="cr11" value="见面完成">
					<label for="cr11">见面完成</label>
				</div>
				<div class="opt">
					<input class="magic-radio" type="radio" name="name1" id="cr12" value="未见面">
					<label for="cr12">未见面</label>
				</div>
			</div>
		</div>

		<div class="date-comment-item">
			<h4>见面时长</h4>
			<div class="opt-radio">
				<div class="opt">
					<input class="magic-radio" type="radio" name="name2" id="cr21" value="1-2小时">
					<label for="cr21">1-2小时</label>
				</div>
				<div class="opt">
					<input class="magic-radio" type="radio" name="name2" id="cr22" value="2-4小时">
					<label for="cr22">2-4小时</label>
				</div>
				<div class="opt">
					<input class="magic-radio" type="radio" name="name2" id="cr23" value="4小时以上">
					<label for="cr23">4小时以上</label>
				</div>
			</div>
		</div>
		<div class="date-comment-item">
			<h4>对方约会目的</h4>
			<div class="opt-radio">
				<div class="opt">
					<input class="magic-radio" type="radio" name="name3" id="cr31" value="交友">
					<label for="cr31">交友</label>
				</div>
				<div class="opt">
					<input class="magic-radio" type="radio" name="name3" id="cr32" value="找对象">
					<label for="cr32">找对象</label>
				</div>
				<div class="opt">
					<input class="magic-radio" type="radio" name="name3" id="cr33" value="结婚">
					<label for="cr33">结婚</label>
				</div>
				<div class="opt">
					<input class="magic-radio" type="radio" name="name3" id="cr34" value="玩玩">
					<label for="cr34">玩玩</label>
				</div>
				<div class="opt">
					<input class="magic-radio" type="radio" name="name3" id="cr35" value="目的不清楚">
					<label for="cr35">目的不清楚</label>
				</div>
			</div>
		</div>
		<div class="date-comment-item">
			<h4>本人于照片一致程度</h4>
			<div class="opt-star">
				<a href="javascript:;" data-val="1"></a>
				<a href="javascript:;" data-val="2"></a>
				<a href="javascript:;" data-val="3"></a>
				<a href="javascript:;" data-val="4"></a>
				<a href="javascript:;" data-val="5"></a>
			</div>
		</div>
		<div class="date-comment-item">
			<h4>本人实际资料与平台个人资料一致程度</h4>
			<div class="opt-star">
				<a href="javascript:;" data-val="1"></a>
				<a href="javascript:;" data-val="2"></a>
				<a href="javascript:;" data-val="3"></a>
				<a href="javascript:;" data-val="4"></a>
				<a href="javascript:;" data-val="5"></a>
			</div>
		</div>
		<div class="date-comment-item">
			<h4>约见对方好感程度</h4>
			<div class="opt-star">
				<a href="javascript:;" data-val="1"></a>
				<a href="javascript:;" data-val="2"></a>
				<a href="javascript:;" data-val="3"></a>
				<a href="javascript:;" data-val="4"></a>
				<a href="javascript:;" data-val="5"></a>
			</div>
		</div>
		<div class="date-comment-item">
			<h4>继续线下交往意愿</h4>
			<div class="opt-star">
				<a href="javascript:;" data-val="1"></a>
				<a href="javascript:;" data-val="2"></a>
				<a href="javascript:;" data-val="3"></a>
				<a href="javascript:;" data-val="4"></a>
				<a href="javascript:;" data-val="5"></a>
			</div>
		</div>
		<div class="date-comment-item">
			<h4>对此次线下见面的其他补充评价</h4>
			<textarea rows="4"></textarea>
		</div>
	</div>
{{/if}}

{{if $st==99}}
	<div class="date-btn">
		<a href="javascript:;" data-tag="to-fail" class="fails">约会失败</a>
	</div>
{{/if}}

{{if $st==100}}
	<div class="date-btn">
		<a href="javascript:;" data-tag="to-fail" class="fails">等待系统审核...</a>
	</div>
{{/if}}

{{if $role=="active"}}
	{{if $st==1}}
		<div class="date-btn">
			<a href="javascript:;" data-tag="start_date">发出邀请</a>
		</div>
	{{/if}}
	{{if $st==105}}
		<div class="date-btn">
			<a href="javascript:;" data-tag="wait_agree" class="fails">等待对方同意...</a>
		</div>
	{{/if}}
	{{if $st==110}}
		<div class="date-btn flex-column ">
			<a href="javascript:;" class="date-rule date-pay-rule" data-rule-tag="data_rule_rose">查看付款平台规则</a>
			<a href="javascript:;" data-tag="date_pay">送TA媒桂花</a>
		</div>
	{{/if}}
{{/if}}

{{if $st==120}}
	<div class="date-btn flex-column">
		<a href="javascript:;" data-tag="date_phone" data-phone="{{$phone}}">申请他的联系方式</a>
	</div>
{{/if}}

{{if $st==130}}
	<div class="date-btn flex-column">
		{{if $commentFlag}}
			<a href="javascript:;" data-tag="date_complete">约会成功</a>
		{{else}}
			<a href="javascript:;" data-tag="date_to_comment">匿名评论对方</a>
		{{/if}}
	</div>
{{/if}}

{{if $st==140}}
	<div class="date-btn ">
		<a href="javascript:;" data-tag="date_complete">约会成功</a>
	</div>
{{/if}}

{{if $role=="inactive"}}
	{{if $st==105}}
		<div class="date-btn flex-column">
			<a href="javascript:;" class="date-rule date-pay-rule" data-rule-tag="data_rule_agree">查看接受规则</a>
			<a href="javascript:;" data-tag="date_agree">欣然接受</a>
		</div>
	{{/if}}
	{{if $st==110}}
		<div class="date-btn">
			<a href="javascript:;" data-tag="wait">等待对方送你媒桂花</a>
		</div>
	{{/if}}
{{/if}}

<input type="hidden" id="user_role" value="{{$role}}">
<input type="hidden" id="user_st" value="{{$st}}">
<input type="hidden" id="user_sid" value="{{$sid}}">
<input type="hidden" id="user_did" value="{{if isset($d.dId)}}{{$d.dId}}{{/if}}">
<input type="hidden" id="user_eid" value="{{$eUid}}">
<div class="m-popup-shade"></div>
<div class="m-popup-main" style="display: none">
	<div class="m-popup-wrap">
		<div class="m-popup-content"></div>
	</div>
</div>
<script type="text/template" id="tpl_give">
	<div class="topup-wrap">
		<h4>送TA媒桂花</h4>
		<h5>约会我的他OR她</h5>
		<div class="topup-opt clearfix">
			{[#items]}
			<a href="javascript:;" data-amt="{[amt]}">
				<div class="img"><img src="/images/ico_rose.png"></div>
				<div class="des">
					<em>x {[amt]}朵</em>
				</div>
			</a>
			{[/items]}
		</div>
		<div class="topup-action">
			<a href="javascript:;" class="btn-togive">送媒<br>桂花</a>
		</div>
		<div class="topup-bot" style="display: none">
			<a href="javascript:;">送花给TA，你会有意外惊喜哦~</a>
		</div>
		<a href="javascript:;" class="m-popup-close"></a>
	</div>
</script>
<script type="text/template" id="tpl_cancel_reason">
	<div class="date-wrap">
		<h4>取消此次约会的原因</h4>
		<div class="date-cancel-opt">
			<a href="javascript:;">有过婚史</a>
			<a href="javascript:;">年龄太大</a>
			<a href="javascript:;">年龄太小</a>
			<a href="javascript:;">学历太低</a>
			<a href="javascript:;">异地恋</a>
			<a href="javascript:;">物资条件太高</a>
			<a href="javascript:;">物资条件太低</a>
			<a href="javascript:;">素质差</a>
			<a href="javascript:;">三观不一致</a>
			<a href="javascript:;">其他原因</a>
		</div>
		<div class="date-cancel">
			<a href="javascript:;" class="btn-date-cancel">确定</a>
		</div>
	</div>
</script>
<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/laydate/laydate.js"></script>
<script src="/assets/js/require.js"></script>
<script>
	requirejs(['/js/config.js?v=1.1.2'], function () {
		requirejs(['/js/date.js?v=1.1.4']);
	});
</script>
