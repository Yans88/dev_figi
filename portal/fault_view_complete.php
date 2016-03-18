<?php
//if (!defined('FIGIPASS')) exit;
echo '<div id="itemedit"><br>';
require_once('../fault/fault_util.php');
require_once('../fault/fault_view_complete.php');
echo '</div>';
echo '<div><a href="./?mod=portal&sub=history&portal=fault">Back to Request History</a></div>';

return;


$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$format_date = '%d-%b-%Y %H:%i:%s';
$rec = get_fault_request($_id);
$transaction_prefix = TRX_PREFIX_FAULT;

$caption = 'Notified Fault Report (View)';
  
echo <<<TEXT
<h4><br/>View Fault Report</h4>
<table width="100%" cellpadding=2 cellspacing=1 class="">
<tr valign="top">
    <td width="50%" >
      <table width="100%" cellpadding=4 cellspacing=1 class="fault_table " >
        <tr valign="top" align="left">
          <th align="left" width=120>No</td>
          <th align="left">$transaction_prefix$rec[id_fault]</th>
        </tr>  
        <tr valign="top" class="alt">  
          <td align="left">Date/Time of Report</td>
          <td align="left">$rec[report_date]</td>
        </tr>
        <tr valign="top" class="normal">  
          <td align="left">Reporter</td>
          <td align="left">$rec[full_name]</td>
        </tr>  
        <tr valign="top" class="alt">  
          <td align="left">Fault Date/Time</td>
          <td align="left">$rec[fault_date]</td>
        </tr>
        <tr valign="top" class="normal">  
          <td align="left">Category</td>
          <td align="left">$rec[category_name]</td>
        </tr>  
        <tr valign="top" class="alt">  
          <td align="left">Fault Location</td>
          <td align="left">$rec[fault_location]</td>    
        </tr>
        <tr valign="top" class="normal">  
          <td align="left">Fault Description</td>
          <td align="left">$rec[fault_description]</td>    
        </tr>
      </table>
      <br/>
    </td>
TEXT;

if (($rec['fault_status'] == FAULT_PROGRESS) || ($rec['fault_status'] == FAULT_COMPLETED)){
    $users = get_user_list();
    $rectification = get_fault_rectification($_id);
    $rectified_by= !empty($users[$rectification['rectified_by']]) ? $users[$rectification['rectified_by']] : null;
    $completed_by= !empty($users[$rectification['completed_by']]) ? $users[$rectification['completed_by']] : null;
    
	echo <<<TEXT2
    <td>
<table width="370" cellpadding=2 cellspacing=1 class="service_table cellform" >
  <tr valign="middle" align="left">
    <th align="left" width=100>Rectified by</td>
    <th align="left">$rectified_by</th>
  </tr>  
  <tr valign="top">  
    <td align="left">Date of Rectification</td>
    <td align="left">$rectification[rectify_date]</td>    
  </tr>
  <tr valign="top" class="alt" >  
    <td align="left">Remark</td><td align="left">$rectification[rectify_remark]</td>    
  </tr>
</table>

TEXT2;

    if ($rec['fault_status'] == FAULT_COMPLETED){
        echo <<<TEXT3
    <table width="370" cellpadding=2 cellspacing=1 class="service_table " >
      <tr valign="middle" align="left">
        <th align="left" width=100>Completed by</td>
        <th align="left">$completed_by</th>
      </tr>  
      <tr valign="top">  
        <td align="left">Date of Completion</td>
        <td align="left">$rectification[completion_date]</td>    
      </tr>
      <tr valign="top" class="alt" >  
        <td align="left">Remark</td><td align="left">$rectification[completion_remark]</td>    
      </tr>
    </table>
    <br/>

TEXT3;
    }
    echo '</td>';
}

?>
</tr>
<tr><td colspan=2 align="center"><a href="./?mod=portal&sub=history&portal=fault">Back to Request History</a></td></tr>
</table>

<br/>&nbsp;

