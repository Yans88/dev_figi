<div style="color:#fff;">

<?php
if (!defined('FIGIPASS')) exit;
  
if ($_sub == null) $_sub = 'facility';
$mod_url = './?mod=facility';
$submod_url = $mod_url;
$current_url = $mod_url;

global $transaction_prefix;
$transaction_prefif = TRX_PREFIX_FACILITY;

$page_access = get_page_privileges(USERGROUP, get_page_id_by_name('facility'));
$i_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));      // can see list/detail
$i_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));// can create/make/submit request
$i_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));// can make issue request / receive item
$i_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));// can approve request

/*
if (!$i_can_view)
	if ($i_can_create) $_act = 'submit';
	else return;
*/
$_path = 'facility/' . $_sub . '.php';

if (!file_exists($_path)) return;

include_once 'item/item_util.php';
include_once 'facility/facility_util.php';

if (defined('USE_NEW_BOOKING') && USE_NEW_BOOKING)
	require_once 'booking/booking_util.php';

if (($i_can_view || $i_can_create) && (USERGROUP != GRPTEA)) {
?>
<div align="center" id="facility_management">
<table width="100%" border=0>
<tr>
  <td align="left" width="30%"><h3>Facility Management</h3></td>
  <td align="right">
  <?php
    if ($i_can_view){
        echo '<a class="button" href="?mod=facility&sub=facility&act=list">Facilities</a> '; 
        if (defined('USE_NEW_BOOKING') && USE_NEW_BOOKING){
		echo '<a href="./?mod=facility&sub=period_term" class="button">Terms Management</a> ';
     	   	} else {
			echo '<a class="button" href="?mod=facility&sub=booking&act=list">Booking Calendar</a> '; 
		}
	}

    if ($i_can_update){
	if (USERGROUP==GRPADM)
    	//echo '<a class="button" href="?mod=maintenance&sub=checking">Maintenance Checklist</a> '; 
        echo '<a class="button" href="?mod=facility&sub=facility&sub=fixed_item">Fixed Item</a> ';
        echo '<a class="button" href="?mod=facility&sub=setting">Setting</a> '; 		
    	
    }
?>
	</td>
</tr>
</table>
<?php
}

	//echo '<div style="width: 800px">';
	require($_path);
	
	//echo '</div>'
?>
</div>
</div>