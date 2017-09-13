<style>
	.bg-color {
		background-color: #e2ca8e;
	}

	.opt {
		display: inline-block;
		margin-left: 1rem;
	}

	.magic-radio, .magic-checkbox {
		position: absolute;
		display: none;
	}

	.magic-radio + label, .magic-checkbox + label {
		position: relative;
		display: block;
		padding-left: 3.5rem;
		cursor: pointer;
		vertical-align: middle;
		font-size: 1.2rem
	}

	.magic-radio + label:before, .magic-checkbox + label:before {
		position: absolute;
		top: 0;
		left: 0;
		display: inline-block;
		width: 3rem;
		height: 3rem;
		content: '';
		border: 1px solid #777;
	}

	.magic-checkbox + label:before {
		border-radius: .3rem;
	}

	.magic-radio:checked + label:before, .magic-checkbox:checked + label:before {
		animation-name: none;
	}

	.magic-checkbox:checked + label:before {
		border: #660000;
		background: #660000;
	}

	.magic-radio + label:after, .magic-checkbox + label:after {
		position: absolute;
		display: none;
		content: '';
	}

	.magic-checkbox + label:after {
		top: .2rem;
		left: .7rem;
		box-sizing: border-box;
		width: .6rem;
		height: 1.2rem;
		transform: rotate(45deg);
		border-width: .2rem;
		border-style: solid;
		border-color: #fff;
		border-top: 0;
		border-left: 0;
	}

	.magic-radio:checked + label:after, .magic-checkbox:checked + label:after {
		display: block;
	}

	.magic-radio + label:before {
		border-radius: 50%;
	}

	.magic-radio + label:after {
		top: .7rem;
		left: .7rem;
		width: 1.8rem;
		height: 1.8rem;
		border-radius: 50%;
		background: #660000;
	}

	.magic-radio:checked + label:before {
		border: 1px solid #660000;
	}

	.otherpart {
		padding: 2rem;
	}

	.o-tpic {

	}

	.o-tpic img {
		width: 100%;
	}

	.o-name {
		text-align: center;
		margin: 1rem;
	}

	.o-name label {
		font-size: 2.4rem;
		color: #660000;
	}

	.o-name div {
		margin: 1rem;
	}

	.o-name div input {
		width: 16rem;
		height: 3rem;
		border: 1px solid #660000;
		border-radius: .5rem;
		font-size: 1.8rem;
		padding: .5rem;
	}

	.o-gender {
		margin: 1rem 0;
		text-align: center;
	}

	.o-gender label {
		font-size: 2.4rem;
	}

	.o-btn-test {
		margin: 2rem 0 1rem 0;
		text-align: center;
	}

	.o-btn-test a {
		background: #f8935f;
		border: 1px solid #660000;
		color: #660000;
		padding: .5rem 2.5rem;
		font-size: 2.5rem;
		border-radius: .5rem;
		display: block;
		width: 18rem;
		margin: 0 auto;
	}

	.o-tip {
		font-size: 1.2rem;
		text-align: center;
		color: #660000;
	}

	.o-result {
		margin: 1rem 0;
	}

	.o-result .o-result-title {
		text-align: center;
		font-size: 2rem;
	}

	.o-result .o-result-title span {
		color: #660000;
		font-weight: 800;
		margin-right: .5rem;
	}

	.o-result .o-result-bg {
		background: #dadddc;
		padding: 1rem;
		text-align: center;
		margin: 1rem 0;
	}

	.o-result .o-result-bg h5 {
		font-size: 1.5rem;
	}

	.o-result .o-result-bg h5 span {
		color: #791214;
	}

	.o-result .o-result-bg img {
		width: 100%;
		margin: 1rem 0;
	}

	.o-result .o-result-bg p {
		font-size: 1.5rem;
	}

	.o-result .o-result-bg p span {
		font-weight: 900;
	}
</style>

{{if $name}}
<div class="otherpart">
	<div class="o-tpic">
		<img src="/images/op_1.jpg">
	</div>
	<div class="o-result">
		<div class="o-result-title">
			<span>丁飞</span>的另一半
		</div>
		<div class="o-result-bg">
			<h5>长得很像 <span>某电影中的莫文蔚</span></h5>
			<img src="/images/op_res_0.jpg" alt="">
			<p><span>专家点评:</span>你最好从了他</p>
		</div>
	</div>
	<div class="o-btn-test">
		<a href="javascript:;"  data-tag="again">再测一测别人</a>
	</div>
	<div class="o-btn-test">
		<a href="JavaScript:;"  data-tag="share">分享</a>
	</div>
	<div class="o-tip">
		本测试仅供娱乐，没有任何科学依据，请勿当真！
	</div>
</div>
{{else}}
<div class="otherpart">
	<div class="o-tpic">
		<img src="/images/op_2.jpg">
	</div>
	<div class="o-name">
		<label for="name">输入你的大名：</label>
		<div>
			<input type="text" id="name">
		</div>
	</div>
	<div class="o-gender">
		<label for="name">你的性别</label>
		<div class="opt">
			<input class="magic-radio" type="radio" name="gender" id="c1" value="male">
			<label for="c1"> 男</label>
		</div>
		<div class="opt">
			<input class="magic-radio" type="radio" name="gender" id="nv" value="female">
			<label for="nv"> 女</label>
		</div>
	</div>
	<div class="o-btn-test">
		<a href="javascript:;" data-tag="test">测一测</a>
	</div>
	<div class="o-tip">
		本测试仅供娱乐，没有任何科学依据，请勿当真！
	</div>
</div>
{{/if}}

<div class="m-popup-shade" style="display: none;"></div>
<div class="m-popup-main" style="display: none;">
	<div class="m-popup-wrap">
		<div class="m-popup-content no-bg"></div>
	</div>
	<i class="share-arrow">点击菜单分享</i>
</div>
<input type="hidden" id="cUID" value="{{$uId}}">
<input type="hidden" id="cNAME" value="{{$name}}">
<input type="hidden" id="cGENDER" value="{{$gender}}">
<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script data-main="/js/otherpart.js?v=1.1.7" src="/assets/js/require.js"></script>
