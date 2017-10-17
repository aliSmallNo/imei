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

//mobile page
var box = $("#pagebox");
var dots = $(".dots2");
var i = 0;
$(document).on("click", ".arraw", function () {
	//...
	i = box.attr("data-index");
	if (i == 4) {
		i = 0;
	} else {
		i++;
	}
	box.css({"top": -i * 100 + '%'});
	dots.find("span").removeClass();
	dots.find("span[data-i=" + i + "]").addClass("on");
	box.attr("data-index", i);
});

//给最大的盒子增加事件监听
$("#pagebox").swipe(
	{
		swipe: function (event, direction, distance, duration, fingerCount) {
			i = box.attr("data-index");
			if (direction == "up") {
				i = i + 1;
			}
			// else if (direction == "down") {
				//i = i - 1;
			// }

			if (i >= 4) {
				i = 0;
			} else {
				i++;
			}
			box.css({"top": -i * 100 + '%'});
			dots.find("span").removeClass();
			dots.find("span[data-i=" + i + "]").addClass("on");
			box.attr("data-index", i);

		}
	}
);

$(function () {
	/*var h = window.screen.availHeight;
	var w = window.screen.availWidth;
	// console.log(w);console.log(h);
	if (w < 800) {
		$(".pcpage").hide();
		$(".homepage").show();
		$("html").css("font-size", w / 10 + "px");
		$(".homepage").css("height", h + "px");
	} else {
		$(".pcpage").show();
		$(".homepage").hide();
	}*/
});