<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}

$_id = isset($_GET['id']) ? $_GET['id'] : 0;

$today = date('j-M-Y');
$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

if (isset($_POST['complete']) && ($_POST['complete'] == 1)){
    // store rectify-out
    $completed_by = USERID;
    $complete_date = convert_date($_POST['completion_date'], 'Y-m-d H:i:s');
    $query = "UPDATE fault_rectification SET completed_by = '$completed_by', 
              completion_date ='$complete_date', completion_remark ='$_POST[completion_remark]' 
              WHERE id_fault = $_id ";
    mysql_query($query);
    if (mysql_affected_rows()>0){
        $query = "UPDATE fault_report SET fault_status = 'COMPLETED' WHERE id_fault = $_id";
        mysql_query($query);      
    }
    // sending notification
    send_fault_report_completed_notification($_id);
    // avoid refreshing the page
    ob_clean();
    header('Location: ./?mod=fault&sub=fault&act=view_complete&id=' . $_id);
    ob_end_flush();
    exit;
}


// get request data

$rec = get_fault_request($_id);
$rectification = get_fault_rectification($_id);
$users = get_user_list();
$rectified_by = !empty($users[$rectification['rectified_by']]) ? $users[$rectification['rectified_by']] : null;
$completion_date = date('Y-M-d H:i');
$completed_by = $users[USERID];

$caption = 'Completion of Fault Rectification';
echo "<h4>$caption</h4>";
echo display_fault_report($rec);
$rec = array_merge($rec, $rectification);
$rec['rectified_by'] = $rectified_by;
echo display_fault_rectified($rec);
echo <<<TEXT
<form method="post">
    <table  cellpadding=4 cellspacing=1 class="fault_table detail" >
      <tr valign="middle" align="left">
        <th align="left" colspan=2>Fault Completion</th>
      </tr>  
      <tr valign="middle" align="left">
        <td align="left" width=140>Completed by</td>
        <td align="left">$completed_by</td>
      </tr>  
      <tr valign="middle">  
        <td align="left">Date of Completion</td>
        <td align="left"><input type=text name=completion_date id=completion_date size=20 value="$completion_date">
            <input type="image" id="button_completion_date" src="images/cal.jpg" alt="[calendar icon]"/>
            <script>
			$('#button_completion_date').click(
			  function(e) {
				$('#completion_date').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y %H:%i"}).focus();
				e.preventDefault();
			  } );
            </script>
        </td>    
      </tr>
      <tr valign="top" class="alt" >  
        <td align="left">Remark</td>
        <td align="left"><textarea name=completion_remark cols=50 rows=4></textarea></td>    
      </tr>
    <tr>
       <td align="right" valign="middle" colspan=2>
       <a class="button" id="btn_submit" >Submit Completion</a>
       </td>
    </tr>
    </table>

<input type=hidden name="complete">
</form>
<script>
$('#btn_submit').click(function(e){
    var frm = document.forms[0];
    if (frm.completion_remark.value == ''){
        alert('Please fill in remark for completion!');
        return false;
    }
    frm.complete.value = 1;
    frm.submit();
});
</script>

TEXT;

?>
<br>&nbsp;
<br>&nbsp;
