$(document).ready(function(){
	$(".button").click(function(){
		$(this).children("input[class=link]").each(function() {
			document.location = $(this).attr("value");
		});
	});
});