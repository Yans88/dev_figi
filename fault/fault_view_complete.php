<?php

if (!defined('FIGIPASS')) exit;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;

$today = date('j-M-Y');
$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

// get request data

$rec = get_fault_request($_id);
$rectification = get_fault_rectification($_id);
$users = get_user_list();
$rectified_by= !empty($users[$rectification['rectified_by']]) ? $users[$rectification['rectified_by']] : null;
$completed_by= !empty($users[$rectification['completed_by']]) ? $users[$rectification['completed_by']] : null;


$caption = 'Completed Fault Rectification (View)';
echo "<h4>$caption</h4>";
echo display_fault_report($rec);
$rec = array_merge($rec, $rectification);
$rec['rectified_by'] = $rectified_by;
$rec['completed_by'] = $completed_by;
echo display_fault_rectified($rec);
echo display_fault_completion($rec);

echo '<div class="right" style="padding-right:240px;">&nbsp;<br/>';
echo '<a class="button" onclick="print_preview()" href="javascript:void(0)">Print Preview</a>';
if (($rec['fault_status'] == FAULT_PROGRESS) && $i_can_update) {   
?>
    
    <button type="button" onclick="location.href='./?mod=fault&act=complete&id=<?php echo $_id?>'">
        Completion
    </button>
  
  
<?php
} // without approval, automatic approved 

	

?>
</div>
<script type="text/javascript">
function print_preview()
{
  var href='./?mod=fault&sub=fault&act=print_completed&id=<?php echo $_id?>'; 
  var w = window.open(href, 'print_issue');  
}
</script>