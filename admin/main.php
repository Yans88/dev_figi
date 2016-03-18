<?php
/*
main
*/

if (!defined('FIGIPASS')) return;
$i_can_create = (USERGROUP == GRPADM);
$i_can_update = (USERGROUP == GRPADM);

?>
<ul class="adminmenu">
        <li><a href="./?mod=admin&sub=setting">Global Setting</a></li>
        <li><a href="./?mod=admin&sub=backuprestore&act=backup">Data Backup</a></li>
        <li><a href="./?mod=admin&sub=backuprestore&act=restore">Data Restore</a></li>
</ul>
<?php
require_once 'loan/loan_util.php';

if (!empty($_sub) && !empty($_mod)){
    
    require_once $_mod . '/' . $_sub . '.php';
    
} else {

$msgbox = null;
// get loan_return_due_date
$rs = loan_return_due_date_query();
if ($rs && mysql_num_rows($rs) > 0){
    $msgbox .= '<div class="notification" ><div class="message"><h4>List of Due Date or Over Due of Loan</h4>';
    $msgbox .= '<table width=550 cellpadding=3 cellspacing=1 class="itemlist">';
    $msgbox .= '<tr><th>Request No.</th><th>Requester</th><th>Loan Start</th><th>Return Date</th><th>Category</th><th>Quantity</th></tr>';
    while ($rec = mysql_fetch_assoc($rs)){
        $msgbox .= '<tr><td><a href="./?mod=loan&act=view_issue&id='.$rec['id_loan'].'">LR' . $rec['id_loan'] . '</a></td><td>';
        $msgbox .=  $rec['name'] . '</td><td align="center">' . $rec['loan_date'] . '</td><td align="center">' . $rec['return_date'];
        $msgbox .= '</td><td align="center">' . $rec['category_name'] . '</td><td align="center">' . $rec['quantity'] . '</td></tr>';
        
    }
    $msgbox .= '</table></div></div>';
}

?>
	<br/>
    <?php echo $msgbox?>
      <br/>
      <br/>
	<div id="generalmenu" align="center">
<?php if (SUPERADMIN) { ?>    
    <div id="leftbox" class="menubox">
      <div class="menuboxhead">Manage Users</div>
      <ul class="menuboxbody">
        <li><a href="./?mod=user">User List</a></li>
        <li><a href="./?mod=user&act=edit">Create New User</a></li>
        <li><a href="./?mod=user&sub=group">Group List</a></li>
      </ul>
    </div>
<?php } ?>    

    <div id="rightbox" class="menubox">
      <div class="menuboxhead">Miscellaneous Setting</div>
      <ul class="menuboxbody">
        <li><a href="./?sub=location">Location</a></li>
<?php
    if (SUPERADMIN) {
?>
        <li><a href="./?mod=admin&sub=setting">Global Setting</a></li>
        <li><a href="./?mod=admin&sub=backuprestore">Data Backup & Restore</a></li>
<?php
    }
?>
      </ul>
    </div>
    </div>
    <!--
    <div id="rightbox" class="menubox">
      <div class="menuboxhead">Manage Payment</div>
      <ul class="menuboxbody">
        <li><a href="./?mod=payment&act=generate">Generate Payment Schedule</a></li>
        <li><a href="./?mod=payment">View Payment Schadule</a></li>
      </ul>
    </div>
    </div>
    -->
    
<?php
} // sub without mod
?>