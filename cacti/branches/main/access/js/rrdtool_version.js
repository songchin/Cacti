<script type="text/javascript">

$(document).ready(function() {
	rrdtool_version_dependencies();
});

// rrdtool_version_dependencies	- disable all version dependant fields
// 								- fields have to be tagged with appropriate class
function rrdtool_version_dependencies() {
	var hidden_rrdtool_version = $('#hidden_rrdtool_version').val();
	if (hidden_rrdtool_version == '<?php print RRD_VERSION_1_0;?>') {
		$('.not_RRD_1_0_x').each(function() { $(this).attr('disabled', 'disabled'); });
	}
	if (hidden_rrdtool_version == '<?php print RRD_VERSION_1_2;?>') {
		$('.not_RRD_1_2_x').each(function() { $(this).attr('disabled', 'disabled'); });
	}
	if (hidden_rrdtool_version == '<?php print RRD_VERSION_1_3;?>') {
		$('.not_RRD_1_3_x').each(function() { $(this).attr('disabled', 'disabled'); });
	}
	if (hidden_rrdtool_version == '<?php print RRD_VERSION_1_4;?>') {
		$('.not_RRD_1_4_x').each(function() { $(this).attr('disabled', 'disabled'); });
	}
}

</script>
