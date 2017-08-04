<style>
	.swap-wrap {
		padding: 12rem 2rem;
		text-align: center;
	}

	.swap-wrap h4 {
		margin: 0;
		text-align: center;
		font-size: 2rem;
		font-weight: 500;
		line-height: 3.2rem;
	}

	.swap-wrap h4 b{
		color: #f06292;
	}

	.swap-wrap a {
		color: #007aff;
		display: inline-block;
		font-size: 1.6rem;
		line-height: 4rem;
		border: 1px solid #007aff;
		border-radius: .5rem;
		width: 10.5rem;
		margin: 1rem;
	}

	.swap-wrap a.back {
		color: #888;
		display: inline-block;
		font-size: 1.6rem;
		line-height: 4rem;
		border: 1px solid #a8a8a8;
		border-radius: .5rem;
		width: 10.5rem;
		margin: 1rem;
	}

</style>

<div class="swap-wrap">
	<h4>{{$tip}}</h4>
	<br><br>
	<div>
		<a href="{{$back}}" class="back">不换了</a>
		<a href="{{$forward}}">我要换身份</a>
	</div>
</div>

