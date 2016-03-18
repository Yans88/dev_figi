<div style="color:#fff;">
<style>.mod_links{float:right;}</style>
<?php
if (!defined('FIGIPASS')) return;
  
if ($_sub == null)	$_sub = 'loan';
if (empty($_act)) $_act = 'list';

global $transaction_prefix, $config;
$transaction_prefix = TRX_PREFIX_LOAN;
$config = @$configuration['loan'];

/*
$page_access = get_page_privileges(USERGROUP, get_page_id_by_name($_sub));
$i_can_view = (isset($page_access[CAN_VIEW] ) && ($page_access[CAN_VIEW] == 1));      // can see list/detail
$i_can_create = (isset($page_access[CAN_CREATE] ) && ($page_access[CAN_CREATE] == 1));// can create/make/submit request
$i_can_update = (isset($page_access[CAN_UPDATE] ) && ($page_access[CAN_UPDATE] == 1));// can make issue request / receive item
$i_can_delete = (isset($page_access[CAN_DELETE] ) && ($page_access[CAN_DELETE] == 1));// can approve request
if (SUPERADMIN) {
	$i_can_delete = false;
	$i_can_update = false;	
}
if (!$i_can_view)
	if ($i_can_create)
		$_act = 'submit';
	else
		return;
*/
$i_can_create = $i_can_view = $i_can_update = $i_can_delete = 1;
$_path = 'loan/' . $_sub . '.php';

if (!file_exists($_path)) 
	return;
include_once 'item/item_util.php';
include_once 'loan/loan_util.php';

if (($i_can_view || $i_can_create) && (USERGROUP != GRPTEA)) {
$sub_title = null;
if (!empty($_sub)) $sub_title = ' :: '.ucwords(str_replace('_',' ', $_sub));
if (!empty($_act) && substr($_sub, 0, 5)!='quick') {
	$cols = explode('_', $_act);
	$real_act = $cols[0];
	$sub_title .= ' '.ucwords($real_act);
}
/*
if ($_act == 'list'){
	$_status = (!empty($_GET['status'])) ? $_GET['status'] : 'pending';
	$sub_title = ' :: '.ucwords($_status . ' ' . $_sub .' '. $_act);
}
*/
?>
<link rel="stylesheet" type="text/css" href="./style/default/anytimec.css" />
<script type="text/javascript" src="./js/anytimec.js"></script>
<div id="item_management">

	<table id="modhead">
	<tr>
	<td align="left" width="21%" id="modtitle" valign="top"><h3>Loan Management <?php echo $sub_title?></h3></td>
	<td align="right" id="modmenu">
	
  <?php
  $page_quickLR = get_page_privileges(USERGROUP, get_pages_id_by_name('Quick loan & return'));
  $i_can_view_quickLR = (isset($page_quickLR[CAN_VIEW] ) && ($page_quickLR[CAN_VIEW] == 1)); 
  
  $page_quickSLR = get_page_privileges(USERGROUP, get_pages_id_by_name('Student Loan & Return'));
  $i_can_view_quickSLR = (isset($page_quickSLR[CAN_VIEW] ) && ($page_quickSLR[CAN_VIEW] == 1));
  
    if ($i_can_create) { 
		if(!SUPERADMIN){
			//if (quick_loan_enabled && SUPERADMIN)
				if(quick_loan_enabled){
					if($i_can_view_quickLR){
						echo '<a class="button" href="?mod=loan&sub=quick_loan_issue">Quick Loan</a> ';
					}	
				}							
			if(quick_return_enabled){
				if($i_can_view_quickLR){
					echo '<a class="button" href="?mod=loan&sub=quick_return">Quick Return</a> ';
				}					
			}	
			$page_quickAR = get_page_privileges(USERGROUP, get_pages_id_by_name('Loan (Walk-In)'));
			$i_can_view_quickAR = (isset($page_quickAR[CAN_VIEW] ) && ($page_quickAR[CAN_VIEW] == 1));
			if($i_can_view_quickAR){
				echo '<a class="button" href="?mod=portal&portal=loan&act=submit">Advanced Request</a> ';
			}
			
		}				
		if (!REQUIRE_LOAN_APPROVAL){
			$page_quickLW = get_page_privileges(USERGROUP, get_pages_id_by_name('Loan (Walk-In)'));
			$i_can_view_quickLW = (isset($page_quickLW[CAN_VIEW] ) && ($page_quickLW[CAN_VIEW] == 1));
			if($i_can_view_quickLW){
				echo '<a class="button" href="?mod=loan&sub=loan&act=walkin">Walk-in Loan</a> ';
			}
			
		}
		
    }
echo '</td></tr><tr><td align="right" id="modmenu"  colspan=6>';
	if ($i_can_view){
		echo '<a class="button" href="?mod=loan&sub=loan&act=list">Loan Return</a> '; 
		if(student_loan){
			if($i_can_view_quickSLR){
				echo '<a class="button" href="?mod=portal&portal=loan&act=submit&loan_student=1">Student Loan</a> ';
			}	
		}
		if($i_can_view_quickSLR){
			echo '<a class="button" href="?mod=loan&sub=loan&status=completed&student_loan=1">Student Return</a>&nbsp;';	
		}		
		echo '<a class="button" href="?mod=user&act=loan">Individual Loan Request</a> ';
	}
        
    if (!SUPERADMIN && $i_can_update)
        echo '<a class="button" href="?mod=loan&sub=setting">Setting</a>'; 
?>
</td>
</tr>
    </table>
    </div>
<br/>

<div class="clear"></div>
<?php
}
  include($_path);
?>
</div>
