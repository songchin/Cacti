<script type="text/javascript">
<!--
$(document).ready(function(){
	// hide all additional descriptions
	$('.description').hide();
	// unhide description on hover
	$('.template_checkbox').hover(function() {
	    $(this).children('span.description').show();
	}, function() {
	    $(this).children('span.description').hide();
	});
	
	// for all template checkboxes, that are checked: hide input fields
	$('td.template_checkbox').children('input:checked').parent().next('td').children().hide();
	// when checking a template checkbox: hide/show input field
	// if you only disable='disable' them, value will be lost!
	$('td.template_checkbox').find(':checkbox').click(function() {
		if (this.checked) {
			$(this).parent().next('td').children().hide();
		} else {
			$(this).parent().next('td').children().show();
		}
	})

});
-->
</script>