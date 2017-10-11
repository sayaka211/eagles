<link href="css/jquery.scrolling-tabs.css" rel="stylesheet" type="text/css">
<script>
$(document).ready(function() {
	var activeUrl = location.pathname.split("/")[1];
	navList = $("#gnav").find("a");
	navList.each(function(){
		if( $(this).attr("href").split("/")[1] == activeUrl ) {
			$(this).parent().addClass("active");
		};
	});
});
//トグル関数
$.fn.clickToggle = function(a, b) {
	return this.each(function() {
		var clicked = false;
		$(this).on('click', function() {
			clicked = !clicked;
			if (clicked) {
				return a.apply(this, arguments);
			}
			return b.apply(this, arguments);
		});
	});
};
</script>

<div id="gnav">
<ul class="nav nav-tabs " role="tablist" style="height: 42px;">
	<li role="presentation"><a class="menu" href="./">予定</a></li>
	<li role="presentation"><a class="menu" href="./members.php" >出欠入力</a></li>
	<li role="presentation"><a class="menu" href="./attend_list.php">出欠リスト</a></li>
	<li role="presentation"><a class="menu" href="./admin.php">管理者</a></li>
</ul>
</div>
<script src="js/jquery.scrolling-tabs.js"></script>
<script>
$('.nav-tabs').scrollingTabs();
</script>
