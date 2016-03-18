<?php 
//require 'facility_util.php';

$msg = '';

if (!defined('FIGIPASS')) exit;
$id_trans = !empty($_GET['id']) ? $_GET['id'] : 0;

$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$id_location = !empty($_POST['id_location']) ? $_POST['id_location'] : 0;

if (empty($id_location))
    $id_location = !empty($_GET['loc']) ? $_GET['loc'] : 0;
$class = !empty($_POST['class']) ? $_POST['class'] : null;
if (empty($class)) 
    $class = !empty($_GET['class']) ? $_GET['class'] : null;
    
$total_item = 0;
$trans = array();
$fmt = '%b-%e-%Y %H:%i';
$usage_end = '';
$query = "SELECT t.*, l.location_name, id_class class, full_name, 
            DATE_FORMAT(start_date, '$fmt') usage_start, DATE_FORMAT(end_date, '$fmt') usage_end  
            FROM students_trans t 
            LEFT JOIN user u ON u.id_user = t.user_start 
            LEFT JOIN location l ON l.id_location = t.id_location 
            WHERE 1 ";
if (!empty($id_location))
    $query .= ' AND t.id_location = '.$id_location;
if (!empty($class))
    $query .= " AND t.id_class = '$class' ";
    
$rs = mysql_query($query);
//echo mysql_error();
if ($rs)
    $total_item = mysql_num_rows($rs);
    
if ($total_item>0){
    $limit = 2;
    $offset = ($page - 1) * $limit;
    $total_page = ceil($total_item/$limit);
    $query .= " ORDER BY start_date DESC LIMIT $offset, $limit"; 
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs) > 0){	
        while ($rec = mysql_fetch_assoc($rs))
            $trans[] = $rec;
            
    }
}
$location_list = get_location_with_fixed_item_list();
if (count($location_list) == 0)
	$location_list[0] = '--- no location available! ---';
else
    $location_list = array('0' => '* select location') + $location_list;
$class_list = get_class_list();
if (count($class_list) == 0)
	$class_list[0] = '--- no class available! ---';
else
    $class_list = array('0' => '* select class') + $class_list;

?>

<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />
<link rel="stylesheet" type="text/css" href="./style/default/student_usage.css" />
<div class="mod_wrap">
	<div class="mod_title"><h3>Student Usage History</h3></div>
	<div class="mod_links">	</div>
</div>
<div class="clear"> </div>
<form method="POST" id="facility_fix">
<br>
<div class="middle" style="width: 800px; margin: 0 auto;">
Location &nbsp; <?php echo build_combo('id_location', $location_list, $id_location)?>  
Class &nbsp; <?php echo build_combo('class', $class_list, $class)?> 
</div>
<table class="itemlist" width=800>
<tr><th width=30>No</th><th>Location</th><th width=60>Class</th><th width=120>Usage Start</th><th width=120>Usage End</th><th width=180>Managed By</th><th width=60>Action</th></tr>
<?php

if ($total_item > 0){
    $no = $offset+1;
    foreach ($trans as $rec){
        $row_class = ($no % 2 == 0) ? 'alt':'normal';
        $usage_end = $rec['usage_end'];
        if ($rec['status'] == 0)
            $usage_end = ' - In Use - ';
        $view = '<a href="./?mod=portal&portal=student_usage&act=view&id='.$rec['id_trans'].'">view</a>';
        echo "<tr class='$row_class'><td>$no</td>
                <td>$rec[location_name]</td>
                <td>$rec[class]</td>
                <td>$rec[usage_start]</td>
                <td>$usage_end</td>
                <td>$rec[full_name]</td>
                <td class='center'>$view</td>
            </tr>";
        $no++;
    }
    echo '<tr ><td colspan=7 class="pagination">';
    echo make_paging($page, $total_page, './?mod=portal&portal=student_usage&act=list&page=');
    //echo  '<div class="exportdiv"><a href="./?mod=portal&sub=student_usage&act=list&do=export" class="button">Export Data</a></div>';
    echo '</td></tr></table>';

} else {
    echo '<tr><td class="center" colspan=7>Data is not available!</td></tr>';
}
?>
</table>
</form>

<div class="clear"></div>

<?php echo $msg ?>


<script>

$(document).ready(function(){	
    $('.fancybox').fancybox({padding: 5 });
	
});

$('#id_location').change(function(){	
    this.form.submit();
});

$('#class').change(function(){
    this.form.submit();
});

</script>
