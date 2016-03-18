<?php
if (!defined('FIGIPASS')) exit;
$_msg = null;

$_dept = isset($_POST['id_department']) ? $_POST['id_department'] : 0;
$_dept = isset($_GET['dept']) ? $_GET['dept'] : $_dept;
$_cat = isset($_POST['id_category']) ? $_POST['id_category'] : 0;
$_state = isset($_POST['id_status']) ? $_POST['id_status'] : 0;
$dept = ($_dept > 0) ? $_dept : USERDEPT ;

$department_list = get_department_list();
if (($dept == 0) && (count($department_list)>0)){
  $dkeys = array_keys($department_list);
  $dept = $dkeys[0];
} else
	$department_list [0] = '--none--';

$category_list = array(0 => 'All Categories') + get_category_list('EQUIPMENT', $dept);
$status_list = array(0 => 'All Statuses') + get_status_list();
/*
if (count($category_list) == 0)
  $category_list[0] = '--none--';
elseif ($_cat == 0) {
  $dkeys = array_keys($category_list);
  $_cat = $dkeys[0];
}
*/



if (isset($_POST['export']) ) {
    $dept_name = preg_replace('/[\)\(\/]/', '', $department_list[$dept]);
    $cat_name  = preg_replace('/[\)\(\/]/', '-', $category_list[$_cat]);
    $status_name  = preg_replace('/[\)\(\/]/', '-', $status_list[$_state]);
    $dept_name = preg_replace('/[\/]/', '', $dept_name);
    $cat_name  = preg_replace('/[\/]/', '-', $cat_name);
    $status_name = preg_replace('/[\/]/', '-', $status_name);
    $fname = 'item-' . $dept_name . '-' . $cat_name . '-' . $status_name . '.csv';
    $path = TMPDIR .'/'. session_id().'-'.$fname;	
    export_csv_item($path, $dept, $_cat, $_state);
    if (file_exists($path)) {
        ob_clean();        
        header("Content-type: text/x-comma-separated-values");
        header("Content-Disposition: attachment; filename=\"$fname\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($path);
        ob_end_flush();        
        exit;
    }
}
?>
<form id="exportform" method="POST" enctype="multipart/form-data" >

<table width="60%"  border="0" cellspacing=4 cellpadding=4 style="color: white">
<tr><td height=20>&nbsp;</td></tr>
<tr><th style="color: white">Export Item into CSV File</th></tr>
<tr><td height=10>&nbsp;</td></tr>
<tr valign="top">
  <td align="center">
  
<form method="post">
<?php
if (SUPERADMIN) {
?>
Department : <?php echo build_combo('id_department', $department_list, $dept, 'department_change()') ?> &nbsp; <br>
<?php
} //superadmin

echo 'Filtered by Category : ' . build_combo('id_category', $category_list, $_cat) . '&nbsp;<br>';
echo 'Filtered by Status: ' . build_combo('id_status', $status_list, $_state) . '&nbsp;<br>';
?>
<br/>
<br/>
<button type="submit" name="export" value="1" >Export Items</button>
</form>
  </td>
</tr>
</table>

<script>
function department_change()
{
	$('#exportform').submit();
	return;
    var d = $('#id_department')[0];
    var did = d.options[d.selectedIndex].value;
    $.post("item/get_category_by_department.php", {queryString: ""+did+""}, function(data){
        if(data.length >0) {
            $('#id_category').empty();
            $('#id_category').append(data);
            category_change();          
        }
    });
}
</script>
