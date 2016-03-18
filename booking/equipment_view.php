<?php
if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
ob_clean();
$facility_data = bookable_facility_info($_id);
if (!empty($_POST['remove'])){
	remove_equipment($_POST['id_facility'], $_POST['id_equipment']);
	//echo '<alert></alert>');
}
$equipments = get_equipments($_id);

$item_count = count($equipments);
if (count($equipments) > 0){
	$no = 1;
	$equipment_list = '<table style="width: 600px; border-collpase: collapse; padding: 4px; " class="itemlist">';
	$equipment_list .= '<tr><th width="30">No</th><th>Equipment</th><th width=80>Quantity</th></tr>';
	foreach ($equipments as $rec){
		$edit_link = '<a href="?mod=booking&sub=equipment&act=edit&id='.$_id.'&id_equipment='.$rec['id_equipment'].'" class="edit_link">edit</a>';
		$dele_link = '<a href="javascript:remove_equipment('.$_id.','.$rec['id_equipment'].')" class="edit_link">x</a>';
		$cn = ($no % 2 == 1) ? 'alt' : 'normal';
		$equipment_list .= '<tr class="el '.$cn.'"><td class="right">'.($no++).' '.$dele_link.'</td><td>'.$rec['name'].'</td><td class="center">'.$rec['quantity'].'<div style="float: right">'.$edit_link.'</div></td></tr>';	
	}
	$equipment_list .= '</table>';
} else {
	$equipment_list = '- NA -';
}


?>
<link rel="stylesheet" type="text/css" href="./style/default/figi.css" media="screen" />
<link rel="stylesheet" type="text/css" href="./style/default/anytimec.css" />
<link rel='stylesheet' type='text/css' href='./style/default/jquery-ui-1.8.13.custom.css'/>	
<script type="text/javascript" src="./js/jquery/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="./js/anytimec.js"></script>
<script type="text/javascript" src="./js/moment.min.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/booking.css" media="screen" />		

<div style="margin: 5px 5px; ">
<a href="./?mod=booking&sub=equipment&act=edit&id=<?php echo $_id?>" class="button">assign equipment</a>
</div>
<?php echo $equipment_list;?>

<form id="frm" method="post">
<input type="hidden" name="id_facility" value="<?php echo $_id;?>">
<input type="hidden" name="id_equipment" value="">
<input type="hidden" name="remove" value="1">
</form>
<script>
function remove_equipment(id_facility, id_equipment){
	if (confirm('Do you sure un-assign the equipment?')){
		$('input[name=id_equipment]').val(id_equipment);
		$('#frm').submit();
		}
}
</script>
