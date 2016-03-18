<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_view) {
    include 'unauthorized.php';
    return;
}
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;

$issue = get_condemned_issue($_id);
if (empty($issue)){
    ob_clean();
    header('Location: ./?mod=condemned' );
    ob_end_flush();
    exit;
} else if ($issue['issue_status'] != 'CONDEMNED'){
    ob_clean();
    header('Location: ./?mod=condemned&act=view&id=' . $_id);
    ob_end_flush();
    exit;
}

$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

$issue_items = get_item_serial_by_condemned($_id);
$items = array();
$no = 1;
foreach ($issue_items as $id => $item)
  $items[] = ($no++) . ". <a href='./?mod=item&act=view&id=$id'>$item</a>";
$item_list = implode("<br/>\r\n", $items);
$users = get_user_list();


?>

<h4>View Condemned Certificate</h4>
<table  class="condemned_table" cellpadding=2 cellspacing=1 id="condemnation">
<tr valign="top">
    <td width="50%">
    <table width="100%" cellpadding=2 cellspacing=1 class="request" >
      <tr valign="top" align="left">
        <th align="left" width=130 >Recommended by</td>
        <th align="left"><?php echo $issue['issued_by_name']?></th>
      </tr>  
      <tr valign="top">  
        <td align="left">Date/Time of Issue</td>
        <td align="left"><?php echo $issue['issue_datetime'] ?></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Remarks on</td>
        <td align="left"><?php echo $issue['issue_remark']?></td>    
      </tr>
    </table>
    <br/>
    <div>
        <strong>Asset / Serial No. to be condemned</strong><br/>&nbsp; 
           <div id="seriallist"><?php echo $item_list?></div>
    </div>
    </td>
    <td width="50%">
      <table cellpadding=3 cellspacing=1 class="condemnview approved"  width="100%">
        <tr align="left">
          <th align="left" width=130>Approved by</th>
          <th align="left"><?php echo $users[$issue['approved_by']]?></th>
        </tr>
        <tr valign="top" class="alt">  
          <td align="left">Date/Time of Approval</td>
          <td  align="left"><?php echo $issue['approval_datetime']?></td>
        </tr>
        <tr valign="top">  
          <td align="left">Remarks on</td>
          <td  align="left"><?php echo $issue['approval_remark']?></td>    
        </tr>
        <tr valign="top" class="alt">  
          <td align="left">Signature</td>
          <td align="left"><img class='signature' src="<?php echo get_condemned_signature($_id, 'approval')?>"></td>
        </tr>
      </table>    
      <br/>
      <table cellpadding=3 cellspacing=1 class="condemnview approval pending"  width="100%">
        <tr align="left">
          <th align="left" width=130>Condemned By</th>
          <th align="left"><?php echo $users[$issue['condemned_by']]?></th>
        </tr>
        <tr valign="top" class="alt">  
          <td align="left">Date/Time of Condemnation</td>
          <td align="left"><?php echo $issue['condemn_datetime']?></td>
        </tr>
        <tr valign="top">  
          <td align="left">Remarks </td>
          <td align="left"><?php echo $issue['condemn_remark']?></td>    
        </tr>
        <tr valign="top" class="alt">  
          <td align="left">Signature</td>
          <td align="left"><img class='signature' src="<?php echo get_condemned_signature($_id, 'condemn')?>"></td>
        </tr>
      </table>   
    </td>
</tr>
</table>

<br/><br/>


