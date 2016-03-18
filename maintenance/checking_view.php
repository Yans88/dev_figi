<?php
if (!defined('FIGIPASS')) exit;
//require 'maintenance_util.php';

$id_check = !empty($_GET['id']) ? $_GET['id'] : 0;
$id_location = !empty($_GET['loc']) ? $_GET['loc'] : 0;
if ($id_check == 0){
    redirect('./?mod=maintenance&sub=checking&loc='.$id_location);
}


$checking = null;
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
    
$userlist = get_user_list();
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
<div class="checking_list middle" style=" width: 800px">
<form method="post" id="frm_check">
<input type="hidden" name="id_location" value="<?php echo $id_location?>">
<div style="float: left">
<strong>Checked Location: <?php	echo $location_list[$id_location]; ?></strong> <a href="./?mod=maintenance&sub=checking&loc=<?php echo $id_location?>">back to location</a><br>
<strong>Last checked on: <?php	echo $checking['modified_on']; ?> by <?php echo $userlist[$checking['modified_by']]?></strong><br>
</div>
<div style="float: right">
<a class="button" href="#">Minimize All</a>
</div>
<style>
.checklist td,
.checklist th { border-bottom: 1px dotted #bbb; border-right: 1px dotted #bbb; }
</style>
<?php
//print_r($checklist_items);

if (!empty($categories)){
	foreach ($categories as $row){
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
            echo '<div style="float: right">';
            if ($row['id_department']==USERDEPT)
                echo ' <a id="check-'.$id_category.'" style="padding: 2px 5px" href="./?mod=maintenance&sub=checking&act=check&id='.$id_check.'">update</a> &nbsp;';
            if ($row['linkable_item'] == 1)
                echo ' <a id="assign-'.$id_category.'" style="padding: 2px 5px" href="#assign">assign item</a> &nbsp;';
			echo ' <a id="btn_cat-'.$id_category.'" class="btn_category_toggle toggle-arrow" rel="open" href="javascript:void(0)">&uArr;</a> ';
            echo '</div><div class="clear"></div></div>';
			echo '<div class="checklist items" id="cat-'.$id_category.'" style=" padding-left: 0">';
			echo '<table class="grid checklist itemlist" style="border-top: 1px solid #555; margin: 0 0; width: 100%;">';
			echo '<tr><th width=200>Checklist</th><th width=100>Status</th><th width=100>Date</th><th width=200>Remark</th></tr>';	
			foreach ($items as $rec){
				echo '<tr><td>'.$rec['item_name'].'</td><td>';
				$options = explode(':', $rec['type_option']);
				//print_r($options);
				$status_list = '';
                $result = array();
                if (!empty($checking_result[$rec['id_item']]))
                    $result = $checking_result[$rec['id_item']];
				if ($rec['type_format'] == 'radio'){
                /*
					foreach($options as $value)
						$status_list .= '<input type="radio" name="status['.$rec['id_item'].']" value="'.$value.'"> '.ucfirst($value).' &nbsp;';
                */
                    if (!empty($result['result'])) $status_list .= ucfirst($result['result']);
                    else $status_list .= 'NA';
                } else if ($rec['type_format'] == 'checkbox'){
                /*
					foreach($options as $value)
						$status_list .= '<input type="checkbox" name="status['.$rec['id_item'].'][]" value="'.$value.'"> '.ucfirst($value).' &nbsp;';
				*/
                    if (!empty($result['result'])) $status_list .= str_replace(':', ', ', ucwords($result['result']));
                    else $status_list .= 'NA';
                } else {
					//$status_list .= '<input type="text" name="status['.$rec['id_item'].']" value="" style="width: 60px;">';
                    if (!empty($result['result'])) $status_list .= ucfirst($result['result']);
                    else $status_list .= 'NA';

				}
				echo $status_list;
				echo '</td><td class="center">'.$result['check_date'].'</td>';	
				echo '</td><td>'.$result['remark'].'</td></tr>';	
				
			}
			echo '</table>';
			echo '</div></div>';
		} //else echo '<p class="msg error">Data is not available!</p>';
	}
} else
	echo '<p class="msg error ">Maintenance Chekclist\'s Category is not available!</p>';
?>
</div>
<!--
<div class="center" style="width: 600px; margin-top: 20px;">
<button name="save" value="create">Save Checklist</button>
</div>
-->
</form>
<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />
<script>
var all_folded = false;
var id_location = '<?php echo $id_location?>';
$('.btn_category_toggle').click(function (e){
    toggle_fold(this);
});

$('a[href=#assign]').click(function (){
    var id = this.id.substr(7);
    $.fancybox.open({
        href: './?mod=maintenance&sub=checklist&act=assign_item&cat='+id+'&loc='+id_location,
        type: 'iframe',
        width: 600,
        height: 400,
        padding: 5
    });
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

$(document).ready(function() {
    $('.fancybox').fancybox({padding: 5 });
});
</script>
