<div id="personalInfo" style="padding-bottom: 5rem;background: #eee;">
	<div class="personalInfo-top">
		<div class="nav">
			<a href="/wx/sh?id={{$user.encryptId}}">返回</a>
		</div>
		<div class="img">
			<img src="{{$user.avatar}}" style="width: 100%">
		</div>
	</div>
	<div class="personalInfo-list">
		<div class="title">基本资料</div>
		<div class="item-des">
			<div class="left">呢称</div>
			<div class="right">{{$user.name}}</div>
		</div>
		<div class="item-des">
			<div class="left">性别</div>
			<div class="right">{{$user.gender_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">所在城市</div>
			<div class="right">{{$user.location_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">出生日期</div>
			<div class="right">{{$user.birthyear_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">身高</div>
			<div class="right">{{$user.height}}cm</div>
		</div>
		<div class="item-des">
			<div class="left">年薪</div>
			<div class="right">{{$user.income_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">学历</div>
			<div class="right">{{$user.education_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">星座</div>
			<div class="right">{{$user.horos_t}}</div>
		</div>

		<div class="title">个人小档案</div>
		<div class="item-des">
			<div class="left">购房情况</div>
			<div class="right">{{$user.estate_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">购车情况</div>
			<div class="right">{{$user.car_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">行业</div>
			<div class="right">{{$user.scope_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">职业</div>
			<div class="right">{{$user.profession_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">饮酒情况</div>
			<div class="right">{{$user.alcohol_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">吸烟情况</div>
			<div class="right">{{$user.smoke_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">宗教信仰</div>
			<div class="right">{{$user.belief_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">健身习惯</div>
			<div class="right">{{$user.fitness_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">饮食习惯</div>
			<div class="right">{{$user.diet_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">作息习惯</div>
			<div class="right">{{$user.rest_t}}</div>
		</div>
		<div class="item-des">
			<div class="left">关于宠物</div>
			<div class="right">{{$user.pet_t}}</div>
		</div>

		<div class="title">内心独白</div>
		<div class="item-des">
			<div class="des">{{$user.intro}}</div>
		</div>

		<div class="title">兴趣爱好</div>
		<div class="item-des">
			<div class="des">{{$user.interest}}</div>
		</div>

	</div>
</div>

<script src="/assets/js/jquery-3.2.1.min.js"></script>
<script src="/assets/js/mustache.min.js"></script>