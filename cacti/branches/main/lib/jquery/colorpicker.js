<script type="text/javascript">
<!--
$(document).ready(function(){

	// set background color before page load
	$('.colortags').each(function() {
		var alpha = "#" + this.id + "_alpha";		// additional input selection for alpha/opacity
		var t_alpha = "#t_" + this.id + "_alpha";	// we want to find the template checkbox
		var background_color_hex = this.value;
		var color_hex = invertColor(background_color_hex);
		$(this).css('background-color', '#' + background_color_hex).attr('title', background_color_hex).css('color', color_hex);
		$(alpha).css('background-color', '#' + background_color_hex).css('color', color_hex);	// show opacity field in same color
		$(t_alpha).hide().next().hide();	// but hide the template checkbox and label
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
			var color_hex = invertColor(hex);
			var alpha = "#" + el.id + "_alpha";
			$(el).css('background-color', '#' + hex).attr('title', hex).css('color', color_hex);
			$(alpha).css('background-color', '#' + hex).css('color', color_hex);
		}
	})
	.bind('keyup', function() {
		$(this).ColorPickerSetColor(this.value).attr('title', this.value);
	});
	
	function invertColor(color_hex) {
		var result;
		if (result = /([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(color_hex)) {
			var red = 255 - parseInt(result[1],16);
			var green = 255 - parseInt(result[2],16);
			var blue = 255 - parseInt(result[3],16);
			return 'rgb('+red+','+green+','+blue+')';
		}
	}
});
//-->
</script>
