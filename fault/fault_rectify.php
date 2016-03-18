<?php

if (!defined('FIGIPASS')) exit;

if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}


$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$today = date('j-M-Y');
if (isset($_POST['rectify']) && ($_POST['rectify'] == 1)){
    $rectify_date = convert_date($_POST['rectify_date'], 'Y-m-d H:i:s');
    $query = "UPDATE fault_report SET fault_status = 'PROGRESS' WHERE id_fault=$_id";
    mysql_query($query);
    
    $admin_id = USERID;
    $query = "INSERT INTO fault_rectification(id_fault, rectified_by, rectify_date, rectify_remark) 
              VALUES($_id, $admin_id, '$rectify_date', '$_POST[rectify_remark]')";
    mysql_query($query);
    ob_clean();
    header('Location: ./?mod=fault&act=view_rectify&id=' . $_id);
    ob_end_flush();
    exit;
}

$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

$rec = get_fault_request($_id);
  
$admin_name = USERNAME;

$rectify = array();
$rec['rectify_date'] = $today . date(' H:i');    
$rec['rectified_by'] = USERNAME;    
$rec['rectify_remark'] = '';    
$rectify_date = date('d-M-Y H:i');

$caption = 'Fault Rectification';
echo <<<TEXT
<script>
function submit_rectify(){
    var frm = document.forms[0]
    var ok = confirm('Will you rectify this fault report?');
    if (!ok)
        return false;
    frm.rectify.value = 1;
    frm.submit();
    return true;
}
</script>
<h4>$caption</h4>
<form method="post">
TEXT;
echo display_fault_report($rec);

echo <<<TEXT2
    
    <table cellpadding=4 cellspacing=1 class="fault_table detail" >
      <tr valign="middle" align="left">
        <th align="left" width=140>Rectified by</td>
        <th align="left">$rec[full_name]</th>
      </tr>  
      <tr valign="top" class="normal">  
        <td align="left">Date of Rectification</td>
        <td valign="middle" align="left"><input type=text name=rectify_date id=rectify_date size=20 value="$rectify_date">
            <input type="image" id="button_rectify_date" src="images/cal.jpg" alt="[calendar icon]" onclick="return false" />
            <script>
			$('#button_rectify_date').click(
			  function(e) {
				$('#rectify_date').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y %H:%i"}).focus();
				e.preventDefault();
			  } );
            </script>
        </td>    
      </tr>
      <tr valign="top" class="alt" >  
        <td align="left">Remark</td>
        <td align="left"><textarea name=rectify_remark cols=50 rows=4>$rec[rectify_remark]</textarea></td>    
      </tr>
      <tr><td colspan=2 align="right">
       <a class="button" onclick="return submit_rectify()" >Submit Rectification</a>
      </td></tr>
    </table>
    </td>
</tr>
<tr class="normal">
   <td align="right" valign="middle" colspan=2>
   </td>
</tr>
</table>
<Input type=hidden name=rectify>
<Input type=hidden name=rectify_signature>
</form>
TEXT2;
?>
&nbsp;
