<?php
if (!defined('FIGIPASS')) exit;
$_msg = null;

$_dept = isset($_POST['id_department']) ? $_POST['id_department'] : 0;
$_dept = isset($_GET['dept']) ? $_GET['dept'] : $_dept;
$dept = ($_dept > 0) ? $_dept : USERDEPT ;
$department_list = get_department_list();
if (($dept == 0) && (count($department_list)>0)){
  $dkeys = array_keys($department_list);
  $dept = $dkeys[0];
} else
	$department_list [0] = '--none--';

$total_item = count_deskcopy_item(null, $dept);

if (isset($_POST['export']) ) {
    $dept_name = preg_replace('/[\)\(]/', '', $department_list[$dept]);
    $fname = 'deskcopy-item-' . $dept_name . '.csv';
    $path = TMPDIR .'/'. session_id().'-'.$fname;
    export_csv_deskcopy_item($path, $dept);
    
    if (file_exists($path)) {
        ob_clean();        
        header("Content-type: application/x-msdownload");
        header("Content-Disposition: attachment; filename=$fname");
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($path);
        ob_end_flush();        
        exit;
    }
}
?>
<form method="POST" enctype="multipart/form-data" >

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
Department : <?php echo build_combo('id_department', $department_list, $dept, 'department_change()') ?> &nbsp; 
<?php
} //superadmin

if ($total_item > 0){
    if ($total_item == 1)
        echo '<br/>There is only one item.<br/>Click button "Export Items" to download as CSV file.<br/>';
    else 
        echo '<br/>There are ' . $total_item . ' items. <br/>Click button "Export Items" to download as CSV file.<br/>';
} else
    echo '<br/>There is no item available. Export function disabled.<br/>';
?>
<br/>
<button type="submit" id="exportbutton" name="export" value="1" >Export Items</button>
<button type="button" onclick="location.href='./?mod=deskcopy';">Cancel</button>  
</form>
  </td>
</tr>
</table>

<script>
function department_change()
{
    var d = $('#id_department')[0];
    var did = d.options[d.selectedIndex].value;
    $.post("item/get_category_by_department.php", {queryString: ""+did+""}, function(data){
        if(data.length >0) {
            $('#id_category').empty();
            $('#id_category').append(data);
            category_change();
            //var c = document.getElementById('id_category');
            /*
            if (c.options.length > 1)
              $('#change').removeAttr("disabled");
            else
              $('#change').attr("disabled","disabled");
              */
        }
    });
}

<?php
if ($total_item <= 0)
    echo '$("exportbutton").attr("disabled", "disabled");';
?>
</script>