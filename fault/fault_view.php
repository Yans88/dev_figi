<?php

if (!defined('FIGIPASS')) exit;

$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_msg = null;
$format_date = '%d-%b-%Y %H:%i:%s';
$rec = get_fault_request($_id);

$caption = 'Notified Fault Report (View)';
  
echo "<h4 class='left'>$caption</h4>";
echo display_fault_report($rec);


if (($rec['fault_status'] == FAULT_NOTIFIED) && $i_can_update) {   
?>
<p>
    <div class="fault_table" style=" text-align: right">
    <a class="button" 
        onclick="location.href='./?mod=fault&act=machrec&id=<?php echo $_id?>'">
       Create Machine Record
    </a>
    <a class="button" 
        onclick="location.href='./?mod=fault&act=rectify&id=<?php echo $_id?>'">
       Rectify the Fault
    </a>
  </div><br/>
</p>  
<?php
} // without approval, automatic approved 

?>
