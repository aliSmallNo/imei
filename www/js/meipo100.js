var t = setInterval(c, 3000);
var i = 1;

//var html = ['微信为媒<span>真实百分百</span>', '朋友做背书<span>交友更靠谱</span>', '单身直接沟通<span>无需媒婆介绍</span>', '双方同意加微信<span>媒婆还能收红包</span>'];
var html = ['微信为媒<span>真实百分百</span>', '喜欢就打赏<span>交友更任性</span>',
	'心动随手点<span>好感+++</span>', '沟通更便捷<span>了解他OR她</span>'];

function c() {
	if (i > 3) i = 0;
	if (i < 0) i = 3;
	$(".screen ul").css({"left": -i * 100 + '%'});
	$(".dots span").each(function (index) {
		if (i == index) {
			$(this).addClass("on");
		} else {
			$(this).removeClass("on");
		}
	});
	$(".words p").html(html[i]);
	if (i == 3) {
		$(".next").hide();
	} else {
		$(".next").show();
	}
	if (i == 0) {
		$(".prev").hide();
	} else {
		$(".prev").show();
	}
	i++;
}

$(".prev").on("click", function () {
	clearInterval(t);
	i = i - 2;
	c();
	t = setInterval(c, 3000);
});
$(".next").on("click", function () {
	clearInterval(t);
	c();
	t = setInterval(c, 3000);
});