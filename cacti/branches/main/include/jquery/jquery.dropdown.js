$(document).ready(function(){

	$('.languages').one("click",
		function () {
			$.ajax({
					method: "get",url: "./lib/ajax/get_languages.php?location=" + window.location,
					beforeSend: function(){$("#loading").show("fast");$("#codelist").hide("fast");},
					complete: function(){ $("#loading").hide("middle");},
					success: function(html){
					$("#codelist").show("fast");
					$("#codelist").html(html);
					 }
				 });
		}
	);

});