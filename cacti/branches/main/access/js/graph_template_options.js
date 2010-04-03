<script type="text/javascript">
<!--
$(document).ready(function(){
	// inter-dependencies
	rrdtool_graph_dependencies();
	// search for both the field itself and the templating checkbox (Use per Graph Value),
	// so use the class for searching and NOT the label!
	Enable_if_Checked('.auto_scale_log', ".scale_log_units");

	// toggle not only if the field itself is (un)checked,
	// do the same for the templating checkbox
	$('.auto_scale_log').click( function() {
		Enable_if_Checked('.auto_scale_log', ".scale_log_units");
	});
});

	function rrdtool_graph_dependencies() {
		var hidden_rrdtool_version = $('#hidden_rrdtool_version').val();
		if (hidden_rrdtool_version == '<?php print RRD_VERSION_1_0;?>') {
			$('.not_RRD_1_0_x').each(function() { $(this).parent().parent().hide(); });
			$("#image_format_id option[value='<?php print IMAGE_TYPE_SVG;?>']").attr('disabled', 'disabled');
			$("#auto_scale_opts_<?php print GRAPH_ALT_AUTOSCALE_MIN;?>").attr('disabled', 'disabled');
		}
		if (hidden_rrdtool_version == '<?php print RRD_VERSION_1_2;?>x') {
			$('.not_RRD_1_2_x').each(function() { $(this).parent().hide(); });
			$("#image_format_id option[value='<?php print IMAGE_TYPE_GIF;?>']").attr('disabled', 'disabled');
		}
		if (hidden_rrdtool_version == '<?php print RRD_VERSION_1_3;?>') {
			$('.not_RRD_1_3_x').each(function() { $(this).parent().hide(); });
			$("#image_format_id option[value='<?php print IMAGE_TYPE_GIF;?>']").attr('disabled', 'disabled');
		}
		if (hidden_rrdtool_version == '<?php print RRD_VERSION_1_4;?>') {
			$('.not_RRD_1_4_x').each(function() { $(this).parent().hide(); });
			$("#image_format_id option[value='<?php print IMAGE_TYPE_GIF;?>']").attr('disabled', 'disabled');
		}
	}

	function Enable_if_Checked(findstring, match) {
		if ($(findstring).is(':checked')) {
			$(match).each(function() { $(this).attr('disabled', ''); });
		} else {
			$(match).each(function() { $(this).attr('disabled', 'disabled'); });
		}
	}


//-->
</script>
