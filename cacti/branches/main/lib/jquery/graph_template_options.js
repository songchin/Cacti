<script type="text/javascript">
<!--
$(document).ready(function(){

	// RRDTool dependencies
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


	//inter-dependencies
	Enable_if_Checked('#auto_scale_log', ".scale_log_units");

	$('#auto_scale_log').click( function() {
		Enable_if_Checked('#auto_scale_log', ".scale_log_units");
	});

	function Enable_if_Checked(findstring, match) {
		if ($(findstring).is(':checked')) {
			$(match).each(function() { $(this).attr('disabled', ''); });
		} else {
			$(match).each(function() { $(this).attr('disabled', 'disabled'); });
		}
	}

});

//-->
</script>
