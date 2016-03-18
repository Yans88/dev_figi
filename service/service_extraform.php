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
$_move = isset($_GET['move']) ? $_GET['move'] : null;
$_msg = null;
$dept = USERDEPT;

if ($_move != null){
  change_field_order($_cat, $_id, $_move);
}

//$department_list = get_department_list();
$statuses = get_status_list();
$category_list = get_category_list('SERVICE', $dept);
if (($_cat == 0) && count($category_list) > 0){
    $cats = array_keys($category_list);
    $_cat = $cats[0];
}
$field_list = get_extra_field_list($_cat, $id_page);
$total_item = count($field_list);
?>
<script type="text/javascript">

var category = '<?php echo $_cat?>';

function delete_it(){
    ok = confirm('Are you sure you want to delete <?php echo 'asset_no'?>?');
    if (ok) 
        location.href="./?mod=service&act=extraform_del&id=<?php echo $_id?>";     
}

function add_new_field(){
	location.href='./?mod=service&act=extraform_edit&cat=' + category;
}
</script>
<br/>
<h4>Extra Form Fields Management</h4>
<form method="POST" id="telo" >
<table cellspacing=1 cellpadding=2 >
<tr><td align="center">&nbsp;<br/>
        Category : 
<?php
    echo build_combo('id_category', $category_list, $_cat, 'this.form.submit()');
?>
    &nbsp; <a href="javascript:void(0);" onclick="add_new_field()" class="button">Add field</a> 

</td></tr>
<tr valign="top">
    <td width=800>
    <table  width="100%" cellspacing="1" cellpadding="2" class="itemlist">
    <tr height="25"><th width=200>Field Name</th><th width=100>Field Type</th><th>Description</th><th width=80>Action</th></tr>
<?php
if (count($field_list)>0){
	$no = 0;
    $nav_link = "./?mod=service&act=extraform&cat=$_cat";
    foreach ($field_list as $k => $rec){
        $no++;
        $edit_link = null;
        if ($i_can_update){
            if ($rec['order_no']>1)
                $edit_link .= "<a href='$nav_link&move=up&id=$rec[id_field]' title='edit'><img class='icon' src='images/up.png' alt='shift up'></a>";
            else
                $edit_link .= "<img class='icon' src='images/upx.png' alt='shift up'>";
            if ($rec['order_no']<$total_item)
                $edit_link .= "<a href='$nav_link&move=down&id=$rec[id_field]' title='edit'><img class='icon' src='images/down.png' alt='shift down'></a>";
            else
                $edit_link .= "<img class='icon' src='images/downx.png' alt='shift up'>";
            $edit_link .=<<<EDIT
            <a href="./?mod=service&act=extraform_edit&id=$rec[id_field]" title="edit"><img class="icon" src="images/edit.png" alt="edit"></a>
            <a href="?mod=service&act=extraform_del&id=$rec[id_field]" 
               onclick="return confirm('Are you sure you want to delete $rec[field_name]?')" title="delete"><img class="icon" src="images/delete.png" alt="delete"></a>
EDIT;
        }
        $class_style = (($no % 2) == 0) ? 'class="alt"' : 'class="normal"';
		$fieldsize = (strtolower($rec['field_type'])=='text') ? " ($rec[field_size])" : null;
		$field_title = null;
		switch (strtolower($rec['field_type'])){
		case 'text': $field_title = "TEXT: may contain any character. will be displayed as multiline text edit if  the size more than 30 chars."; break;
		case 'numeric': $field_title = "NUMERIC: contain only numeric, will be displayed with smaller space than text."; break;
		case 'boolean': $field_title = "BOOLEAN: contain only true or false state, will be displayed as Yes or No option. "; break;
		}
        echo <<<HTML
        <tr $class_style>
            <td id="td_name$rec[id_field]">$rec[field_name]</td>
            <td id="td_type$rec[id_field]">$rec[field_type]$fieldsize 
				<div style="float: right;"> <a class="hint" title="$field_title">?</a> </div>
				</td>
            <td id="td_desc$rec[id_field]">$rec[field_desc]</td>
            <td align="center" id="td_button$rec[id_field]">$edit_link</td>
        </tr>
HTML;
    }
    echo '</table>';
} else
    echo '<tr><td colspan=4 align="center"><p>Field is not found! Click <a href="javascript:void(0);" onclick="add_new_field()">here</a> to add new extra field!</a></td></tr>';
?>
        </td>
  </tr>
  <tr><td colspan=4>&nbsp;</td></tr>
</table>
</form>
<br/>
<br/>

