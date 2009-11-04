$(document).ready(function(){

	$('.languages').one("click",
		function () {
			var url_path = this.id;
			$.ajax({
					method: "get",url: url_path + "lib/ajax/get_languages.php",
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