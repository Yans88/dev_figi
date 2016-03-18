<?php 


if (!defined('FIGIPASS')) exit;
if (SUPERADMIN || !$i_can_update) {
    include 'unauthorized.php';
    return;
}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_cat = isset($_POST['id_category']) ? $_POST['id_category'] : 0;
if ($_cat == 0)
    $_cat = isset($_GET['cat']) ? $_GET['cat'] : 0;

$_msg = null;
$dept = USERDEPT;

if (isset($_POST['save_field']) && $_POST['save_field'] == 1) {
    $field_name = isset($_POST['field_name']) ? $_POST['field_name'] : '';
    $field_type = isset($_POST['field_type']) ? $_POST['field_type'] : '';
    $field_size = isset($_POST['field_size']) ? $_POST['field_size'] : 0;
    $field_desc = isset($_POST['field_desc']) ? $_POST['field_desc'] : '';
    $res = save_extra_field($_id, $field_name, $field_type, $field_size, $field_desc, $_cat, $id_page);
	if ($res > 1){
        //$_id = mysql_insert_id();
		echo '<script>alert("THe field has been updated!")</script>';
    } else if ($res > 0){
		echo '<script>alert("New field has been added!")</script>';
	}
	echo '<script>location.href="./?mod=service&act=extraform&cat='.$_cat.'"</script>';
	return;
}

$category_list = get_category_list('SERVICE', $dept);
if (($_cat == 0) && count($category_list) > 0){
    $cats = array_keys($category_list);
    $_cat = $cats[0];
}
$field_list = get_extra_field_list($_cat, $id_page);
$field_types = array(
	'TEXT' => 'TEXT',
	'NUMERIC' => 'NUMERIC',
	'BOOLEAN' => 'BOOLEAN'
	);

if ($_id>0){
	$field = get_extra_field($_id);
	$_cat = $field['id_category'];
} else {
	$field['field_name'] = '';
	$field['field_type'] = '';
	$field['field_size'] = '16';
	$field['field_desc'] = '';
	$field['id_category'] = '';
	$field['id_page'] = '';
}

?>
<script type="text/javascript">

var category = '<?php echo $_cat?>';
var curname = '';
var curtype = '';
var curbutt ='';

function field_type_change(me)
{
    if (me.options[me.selectedIndex].value == 'TEXT'){
        $('#field_size_space').show();
    } else {
        $('#field_size_space').hide();
    }
}

function delete_it(){
    ok = confirm('Are you sure you want to delete <?php echo 'asset_no'?>?');
    if (ok) 
        location.href="./?mod=service&act=extraform_del&id=<?php echo $_id?>";     
}

function cancel_edit()
{
	location.href='./?mod=service&act=extraform';
}

</script>

<br/>
<h4>Extra Form Fields Management
<br/>Add/Edit Field	
</h4>

<form method="POST" id="telo" >

<table cellspacing=1 cellpadding=2 id="itemeditz">
<tr valign="top">
    <td width=600>
        <table class="service_table" cellpadding=2 cellspacing=1>
		<tr class="normal">
			<td>Category </td>
			<td>
			<?php
				echo build_combo('id_category', $category_list, $_cat);
			?>
			</td>
		</tr>
		<tr class="alt">
			<td>Field Name</td>
			<td><input type="text" name="field_name" size=35 value="<?php echo $field['field_name']?>"></td>
		</tr>
		<tr class="normal">
			<td>Field Type</td>
			<td><select name="field_type" onchange="field_type_change(this)">
			<?php
				echo build_option($field_types, $field['field_type']);
			?>
            </select>
			<span id="field_size_space"> &nbsp;
			<span id="field_size_title">Field Size:</span>
			<input type="text" name="field_size" size=4 value="<?php echo $field['field_size']?>">
			</span>
			</td>
		</tr>
		<tr class="alt">
			<td>Description</td>
			<td><textarea cols=40 rows=3 name="field_desc"><?php echo $field['field_desc']?></textarea></td>
		</tr>
		<tr>
			<td class="alt" colspan=2 align="center">
				<button name="save_field" value=1>Save</button>
				<button type="button" onclick="cancel_edit()">Cancel</button>
			</td>
		</tr>
		<tr>
			<td colspan=2 id="hintbox">
			<ul>Notes:
				<li>TEXT: may contain any character. will be displayed as multiline text edit if  the size more than 30 chars.</li>
				<li>BOOLEAN: contain only true or false state, will be displayed as Yes or No option. </li>
				<li>NUMERIC: contain only numeric, will be displayed with smaller space than text. </li>
			</ul>
			</td>
		</tr>
		</table>
    </td>
  </tr>
  <tr><td colspan=4>&nbsp;</td></tr>
</table>
</form>
<br/>
<br/>

