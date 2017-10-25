<style>

	.date-margintop{
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
		padding: .8rem 0 2.5rem 0;
		font-size: 1rem;
		text-align: center;
	}

	.date-rate a:before {
		position: absolute;
		content: '';
		width: 6.4rem;
		height: .12rem;
		background: #bbb;
		top: 2.8rem;
		left: 0;
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

	.date-rate a.on {
		color: #d4237a;
	}

	.date-rate a.on:before {
		background: #d4237a;
	}

	.date-rate a.on:after {
		background: #d4237a;
	}

	.date-who {
		font-size: 1.4rem;
		text-align: center;
		background: #fff;
		color: #d4237a;
		padding: 1rem;
		border-bottom: 1px solid #eee;
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
		background:#fff;
		padding: .5rem;
	}

	.date-input .date-input-label {
		flex: 0 0 6rem;
		font-size: 1.3rem;
	}

	.date-input input{
		font-size: 1.2rem;
		flex: 1;
		border: none;

	}
	.date-textarea {
		background: #fff;
		padding: 1rem .5rem;
	}

	.date-textarea .date-textarea-label {
		font-size: 1.3rem;
		margin-bottom: .5rem;
	}

	.date-textarea textarea {
		display: block;
		width: 30rem;
		font-size: 1.2rem;
		border: none;
	}

	.date-btn {
		position: fixed;
		left: 0;
		right: 0;
		bottom: 0;
		display: flex;
	}

	.date-btn a {
		flex: 1;
		display: block;
		background: #d4237a;
		text-align: center;
		padding: 1rem;
		color: #fff;
	}
	.date-btn a.fail{
		background: #555;
	}
</style>

<div class="date-who">预约开心</div>
<div class="date-rate date-margintop">
	<a href="javascript:;" class="on">预约对方</a>
	<a href="javascript:;">对方同意</a>
	<a href="javascript:;">付款平台</a>
	<a href="javascript:;">线下见面</a>
	<a href="javascript:;">评价对方</a>
</div>
<div class="date-item date-margintop">
	<div class="date-label">约会项目</div>
	<div class="date-option">
		<a href="javascript:;" class="on" data-val="1">吃饭</a>
		<a href="javascript:;">唱歌</a>
		<a href="javascript:;">看电影</a>
		<a href="javascript:;">健身</a>
		<a href="javascript:;">旅游</a>
		<a href="javascript:;">其他</a>
	</div>
</div>
<div class="date-item date-margintop">
	<div class="date-label">约会预算</div>
	<div class="date-option">
		<a href="javascript:;" class="on" data-val="1">AA</a>
		<a href="javascript:;">TA买单</a>
		<a href="javascript:;">我买单</a>
	</div>
</div>
<div class="date-input date-margintop">
	<div class="date-input-label">约会时间</div>
	<input type="date">
</div>
<div class="date-input date-margintop">
	<div class="date-input-label">约会地点</div>
	<input type="text">
</div>
<div class="date-textarea date-margintop">
	<div class="date-textarea-label">约会说明</div>
	<textarea rows="4" placeholder="写下你对这个约会的预见，期许等"></textarea>
</div>
<div class="date-textarea date-margintop">
	<div class="date-textarea-label">自我介绍</div>
	<textarea rows="5" placeholder="写下你的个人信息，提高约会成功率"></textarea>
</div>

<div class="date-btn">
	<a href="javascript:;" data-tag="to-date">提交</a>
</div>
<div class="date-btn">
	<a href="javascript:;" data-tag="to-pay">付款</a>
</div>

<div class="date-btn">
	<a href="javascript:;" data-tag="to-phone">查看对方手机号</a>
</div>
<div class="date-btn">
	<a href="javascript:;" data-tag="to-common">评论对方</a>
</div>

<div class="date-btn">
	<a href="javascript:;" data-tag="fail" class="fail">欣然接受</a>
	<a href="javascript:;" data-tag="agree">欣然接受</a>
</div>


<script type="text/template" id="tpl_wx_info">
	{{$wxInfoString}}
</script>
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/date.js?v=1.1.8" src="/assets/js/require.js"></script>
