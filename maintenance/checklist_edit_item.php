<?php 
if (!defined('FIGIPASS')) exit;
ob_clean();
require 'header_popup.php';
require 'maintenance_util.php';

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_cat = isset($_GET['cat']) ? $_GET['cat'] : 0;
if ($_cat == 0)
    $_cat = isset($_GET['cat']) ? $_GET['cat'] : 0;
$_msg = null;
$dept = USERDEPT;
$is_new_item = false;

if (isset($_POST['dele'])) {
	$query = 'DELETE FROM checklist_item WHERE id_category = '.$_POST['cat'].' AND id_item = '.$_POST['id'];
	mysql_query($query);
	$ok = mysql_affected_rows() > 0;
	echo "|$ok|";
	//redirect('./?mod=maintenance&sub=checklist', 'Checklist item has been deleted!');
	return;
} else if (isset($_POST['save']) && $_POST['save'] == 1) {

	//error_log(serialize($_POST));
    $item_name = isset($_POST['item_name']) ? mysql_real_escape_string($_POST['item_name']) : '';
    $item_type = isset($_POST['item_type']) ? $_POST['item_type'] : '';
    
	$id_type = $_POST['id_type'];
    if ($id_type == 'custom'){ // create new type item
		// get last id_type
        $rs = mysql_query('SELECT MAX(id_type) FROM checklist_type');
		$rec = mysql_fetch_row($rs);
		if (!empty($rec)) $id_type = $rec[0]+1;
		else $id_type = 1;
        $is_new_item = true;
		
		$option = mysql_real_escape_string(str_replace(PHP_EOL, ':', $_POST['custom_option']));
		$query = "INSERT INTO checklist_type (id_type, type_option, type_format) ";
		$query .= "VALUE($id_type, '$option', '$_POST[type_format]')";
		mysql_query($query);
		//if (mysql_affected_rows()>0) $id_type = mysql_insert_id();
	} 
    {
        $query = "REPLACE INTO checklist_item (id_item, item_name, item_type, id_category)
                    VALUE ($_id, '$item_name', '$id_type', $_cat)";
        mysql_query($query);
        $res = mysql_affected_rows();
        if ($res > 1){
            $_id = mysql_insert_id();
            $_msg = 'The checklist has been updated!';
        } else if ($res > 0){
            $_msg = 'New checklist has been added!';
            $is_new_item = true;
        }
    }
	//echo '<script>alert("'.$_msg.'");parent.location.reload()</script>';
	//return;
}

$checklist_types = array('0' => '* select a checklist type', 'custom' => ' ** create custom option ');
$query = 'SELECT * FROM checklist_type ';
$rs = mysql_query($query);
if ($rs){
	while($rec = mysql_fetch_assoc($rs)){
		$options = explode(':', $rec['type_option']);
		$type_name =  ucwords(implode(', ', $options));
		$type_name .= ' ( '.ucwords($rec['type_format']).' )';
		$checklist_types[$rec['id_type']] = $type_name;
	}
}

$category = get_category($_cat);
$field_types = array('radio' => 'Single selection ( Radio )', 'checkbox' => 'Multiple selection ( Checkbox )');

if ($_id > 0) { // edit
	$query = "SELECT * FROM checklist_item WHERE id_item = '$_id'";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)>0)
		$item = mysql_fetch_assoc($rs);
} else {
	$item['item_type'] = '';	
	$item['item_name'] = '';	
	$item['id_item'] = 0;	
	$item['id_category'] = $_cat;	
}
?>
<div style="width:360px">
<h4>Add Checklist Item </h4>

<form method="POST" id="telo" >
<table width="99%" class="itemlist middle" cellpadding=2 cellspacing=1>
<tr class="normal">
	<td>Category</td>
	<td><?php echo $category['category_name']?></td>
</tr>
<tr class="alt">
	<td>Title</td>
	<td><input type="text" name="item_name" style="width: 200px" value="<?php echo $item['item_name']?>"></td>
</tr>
<tr class="normal">
	<td>Option</td>
	<td>
	<div id="preset_option">
	<select name="id_type" onchange="checklist_type_change(this)">
	<?php
		echo build_option($checklist_types, $item['item_type']);
	?>
	</select>
	</div>
	<div id="new_option" style="display: none">
	<textarea name="custom_option" id="custom_option" rows=3 cols=26></textarea>
	<div class="field-note" style="width: 200px">* put option/line
	<div style="float: right" ><a href="#X" class="field-note info">cancel</a></div>
	<div class="clear"></div>
	</div>
	</div>
	</td>
</tr>
<tr class="alt" id="item_type_option" style="display: none">
	<td>Format</td>
	<td>
		<?php echo build_combo('type_format', $field_types); ?> 
	</td>
</tr>
<tr class="alt">
	<td colspan=2 align="center">
		<button type="button" id="save" value=1>Save</button>
		<button type="button" onclick="cancel_edit()">Cancel</button>
	</td>
</tr>
</table>
 </form>
<br/>
<br/>
</div>
<div class="center">
<?php
if (!empty($_msg)){
    echo '<p class="msg center">'.$_msg.'</p>';
    echo '<button onclick="close_me()" type="button">Close</button>';
    echo '<script>function close_me(){ parent.location.reload(); }</script>';
}

?>
</div>
<script type="text/javascript">

var category = '<?php echo $_cat?>';
var curname = '';
var curtype = '';
var curbutt ='';

function checklist_type_change(me)
{
    if (me.options[me.selectedIndex].value != '1'){
    	if (me.options[me.selectedIndex].value == 'custom'){
        	$('#item_type_option').show();
			$('#preset_option').hide();
			$('#new_option').show();
		}
    } else {
        $('#item_type_option').hide();
    }
}

$('a[href="#X"]').click(function(){
	$('#preset_option').show();
	$('#new_option').hide();
	$('#id_type').find('option[value=0]').attr('selected', true);	
});

function delete_it(){
    ok = confirm('Are you sure you want to delete <?php echo 'asset_no'?>?');
    if (ok) 
        location.href="./?mod=service&act=extraform_del&id=<?php echo $_id?>";     
}

function cancel_edit()
{
	parent.jQuery.fancybox.close();
}

$('#save').click(function(){
	var n = $('input[name=item_name]').val();
	var t = $('#id_type').val();
	if (t == '0'){
		alert('You must select the checklist type');
	} else if (n.length == 0){
		alert('Checklist name is mandatory. Please entry the name.');
	} else {
		$('#telo').append('<input type="hidden" name="save" value=1>');	
		$('#telo').submit();
	}
});

</script>


