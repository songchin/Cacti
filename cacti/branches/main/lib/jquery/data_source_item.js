<script type="text/javascript">
<!--
$(document).ready(function(){
	DS_Visibility()
	$('#data_source_type_id').change(DS_Visibility);
});

function DS_Visibility() {

	if ($('#data_source_type_id').val() == 5) {
		$('.DS_compute').show();
		$('.DS_std').hide();
	} else {
		$('.DS_compute').hide();
		$('.DS_std').show();
	}
}
-->
</script>