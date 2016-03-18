<?php
if (!defined('FIGIPASS')) exit;
//require 'maintenance_util.php';

$id_location = !empty($_POST['id_location']) ? $_POST['id_location'] : null;
if (empty($id_location))
	$id_location = !empty($_GET['loc']) ? $_GET['loc'] : 0;

if (empty($id_location))
    redirect('./?mod=maintenance&sub=checking');

else {
    $data['id_location'] = $id_location;
    $id_check = create_checklist($data);
    if ($id_check>0)
        redirect('./?mod=maintenance&sub=checking&act=check&id='.$id_check);
    else
        redirect('./?mod=maintenance&sub=checking','Error: could not create a checklist!');
    
}
return;

$categories = category_rows(true);
$item_count = count($categories);

$checklist_items = array();
$query = 'SELECT * FROM checklist_item ci LEFT JOIN checklist_type ct ON ct.id_type = ci.item_type';
$rs = mysql_query($query);
while ($rec = mysql_fetch_assoc($rs)){
	$checklist_items[$rec['id_category']][] = $rec;
}

$location_list = get_location_list();
?>
<div class="submod_wrap">
	<div class="submod_title"><h4>Maintenance Checking</h4></div>
	<div class="submod_links">
	<!--
		<a class="button" href="./?submod=maintenance&sub=checklist">Checking</a> 
		<a class="button" href="./?submod=maintenance&sub=manage">Manage Checklist</a>
	-->
	</div>
</div>

<div class="clear"> </div>
<div class="checking_list middle" style=" ">
<form method="post" id="frm_check">
<input type="hidden" name="id_location" value="<?php echo $id_location?>">
<div >
<strong>Check Location: <?php 
	//echo build_combo('id_location', $location_list, $id_location);
	echo $location_list[$id_location];
?></strong>

</div>
<style>
.checklist td,
.checklist th { border-bottom: 1px dotted #bbb; border-right: 1px dotted #bbb; }
</style>
<?php

if (!empty($categories)){
	foreach ($categories as $row){
        if ($row['id_department'] != USERDEPT) continue;
        $id_category = $row['id_category'];    
        $category_name = $row['category_name'];
		$items = $checklist_items[$id_category];
		if (!empty($items)){
			echo '<div class="category collapseble middle" style="width: 600px; margin: 5px 0">';
			echo '<div class="category_title foldtoggle header" style=""><h4>'.$category_name.'</h4>';
			echo '<a id="btn_cat-'.$id_category.'" class="btn_category_toggle toggle-arrow" rel="open" href="javascript:void(0)">&uArr;</a><div class="clear"></div></div>';
			echo '<div class="checklist items" id="cat-'.$id_category.'" style=" padding-left: 0">';
			echo '<table class="grid checklist itemlist" style="border-top: 1px solid #555; margin: 0 0; width: 100%;">';
			echo '<tr><th>Checklist</th><th>Status</th><th>Remark</th></tr>';	
			foreach ($items as $rec){
				echo '<tr><td>'.$rec['item_name'].'</td><td>';
				$options = explode(':', $rec['type_option']);
				$status_list = '';
				if ($rec['type_format'] == 'radio'){
					foreach($options as $value)
						$status_list .= '<input type="radio" name="status['.$rec['id_item'].']" value="'.$value.'"> '.ucfirst($value).' &nbsp;';
				} else if ($rec['type_format'] == 'checkbox'){
					foreach($options as $value)
						$status_list .= '<input type="checkbox" name="status['.$rec['id_item'].'][]" value="'.$value.'"> '.ucfirst($value).' &nbsp;';
				} else {
					$status_list .= '<input type="text" name="status['.$rec['id_item'].']" value="" style="width: 60px;">';
				}
				echo $status_list;
				echo '</td><td><input type="text" style="width: 200px;" name="remarks['.$rec['id_item'].']"></td></tr>';	
				
			}
			echo '</table>';
			echo '</div></div>';
		} //else echo '<p class="msg error">Data is not available!</p>';
	}
} else
	echo '<p class="msg error ">Maintenance Chekclist\'s Category is not available!</p>';
?>
</div>
<div class="center" style="width: 600px; margin-top: 20px;">
<button name="save" value="create">Save Checklist</button>
</div>

</form>
<script>

$('.btn_category_toggle').click(function (e){
    toggle_fold(this);
});


//$('.btn_category_toggle').trigger('click');

</script>
