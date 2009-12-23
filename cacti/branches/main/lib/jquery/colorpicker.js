<script type="text/javascript">
<!--
$(document).ready(function(){

	// set background color before page load
	$('.colortags').each(function() {
		$(this).css('background-color', '#' + this.value).attr('title', this.value);
	});

	$('.colortags').ColorPicker({
		livePreview: true,
		onShow: function(picker) {
			$(picker).fadeIn(500);
			return false;
		},
		onBeforeShow: function() {
			$(this).ColorPickerSetColor(this.value);
			$(this).css('background-color', '#' + this.value).attr('title', this.value);
		},
		onHide: function(picker) {
			$(picker).fadeOut(500);
			return false;
		},
		onSubmit: function(hsb, hex, rgb, el) {
			$(el).val(hex);
			$(el).ColorPickerHide();
			$(el).css('background-color', '#' + hex).attr('title', hex);
		}
	})
	.bind('keyup', function() {
		$(this).ColorPickerSetColor(this.value).attr('title', this.value);
	});
});
//-->
</script>
