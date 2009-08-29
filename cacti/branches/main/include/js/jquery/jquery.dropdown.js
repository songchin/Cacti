$(document).ready(function(){

	$('.languages').one("click",
		function () {
			$.ajax({
					method: "get",url: "./lib/ajax/get_languages.php",
					beforeSend: function(){$("#loading").fadeIn(200);},
					complete: function(){$("#loading").fadeOut(400); },
					success: function(html){
					$("#codelist").hide();
					$("#codelist").html(html);
					$("#codelist").fadeIn(400);
					 }
				 });
		}
	);

});