<script type="text/javascript">
<!--
$(document).ready(function(){
	$('#graph_type_id').change( function() {
		var graph_type = $(this).val();
		var line_width = $('#line_width').val();
		switch (parseInt(graph_type)) {
			case <?php print GRAPH_ITEM_TYPE_LINE1;?>:
				$('#line_width').val('1.00');
			break;
			case <?php print GRAPH_ITEM_TYPE_LINE2;?>:
				$('#line_width').val('2.00');
			break;
			case <?php print GRAPH_ITEM_TYPE_LINE3;?>:
				$('#line_width').val('3.00');
				break;
		}
	});
});
//-->
</script>
