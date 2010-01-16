<script type="text/javascript">

$(document).ready(function() {
	var graph_type = $("#graph_type_id").val();
	graph_interdependencies(graph_type);

	/* Hide dependant options when sth changed */
	$("#graph_type_id").change(function () {
		$(this).find("option:selected").each(function () {
			var graph_type = $(this).val();
			graph_interdependencies(graph_type);
		});
		// make sure to have the appropriate JS included
		rrdtool_version_dependencies();
	});

});

function graph_interdependencies(graph_type) {
	/* Initially unhide */
	$("[class*='not_RRD_TYPE']").each(function() { $(this).attr('disabled', ''); });

	switch (parseInt(graph_type)) {
	case <?php print GRAPH_ITEM_TYPE_COMMENT;?>:
		$('.not_RRD_TYPE_COMMENT').each(function() { $(this).attr('disabled', 'disabled'); });
		break;
	case <?php print GRAPH_ITEM_TYPE_HRULE;?>:
		$('.not_RRD_TYPE_HRULE').each(function() { $(this).attr('disabled', 'disabled'); });
		break;
	case <?php print GRAPH_ITEM_TYPE_VRULE;?>:
		$('.not_RRD_TYPE_VRULE').each(function() { $(this).attr('disabled', 'disabled'); });
		break;
	case <?php print GRAPH_ITEM_TYPE_LINE1;?>:
	case <?php print GRAPH_ITEM_TYPE_LINE2;?>:
	case <?php print GRAPH_ITEM_TYPE_LINE3;?>:
		$('.not_RRD_TYPE_LINE').each(function() { $(this).attr('disabled', 'disabled'); });
		break;
	case <?php print GRAPH_ITEM_TYPE_AREA;?>:
		$('.not_RRD_TYPE_AREA').each(function() { $(this).attr('disabled', 'disabled'); });
		break;
	case <?php print GRAPH_ITEM_TYPE_AREASTACK;?>:
		$('.not_RRD_TYPE_AREASTACK').each(function() { $(this).attr('disabled', 'disabled'); });
	break;
	case <?php print GRAPH_ITEM_TYPE_LINESTACK;?>:
		$('.not_RRD_TYPE_LINESTACK').each(function() { $(this).attr('disabled', 'disabled'); });
		break;
	case <?php print GRAPH_ITEM_TYPE_GPRINT;?>:
		$('.not_RRD_TYPE_GPRINT').each(function() { $(this).attr('disabled', 'disabled'); });
		break;

	}
}
</script>
