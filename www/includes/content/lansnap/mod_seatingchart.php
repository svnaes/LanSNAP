<?php
$output = LS_GetTableLayout($component_id, '', '<div id="tooltip_SEAT_ID" class="seat_tooltip"></div><span class="label">TABLE_ROWSEAT_NUM</span>');
?>
<?=$output?>
<script type="text/javascript">
var func = function() {
	LS_LoadSeatTooltip(<?=$component_id?>)
}
add_load_event(func);
</script>