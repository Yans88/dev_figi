<?php
if (!defined('FIGIPASS')) exit;
$id_check = !empty($_GET['id']) ? $_GET['id'] : 0;
if ($id_check == 0){
    redirect('./?mod=maintenance&sub=checking&loc='.$id_location);
}

$query = "SELECT * FROM checklist_checking WHERE id_check = $id_check";
$rs = mysql_query($query);
if ($rs && mysql_num_rows($rs)>0){
    $checking = mysql_fetch_assoc($rs);
    if (empty($id_location)) $id_location = $checking['id_location'];
    $query = "SELECT *, DATE_FORMAT(checked_on, '%e-%b-%Y %H:%i') AS check_date FROM checklist_checking_result WHERE id_check = $id_check";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0){
        while ($rec = mysql_fetch_assoc($rs))
            $checking_result[$rec['id_item']] = $rec;
    }    
}

$equipments = array();
$query = "SELECT cce.*, i.asset_no, i.serial_no
            FROM checklist_checking_equipment cce LEFT JOIN item i  ON i.id_item = cce.id_item
            WHERE cce.id_location = $id_location";
$rs = mysql_query($query);
if ($rs)
    while ($rec = mysql_fetch_assoc($rs))
        $equipments[$rec['id_category']] = $rec;

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
<div class="checking_list middle" style="width: 800px">
<form method="post" id="frm_check">
<input type="hidden" name="id_check" value="<?php echo $id_check?>">
<div >
<strong>Check Location: <?php echo $location_list[$id_location]; ?></strong>
</div>
<style>
.checklist td,
.checklist th { border-bottom: 1px dotted #bbb; border-right: 1px dotted #bbb; }
</style>
<div style="float: right">
<a class="button" href="#">Minimize All</a>
</div>

<?php

if (!empty($categories)){
	foreach ($categories as $row){
        if ($row['id_department'] != USERDEPT) continue;
        $id_category = $row['id_category'];
        $category_name = $row['category_name'];
		$items = $checklist_items[$id_category];
        $asset_no = null;
        if (!empty($equipments[$id_category])){
            $item = $equipments[$id_category];
            $asset_no = ' ( <a target="new_win" href="./?mod=item&act=view&id='.$item['id_item'].'">'.$item['asset_no']. '</a> ) ';
        }
		if (!empty($items)){
			echo '<div class="category collapseble middle" style="width: 800px; margin: 5px 0">';
			echo '<div class="category_title foldtoggle header" style=""><h4>'.$category_name.$asset_no.'</h4>';
			echo '<div style="float:right"><a id="btn_cat-'.$id_category.'" class="btn_category_toggle toggle-arrow" rel="open" href="javascript:void(0)">&uArr;</a></div><div class="clear"></div></div>';
			echo '<div class="checklist items" id="cat-'.$id_category.'" style=" padding-left: 0">';
			echo '<table class="grid checklist itemlist" style="border-top: 1px solid #555; margin: 0 0; width: 100%;">';
			echo '<tr><th>Checklist</th><th>Status</th><th style="width: 110px">Last Checked</th><th style="width: 210px">Remark</th></tr>';	
			foreach ($items as $rec){
				echo '<tr><td>'.$rec['item_name'].'</td><td>';
				$options = explode(':', $rec['type_option']);
				$result = array();
                if (!empty($checking_result[$rec['id_item']]))
                    $result = $checking_result[$rec['id_item']];
				$status_list = '';
				if ($rec['type_format'] == 'radio'){
					foreach($options as $value){
                        $checked = ($value==$result['result']) ? 'checked' : null;
                        $status_list .= '<input type="radio" name="status['.$rec['id_item'].']" value="'.$value.'" '.$checked.'> '.ucfirst($value).' &nbsp;';
                    }
                } else if ($rec['type_format'] == 'checkbox'){
                    $checkeds = explode(':', $result['result']);
                    foreach($options as $value){
                        $checked = isset($checkeds[$value]) ? 'checked' : null;
						$status_list .= '<input type="checkbox" name="status['.$rec['id_item'].'][]" value="'.$value.'" '.$checked.'> '.ucfirst($value).' &nbsp;';
                    }
                } else {
					$status_list .= '<input type="text" name="status['.$rec['id_item'].']" value="'.$result['result'].'" style="width: 60px;">';
				}
				echo $status_list;
                echo '</td><td>'.$result['check_date'];
				echo '</td><td><input type="text" style="width: 200px;" name="remarks['.$rec['id_item'].']" value="'.$result['remark'].'"></td></tr>';	
				
			}
			echo '</table>';
			echo '</div></div>';
		} //else echo '<p class="msg error">Data is not available!</p>';
	}
} else
	echo '<p class="msg error ">Maintenance Chekclist\'s Category is not available!</p>';

?>

<div class="center" style="  margin-top: 20px;">
<button type="button" name="cancel"> Cancel </button>
<button type="reset" name="reset"> Reset </button>
<button name="save" value="update"> Save Changes </button>
</div>
</div>

</form>
<script>
var all_folded = false;

$('.btn_category_toggle').click(function (e){
    toggle_fold(this);
});

$('button[name=cancel]').click(function(){
    var from_view = <?php echo (strpos($_SERVER['HTTP_REFERER'], 'view')>0) ? 'true' : 'false';?>;
    if (from_view) 
        location.href = "./?mod=maintenance&sub=checking&act=view&id=<?php echo $id_check?>";
    else
        location.href = "./?mod=maintenance&sub=checking&loc=<?php echo $id_location?>";
});

$('a[href=#]').click(function(){
    if (!all_folded){
        $('.btn_category_toggle').trigger('click');
        $(this).text('Maximize All');
        all_folded = true;
    } else {
        $('.btn_category_toggle').trigger('click');
        $(this).text('Minimize All');
        all_folded = false;
    }
});

//$('.btn_category_toggle').trigger('click');

</script>
