<?php 
//echo 'l';
//require 'facility_util.php';

$msg = '';

if (!defined('FIGIPASS')) exit;
$id_trans = !empty($_GET['id']) ? $_GET['id'] : 0;
$id_trans = !empty($_GET['id']) ? $_GET['id'] : 0;

$mappings = array();
$trans = array();
$fmt = '%b-%e-%Y %H:%i';
$usage_end = '';
$check = "SELECT t.*, l.location_name, id_class class, 
            DATE_FORMAT(start_date, '$fmt') usage_start, DATE_FORMAT(end_date, '$fmt') usage_end  
            FROM students_trans t 
            LEFT JOIN location l ON l.id_location = t.id_location 
            WHERE id_trans = $id_trans "; 
$check_rs = mysql_query($check);

if ($check_rs &&(mysql_num_rows($check_rs) > 0)){	
	$trans = mysql_fetch_assoc($check_rs);
    if (!empty($trans)){
        if ($trans['end_date'] != '0000-00-00 00:00:00')
            $usage_end = $trans['usage_end'];
        else
            $usage_end = ' - In Use - ';
        $query = "SELECT t.*, i.asset_no, s.full_name 
                FROM students_trans_detail t 
                LEFT JOIN item i ON i.id_item = t.id_item 
                LEFT JOIN students s ON s.id_student = t.id_student 
                WHERE id_trans = '$id_trans'
                ORDER BY reg_number"; 
        $rs = mysql_query($query);

        if ($rs)
            while($rec = mysql_fetch_assoc($rs)){
                $mappings[$rec['reg_number']] = $rec;
            }
    }
}
if (!empty($_POST['end_use_mapping'])){
    
    $id_user = USERID;
	$end_time = date('Y-m-d H:i:s');
    $query = "UPDATE students_trans SET status=1, end_date = '$end_time', user_end = $id_user WHERE id_trans=$id_trans";
    $rs = mysql_query($query);	
    //error_log(mysql_error());
    $query = "SELECT id_item FROM students_trans_detail WHERE id_trans = $id_trans and id_student > 0 ";
    $rs = mysql_query($query);
    $items = array();
    if ($rs){
        while ( $rec = mysql_fetch_row($rs) )
            $items[] = $rec[0];
    }
    // update item's status to IN_USE
    if (!empty($items)){
        $release_status = AVAILABLE_FOR_LOAN;
        $query = "UPDATE item SET id_status = '$release_status', status_defect = 'released from student usage' WHERE id_item IN (".implode(', ', $items).")";
        mysql_query($query);
        //error_log(mysql_error().$query);
    }
    redirect('./?mod=portal&portal=student_usage&act=view&id='.$id_trans);
} 



?>

<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />
<link rel="stylesheet" type="text/css" href="./style/default/student_usage.css" />
<div class="mod_wrap">
	<div class="mod_title"><h3>Student Usage Management</h3></div>
	<div class="mod_links">	
        <a class="button" href="#history">Student Usage History</a>
</div>
</div>
<div class="clear"> </div>
<table cellpadding=4 cellspacing=1 width=400 style='color:#fff;'>
<tr><th class="right">Location</th><th class="left"><?php echo $trans['location_name']?></th></tr>
<tr><th class="right">Class</th><th class="left"><?php echo $trans['class']?></th></tr>
<tr><th class="right">Usage Start</th><th class="left"><?php echo $trans['usage_start']?></th></tr>
<tr><th class="right">Usage End</th><th class="left"><?php echo $usage_end?></th></tr>
</table>
<form method="POST" id="facility_fix">
<input type="hidden" name="id_trans" id="id_trans" class="id_trans" value="<?php echo $id_trans ?>">
<input type="hidden" id="id_location" value="<?php echo $trans['id_location'] ?>">
<input type="hidden" id="class" value="<?php echo $trans['id_class'] ?>">

<div style="padding: 10px 70px; float: right" class="right">
<?php
    if ($trans['status']==0){
        echo '<button  class="use round-corner filter" id="use" name="end_use_mapping" value=1> End Use </button>';
    }
?>
</div>

<div class="clear"></div>

<?php echo $msg ?>

<div id="facility_fixed" class="middle">
<?php 
foreach($mappings as $regno => $map){
    $id_asset = $map['id_item'];
    $asset_no = $map['asset_no'];
    $id_student = !empty($map['id_student']) ? $map['id_student'] : 0;
    $fullname = null;
    $display_name = '_';
    $abs = null;
    $_absent = 'disabled';
    $_present = 'disabled';
    if ($id_student>0) {
        $fullname = $map['full_name'];
        $display_name  = $fullname;
        if ($map['absent_present']>0) $_present = 'checked';
        else $_absent = 'checked';
    } else 
        $abs = 'no_abs';
	echo <<<DATA
	<button class="$regno item_fixed" type="button" style='width:200px;'>
        <div class="register">$regno</div> 
        <div class="asset">Asset No : $asset_no</div>  
        <div class="student">$display_name</div>  
        <div class="buttons">
            <span class="$abs">
            <input type="radio" $_present > Present 
            <input type="radio" $_absent > Absent
            </span>
            <div class="clear"></div>
        </div>
	</button>
DATA;

}

?>
</div>
<div class="clear"></div>

</form>


<script>
var is_mapped = <?php echo (count($mappings)>0) ? 'true' : 'false'?>;

$(document).ready(function(){	
    $('.fancybox').fancybox({padding: 5 });

});

$('a[href=#history]').click(function(){
    var _location = $('#id_location').val();
    var _class = $('#class').val();
    location.href = "./?mod=portal&portal=student_usage&act=list&loc="+_location+"&class="+_class;
});

</script>
