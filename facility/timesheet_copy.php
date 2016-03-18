<?php 

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_sid = isset($_GET['sid']) ? $_GET['sid'] : 0;
$_msg = null;

if (isset($_POST['copy'])) {
    $_src_id = isset($_POST['id_facility']) ? $_POST['id_facility'] : 0;
    $facility_data = get_facility($_id);
    $facility_data_from = get_facility($_src_id);
    
    if ($_src_id > 0){
        $timesheets = get_timesheets($_id);
        if (count($timesheets) > 0){ // has time sheet
        // clear existing time sheet
        $query = "DELETE FROM facility_timesheet WHERE id_facility = $_id";
        mysql_query($query);
        // copy the time sheet from selected facility
        $query = "INSERT INTO facility_timesheet (id_facility, time_start, time_end) 
                    SELECT $_id, time_start, time_end FROM facility_timesheet 
                    WHERE id_facility = $_src_id ";
        mysql_query($query);   
        $rows = mysql_affected_rows();
        $_msg = "Copy time sheet ($rows periods) from '$facility_data_from[facility_no]' to '$facility_data[facility_no]' is succeed!";
    } else {
        $_msg = "Copy can not be performed. Facility '$facility_data_from[facility_no]' does not has time sheet!";
    }
	echo '<script>alert("'.$_msg.'");location.href="./?mod=facility&sub=timesheet&act=view&id='.$_id.'"</script>';
	return;
		
    }
    /*
	$_id = isset($_POST['id']) ? $_POST['id'] : 0;
	ob_clean();
	header('Location: ./?mod=facility&sub=facility&act=del&type='.$_type.'&id=' . $_id);
	ob_flush();
	ob_end_flush();
	exit;
    */
}		

$facility_data = get_facility($_id);

?>
<script>
 function copy_timesheet(){
  var frm = document.forms[0];
  var facility_id = frm.id_facility.options[frm.id_facility.selectedIndex].value;
  var facility_no = frm.id_facility.options[frm.id_facility.selectedIndex].innerHTML;
  if (facility_id == frm.dest.value){
    alert("You can not copy from the same facility!");
    return false;
  }
  return confirm('Are you sure copy the time sheet from "'+facility_no+'"?');
 }
 
</script>
<br/>
<br/>
<form method="POST">
<input type="hidden" name="dest" value="<?php echo $facility_data['id_facility']?>">
<table width=400 class="itemlist" cellpadding=2 cellspacing=1>
<tr><th colspan=2>Copy Time Sheet for "<?php echo $facility_data['facility_no']?>"</th></tr>
<tr>
  <td width=200>Copy from Facility  </td>
  <td><?php echo build_combo('id_facility', get_facility_list())?></td>
</tr>
<tr valign="middle">
  <th colspan=2><br/>
	<!--input type="button" name="cancel" value=" Cancel " onclick='location.href="./?mod=facility&sub=facility&act=list"'/-->
	<input type="submit" name="copy" value=" Copy Time Sheet " onclick="return copy_timesheet()">
	
    </th>
</tr>
</table>
</form>

<br>