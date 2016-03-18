<?php

if (!defined('FIGIPASS')) exit;
if (!$i_can_update) {
    include 'unauthorized.php';
    return;
}
$dept = USERDEPT;
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$_items = isset($_POST['items']) ? $_POST['items'] : null;
$_serialno = isset($_POST['serial_no']) ? $_POST['serial_no'] : null;
$_msg = null;
$today = date('j-M-Y H:i');


$request = get_request($_id);
$requester = get_user($request['id_user']);
$parent_info = get_parent_info($request['id_user']);
$parent_name = $parent_info['father_name'];
$students_loan = $request['students_loan'];

//add 18052025 by hansen for bug in walk in
$item_id = get_id_item($_id);
//print_r($item_id);
if($item_id != null){
				?>
				<style>
					.del, .delImg{
						display:none;
					}
				</style>
<?php				
			}

$serialNo = array();
$assetNo = array();
foreach($item_id as $id){
	$data_item = get_item_walkin($id['id_item']);
	//echo $data_item;
	foreach($data_item as $dataItem){	
		$serialNo[] = $dataItem['serial_no'];
		$assetNo[] = $dataItem['asset_no'];        
}
}
$serialNo = implode(',',$serialNo);
$assetNo = implode(',',$assetNo);
//print_r($data_item);
// End of bug in loan walk in
/////////////////////////////////////
$get_category = get_request_category($_id);
$cn = $request['category_name'];
$cat_name = array();
foreach($get_category as $row){
	$cat_name[] = $row['category_name'];
}
if($request['category_name']==null){
	$request['category_name'] = implode(',',$cat_name);
}
/////////////////////////////////////

$need_approval = ($request['without_approval'] == 0);
$item_requested = get_request_item($_id);
error_log('post: '.serialize($_POST));
if (isset($_POST['issue']) && ($_POST['issue'] == 4)){    
    $items = get_item_from_serial_no($_items); // asset_no|serial_no,asset_no|serial_no,...
    if (count($items) > 0) { // selected item found
        // store loan-out
        $this_time = date(' H:i:s');
		$quick_issue = 0;//isset($_POST['quick_issue']) ? $_POST['quick_issue'] : 0;
        $loan_date = convert_date($_POST['loan_date'], 'Y-m-d').$this_time;
        $return_date = convert_date($_POST['date_to_be_returned'], 'Y-m-d').$this_time;
        $chk = $_POST['loan_checklist'];        
		
        $query = "REPLACE INTO loan_out_as_draft(id_loan, name, nric, contact_no, id_location, id_department, loan_date, return_date, checklist ) 
                  VALUES ($_id, '$_POST[name]', '$_POST[nric]', '$_POST[contact_no]', '$_POST[id_location]', '$_POST[id_department]', 
                  '$loan_date', '$return_date', '$chk')";
        mysql_query($query);
		error_log('update loan_out data');
        if (mysql_affected_rows()>0){
            $values = array();
            foreach ($items as $id_item){
                if (preg_match('/^[0-9]+$/', $id_item) > 0)
                    $values[] = "($_id, $id_item)";
            }            
            // delete if existing items
            mysql_query("DELETE FROM loan_item WHERE id_loan = $_id");
            if (count($values)>0){
                $query = "INSERT INTO loan_item(id_loan, id_item) VALUES " . implode(', ', $values);
                mysql_query($query);
                //echo mysql_error().$query;
            }
            // update request
            //$query = "UPDATE loan_request SET status = 'LOANED' WHERE id_loan=$_id";
           // mysql_query($query);
            // update loan process for approval type, otherwise insert
            $admin_id = USERID;
			$now = date('Y-m-d H:i:s');
            if ($need_approval)
                $query = "UPDATE loan_process SET 
                          issued_by = $admin_id, 
                          issue_date = '$now', 
                          issue_remark = '$_POST[issue_remark]', 
                          loaned_by = 0, 
                          loan_date = '$now',  
                          loan_remark = '$_POST[loan_remark]' 
                          WHERE id_loan = $_id";
            else
                $query = "REPLACE INTO loan_process(id_loan, issued_by, issue_date, issue_remark, loaned_by, loan_date, loan_remark) 
                          VALUES($_id, $admin_id, '$now', '$_POST[issue_remark]', 0, '$now', '$_POST[loan_remark]')";
            mysql_query($query);
            // keep signature
            if ($need_approval)
                $query = "UPDATE loan_signature SET 
                          issue_sign = '$_POST[issue_signature]', 
                          loan_sign = '$_POST[loan_signature]' 
                          WHERE id_loan = $_id";
            else
                $query = "REPLACE INTO loan_signature(id_loan, issue_sign, loan_sign)
                          VALUES($_id, '$_POST[issue_signature]', '$_POST[loan_signature]')";
            mysql_query($query);
            
           
			// save item's accessories
			$accessories = explode('~~', $_POST['accs']);
			if (count($accessories)>0){
				$values = array();
				foreach($accessories as $line){
					if (preg_match('/^(\d+):(.+)$/', $line, $matches)){
						$id_item = $matches[1];
						$accs = explode('|', $matches[2]);
						foreach($accs as $idacc)
							$values[] = '(' . $_id . ',' . $idacc . ', '. $id_item.')';
					}
				}
				if (count($values)>0){
					$query = "INSERT INTO loan_item_accessories VALUES " . implode(',', $values);
					mysql_query($query);
				}
			}
            // send notification
            $request = get_request($_id);
			//error_log('Sending alert for issuance....');
            send_loan_issued_alert($request);
            // avoid refreshing the page
            goto_view($_id, LOANED);
        } else 
			error_log('error: '.mysql_error().$query);
	} else $_msg = 'There is no item selected !';
}

if (isset($_POST['issue']) && ($_POST['issue'] == 1)){    
    $items = get_item_from_serial_no($_items); // asset_no|serial_no,asset_no|serial_no,...
    if (count($items) > 0) { // selected item found
        // store loan-out
        $this_time = date(' H:i:s');
		$quick_issue = 0;//isset($_POST['quick_issue']) ? $_POST['quick_issue'] : 0;
        $loan_date = convert_date($_POST['loan_date'], 'Y-m-d').$this_time;
        $return_date = convert_date($_POST['date_to_be_returned'], 'Y-m-d').$this_time;
        $chk = $_POST['loan_checklist'];        
		
        $query = "REPLACE INTO loan_out(id_loan, name, nric, contact_no, id_location, id_department, loan_date, return_date, checklist ) 
                  VALUES ($_id, '$_POST[name]', '$_POST[nric]', '$_POST[contact_no]', '$_POST[id_location]', '$_POST[id_department]', 
                  '$loan_date', '$return_date', '$chk')";
        mysql_query($query);
		//error_log('update loan_out data');
        if (mysql_affected_rows()>0){
            $values = array();
            foreach ($items as $id_item){
                if (preg_match('/^[0-9]+$/', $id_item) > 0)
                    $values[] = "($_id, $id_item)";
            }   
			mysql_query("DELETE FROM loan_out_as_draft WHERE id_loan = $_id");
            // delete if existing items
            mysql_query("DELETE FROM loan_item WHERE id_loan = $_id");
            if (count($values)>0){
                $query = "INSERT INTO loan_item(id_loan, id_item) VALUES " . implode(', ', $values);
                mysql_query($query);
                //echo mysql_error().$query;
            }
            // update request
            $query = "UPDATE loan_request SET status = 'LOANED' WHERE id_loan=$_id";
            mysql_query($query);
            // update loan process for approval type, otherwise insert
            $admin_id = USERID;
			$now = date('Y-m-d H:i:s');
            if ($need_approval)
                $query = "UPDATE loan_process SET 
                          issued_by = $admin_id, 
                          issue_date = '$now', 
                          issue_remark = '$_POST[issue_remark]', 
                          loaned_by = 0, 
                          loan_date = '$now',  
                          loan_remark = '$_POST[loan_remark]',
						  parent_remark = '$_POST[loan_remark_parent]',
						  parent_remark_date = '$now',
                          WHERE id_loan = $_id";
            else
                $query = "REPLACE INTO loan_process(id_loan, issued_by, issue_date, issue_remark, loaned_by, loan_date, loan_remark,parent_remark,parent_remark_date) 
                          VALUES($_id, $admin_id, '$now', '$_POST[issue_remark]', 0, '$now', '$_POST[loan_remark]','$_POST[loan_remark_parent]','$now')";
            mysql_query($query);
            // keep signature
            if ($need_approval)
                $query = "UPDATE loan_signature SET 
                          issue_sign = '$_POST[issue_signature]', 
                          loan_sign = '$_POST[loan_signature]' ,
						  parent_loan_sign = '$_POST[parent_signature]'
                          WHERE id_loan = $_id";
            else
                $query = "REPLACE INTO loan_signature(id_loan, issue_sign, loan_sign, parent_loan_sign)
                          VALUES($_id, '$_POST[issue_signature]', '$_POST[loan_signature]', '$_POST[parent_signature]')";
            mysql_query($query);
            // update item's status
            if (count($items)>0){
                $item_status = ($request['long_term']>0) ? ISSUED : ONLOAN;
                $query = "UPDATE item SET status_update = '$now', 
                          id_status = '".$item_status."', issued_to = '$request[id_user]', issued_date = '$now', id_location = '$_POST[id_location]'
                          WHERE id_item in (" . implode(',', $items) . ")";
                mysql_query($query);
              //echo  mysql_error().$query;
            }
			// save item's accessories
			$accessories = explode('~~', $_POST['accs']);
			if (count($accessories)>0){
				$values = array();
				foreach($accessories as $line){
					if (preg_match('/^(\d+):(.+)$/', $line, $matches)){
						$id_item = $matches[1];
						$accs = explode('|', $matches[2]);
						foreach($accs as $idacc)
							$values[] = '(' . $_id . ',' . $idacc . ', '. $id_item.')';
					}
				}
				if (count($values)>0){
					$query = "INSERT INTO loan_item_accessories VALUES " . implode(',', $values);
					mysql_query($query);
				}
			}
            // send notification
            $request = get_request($_id);
			//error_log('Sending alert for issuance....');
            send_loan_issued_alert($request);
            // avoid refreshing the page
            goto_view($_id, LOANED);
        } else 
			error_log('error: '.mysql_error().$query);
	} else $_msg = 'There is no item selected !';
}

$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

$location_list = get_location_list();
if (count($location_list) == 0)
    $location_list[0] = '--- no location available! ---';

$users = get_user_list();
$approval['approved_by'] = null;
$approval['approval_date'] = null;
$approval['approval_remark'] = null;
$approval['approval_sign'] = null;
if ($need_approval && ($request['status'] == APPROVED)){
    $process = get_request_process($_id);
}
 
$issue['issued_by'] = FULLNAME;
$issue['issue_date'] = $today;
$issue['loan_date'] = $today;
$issue['name'] = $requester['full_name'];
$issue['nric'] = $requester['nric'];
$issue['contact_no'] = $requester['contact_no'];
$issue['id_department'] = $requester['id_department'];



//$department_option = build_option(get_department_list(), $requester['id_department']);
$accessories = get_accessory_list($request['id_category']);
$accessories_option = '<ul style="margin: 0;padding: 0" id="accessories_list">';
if($cn==null){
	$o = 0;
	foreach($get_category as $row){
		$accessories = get_accessory_list($row['lid']);
		if (count($accessories)>0){
			$o ++;
			foreach($accessories as $id => $acc)
			$accessories_option .= '<li style="list-style: none;padding-left: 0"><input type="checkbox" name="accessories[]" value="' . $id . '">' . $acc . '</li>';
		}
	}
	if($o == 0)$accessories_option .= "<li style='list-style: none;' id='noacc'>This category doesn't has accessories</li>";
}
else{
$accessories = get_accessory_list($request['id_category']);

	if (count($accessories)>0){
		foreach($accessories as $id => $acc)
			$accessories_option .= '<li style="list-style: none;padding-left: 0"><input type="checkbox" name="accessories[]" value="' . $id . '">' . $acc . '</li>';
	} else {
		$accessories_option .= "<li style='list-style: none;' id='noacc'>This category doesn't has accessories</li>";
	}

}
$accessories_option .= '</ul>';
list($start_loan, $start_time) = explode(' ', $request['start_loan']);
@list($end_loan, $end_time) = explode(' ', $request['end_loan']);
$diff_msec = strtotime($request['end_loan'])-strtotime($request['start_loan']);
$diff_days = round($diff_msec / 86400);
$to_be_returned =  date('d-M-Y H:i', time()+$diff_msec);
//$to_be_returned = $end_loan . date(' H:i');
$category = getCategory($_id);
foreach($category as $cat){
	$id_cat = $cat['id_category'];	
}
$checklist = null;
if ($id_cat>0)
	$cheklist = getCheklist($id_cat);  // 13052025 add by hansen for point 23

$messages['loan_issue_note'] = get_text('loan_issue_note');
if (empty($issue['nric'])) $issue['nric'] = '-';
if (empty($issue['contact_no'])) $issue['contact_no'] = '-';
?>
<style>
.loan_table #edit_item { width: 600px; }
.suggestionsBox { width: 598px; }
</style>

<br>
<form method="post">
<input type="hidden" name="quantity" id="quantity" value="<?php echo $request['quantity']?>">
<input type="hidden" name="items" id="items" value="">
<input type="hidden" name="accs" id="accs" value="">
<input type="hidden" name="serial_number" id="serial_number" value="<?php echo  $serialNo?>">
<input type="hidden" name="asset_number" id="asset_number" value="<?php echo  $assetNo?>">
<input type="hidden" name="iditems" id="iditems" value="">
<input type="hidden" name="id_department" id="id_department" value="<?php echo $issue['id_department']?>">
<input type="hidden" name="nric" id="nric" value="<?php echo $issue['nric']?>">
<input type="hidden" name="name" id="name" value="<?php echo $issue['name']?>">
<input type="hidden" name="contact_no" id="contact_no" value="<?php echo $issue['contact_no']?>">
<input type="hidden" name="loan_checklist" id="loan_checklist" value="">
<input type="hidden" name="loan_date" id="loan_date" value="<?php echo $issue['loan_date']?>">

<?php display_request($request);?>
<div class="space5-top"></div>

<table width="100%"  class="request loan itemlist" >
	<thead>
<tr>
		<th align="left" colspan=6>Loan-Out Details
				<div class="foldtoggle"><a id="btn_loan_issuance" rel="open" href="javascript:void(0)">&uarr;</a></div>
        </th>
    </tr>
	</thead>
    <tbody id="loan_issuance">
      <tr valign="top">  
        <td align="left" width=100>Loan Out to</td>
        <td align="left" width=270 style=""><?php echo $issue['name']?>
		<!--
			<input type="text" name="name" style="width: 260px" id='refname' onkeyup="suggest_user(this, this.value)" onchange="loaned_by_update(this)" value="<?php echo $issue['name']?>">
			<div class="clear"></div>
            <div class="suggestionsBox user" id="suggestions_user" style="display: none; z-index: 500; "> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsList_user"> &nbsp; </div>
            </div>
			-->
		</td>
		<?php
			if($students_loan > 0){
				echo '<td align="center" colspan=2><b>Parent Info</b></td>';
			}
		?>
		
        <td align="center" colspan=2><b>Projected Date to return</b></td>
        </tr>  
      <tr valign="top" class="alt" valign="middle">  
        <td align="left">NRIC</td>
        <td align="left"><?php echo $issue['nric']?></td>
			<?php
			$colspan = 4;
			if($students_loan > 0){
				echo '<td align="left"  width=95>Name</td> <td align="left">'.$parent_name.'</td>';
				$colspan = 0;
			}
		?>		 
        <td align="right" width=140>Sign Out</td>
        <td align="left" width=300><?php echo $issue['issue_date'];?> </td>         
      </tr>  
      <tr valign="top">  
        <td align="left">Contact No.</td>
        <td align="left"><?php echo $issue['contact_no']?></td>    
		<?php
			if($students_loan > 0){
				echo '<td align="left" width=95>Email</td> <td align="left">'.$parent_info['father_email_address'].'</td>';
			}
		?>
		 
        <td align="right">To be Returned</td>
        <td align="left"><input type="text" name="date_to_be_returned" id="date_to_be_returned" size=18 value="<?php echo $to_be_returned?>">
            <a id="button_date_to_be_returned" href="javascript:void(0)"><img class="icon" src="images/cal.jpg" alt="[calendar icon]"/></a>
            <script>
			$('#button_date_to_be_returned').click(
			  function(e) {
				$('#date_to_be_returned').AnyTime_noPicker().AnyTime_picker({format: "%e-%b-%Y %H:%I"}).focus();
				e.preventDefault();
			  }
			  );
			 
        </script>
        </td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Department</td>
        <td align="left" colspan="<?php echo $colspan;?>"> <?php echo $request['department_name']?> </td>  
		<?php
			if($students_loan > 0){
				echo '<td align="left" width=95>Phone number</td> <td align="left" colspan="4">'.$parent_info['father_mobile_number'].'</td>';
			}
		?>
				
      </tr>  
      <tr valign="top">  
        <td align="left">Location</td>
        <td align="left" colspan=6>
           <select name="id_location" id="id_location">
		   <?php echo build_option($location_list );?>
           </select>
        </td>    
      </tr>
       <tr valign="top" align="left" class="alt">
        <td align="left" colspan=6>
			<div class="clear"></div>
			<u>Items:</u>
            <ul id="item_list" style="padding-left: 0px"></ul>
	<div id="item_find">Find item &nbsp; 
            <input type="text" id="edit_item" name="serial_no" onKeyUp="suggest(this, this.value);" autocomplete="off" style="width: 600px">
            <a href="javascript:void(0)" onclick="add_item()"><img class="icon" src="images/add.png"></a>
            <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500; left: 60px"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsList"> &nbsp; </div>
            </div>
		</div>
        </td>
   </tr>
<tr><td colspan=8>
<table width="100%" class="itemlist loan">
<!-- signature -->
<?php

if ($need_approval) {

?>
<tr valign="middle">
    <th width="20%">&nbsp;</th>
    <th width="20%" align="center">Approved By</th>
    <th width="20%" align="center">Issued By</th>
    <th width="20%" align="center">Loaned By</th>
	
</tr>
<tr valign="top">
    <td>Name</td>
    <td><?php echo $approval['approved_by']?></td>
    <td><?php echo $issue['issued_by']?></td>
    <td id="loanedby"><?php echo $issue['name']?></td>
</tr>
<tr valign="top" class="alt">
    <td>Date/Time Signature</td>
    <td><?php echo $approval['approval_date']?></td>
    <td><?php echo $issue['issue_date']?></td>
    <td><?php echo $issue['loan_date']?></td>
</tr>
<tr valign="top">
    <td>Remarks</td>
    <td><?php echo $approval['approval_remark']?></td>
    <td><textarea name="issue_remark" cols=26 rows=3></textarea></td>
    <td><textarea name="loan_remark" cols=26 rows=3></textarea></td>
</tr>
<tr valign="top" class="alt">
    <td>Signatures</td>
    <td><img class='signature' src="<?php echo get_signature($_id, 'approve')?>"></td>
    <td>
        <div id="signature-pad" class="m-signature-pad" style='width: 202px;height: 80px;'>
			<div class="m-signature-pad-body">
			 <canvas id="imageView" height=80 width="200px"></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>			
		</div>
    </td>
    <td>
        <div id="signature-pad2" class="m-signature-pad" style='width: 202px;height: 80px;'>
			<div class="m-signature-pad-body">
			 <canvas id="imageView2" height=80 width="200px"></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
			
		</div>
    </td>
</tr>
<?php
}  // need approval
    else {
?>
<tr valign="middle">
    <th width="20%" rowspan=5>&nbsp;</th>
    <th width="20%" >&nbsp;</th>
    <th width="20%" align="center" >Issued By</th>
    <th width="20%" align="center">Loaned By</th>
	<?php if($students_loan > 0){
		echo '<th width="20%" align="center">Parent</th>';
	}?>
    
</tr>
<tr valign="top">
    <td>Name</td>
    <td><?php echo $issue['issued_by']?></td>
    <td id="loanedby"><?php echo $issue['name']?></td>
	<?php if($students_loan > 0){
		echo '<td id="loaned_by">'.$parent_name.'</td>';
	}?>
	
</tr>
<tr valign="top" class="alt">
    <td>Date/Time Signature</td>
    <td><?php echo $issue['issue_date']?></td>
    <td><?php echo $issue['loan_date']?></td>
	<?php if($students_loan > 0){
		echo '<td>'.$issue['loan_date'].'</td>';
	}?>
	
</tr>
<tr valign="top">
    <td>Remarks</td>
    <td><textarea name="issue_remark" cols=26 rows=3></textarea></td>
    <td><textarea name="loan_remark" cols=26 rows=3></textarea></td>
	<?php if($students_loan > 0){
		echo ' <td><textarea name="loan_remark_parent" cols=26 rows=3></textarea></td>';
	}?>
	
   
</tr>
<tr valign="top" class="alt">
    <td>Signatures</td>
    <td>
        <div id="signature-pad" class="m-signature-pad" style='width: 202px;height: 80px;'>
			<div class="m-signature-pad-body">
			 <canvas id="imageView" height=80 width="200px"></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>			
		</div>
    </td>
    <td>
        <div id="signature-pad2" class="m-signature-pad" style='width: 202px;height: 80px;'>
			<div class="m-signature-pad-body">
			 <canvas id="imageView2" height=80 width="200px"></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
			
		</div>
    </td>
	
	<?php if($students_loan > 0){
		echo ' <td>
        <div id="signature-pad3" class="m-signature-pad" style="width: 202px;height: 80px;">
			<div class="m-signature-pad-body">
			 <canvas id="imageView3" height=80 width="200px"></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
			
		</div>
    </td>';
	}?>
	
	
</tr>
<?php
    } // non-approval
?>    
	</tbody>
</table>
</td></tr>
</tbody>
</table>
<div class="space5-top"></div>

<!-- 13052025 add by hansen for point 23 -->
<table width="100%" class="request itemlist" >
<thead>
<tr>	
	<th align="left" width="100%">Loan-Out Checklist
		<div class="foldtoggle"><a id="btn_loan_checklist_body" rel="open" href="javascript:void(0)">&uarr;</a></div>
	</th>
</tr>	
</thead>
	<tbody id="loan_checklist_body"><tr><td id="loan_checklist_item"></td></tr></tbody>	
</table>
<div class="space5-top"></div>
<!-- end of point 23 -->

<table cellpadding=2 cellspacing=1 width="100%" class="request issue itemlist">

	
	<tr>
		<td><div class="note" id="issue_note" ><?php echo $messages['loan_issue_note']?></div></td>
		<!--<img id="btn_submit" src="images/submit.png" class="button"/ > -->
		
	</tr>
	<tr>
		<td align="right" height="30">
		
		
		<a id="btn_draft" class="button">Save as Draft</a>&nbsp;&nbsp;
		<a id="btn_submit" class="button">Submit</a>&nbsp;&nbsp;</td>
	</tr>
</table>
<Input type="hidden" name="issue">
<Input type="hidden" name="issue_signature">
<Input type="hidden" name="loan_signature">
<Input type="hidden" name="parent_signature">
</form>
<br/><br/>
<div id="dialog_accessories" class="dialog ui-helper-hidden">
<p>Select accessories for the selected item:</p>
<div id="accessories_option"> </div>
<div>
<br>
<button type="button" id="btn_setacc"> Set Accessories </button>
<button type="button" id="btn_close"> Close </button>
</div>
</div>
<style>
#btn_submit:hover {cursor: pointer;}
</style>

<script type="text/javascript" src="./js/signature2.js"></script>
<script type="text/javascript" src="./js/signature.js"></script>
<?php if($students_loan > 0){
	echo '<script type="text/javascript" src="./js/signature3.js"></script>';
}?>
<script type="text/javascript">

var department = '<?php echo $dept ?>';
var category = 0;
var item_accs = new Array();
var all_accs = new Array();
var all_accs_text = new Array();
var window_item = false;
var find_item_invisible = true;     
var current_acc_index = -1;
var students_loan = '<?php echo $students_loan;?>';
var sl = parseInt(students_loan);
var asset = $('#asset_number').val();
var serial = $('#serial_number').val();
$(document).ready(function(){	
	var Myasset = asset.split(',');
	var Myserial = serial.split(',');		
	if(asset.length > 0 || serial.length > 0){		
		for (var i=0; i < Myasset.length; i++){		
           get_item_info(Myasset[i], Myserial[i]); 		   
        }
		//$('#item_find').hide();					
	}	
});


$.fn.selectRange = function(start, end) 
{
    return this.each(function() {
        if (this.setSelectionRange) {
            this.focus();
            this.setSelectionRange(start, end);
        } else if (this.createTextRange) {
            var range = this.createTextRange();
            range.collapse(true);
            range.moveEnd('character', end);
            range.moveStart('character', start);
            range.select();
        }
    });
}

function in_array(search, stack)
{
	for(var i=0; i<stack.length; i++)
		if (stack[i] == search)
			return true;
	return false;
}

function loaned_by_update(out_to)
{
    var loaned_by = document.getElementById('loanedby');
    //loaned_by.innerHTML = out_to.value;
}

function del_item(item)
{
    if (confirm("Are you sure delete the item?")){
        var items = $('#items').val();
        var recs = items.split(',');
        var newrecs = new Array();
        var cols = item.split('|');
        var cart = cols[3];
        var new_all_accs = new Array();
        var new_all_accs_text = new Array();

        for (var i=0; i < recs.length; i++){
             cols = recs[i].split('|');
            if ((recs[i] == item) || ((cart>0) && (cols[3]==cart))) continue;
            newrecs.push(recs[i]);
			new_all_accs.push(all_accs[i]); 
			new_all_accs_text.push(all_accs_text[i]); 
        }
		all_accs_text = new_all_accs_text;
		all_accs = new_all_accs;
        $('#items').val(newrecs);
        display_list(newrecs.join(','));

		if (newrecs.length==0){
			category = 0;
			$('#loan_checklist_item').html('');	
		}
	}
}



function add_item()
{	
    var item = $('#edit_item').val();	
    if (item == '') return;
	//alert(item);
    var items = $('#items').val();	
		//var cols = item.match(/([^ ].+) *\((.+)\)/);
    var cols = item.match(/([^,]+), *([^,]+)/);
    if (cols.length > 2) {
		if (items.search(new RegExp(cols[1]+'|'+cols[2])) == -1){
			get_item_info(cols[1], cols[2]);
			$('#edit_item').val('');
			$('#edit_item').focus();			
		} else {
			alert('Asset No / Serial No already exist in the list!');
		}
    }	
	
	
}

function get_item_info(asset_no, serial_no)
{
	$('#edit_item').attr('disabled', 'disabled');
	var tr = $('tr[class="an_item"]').length;	
    $.post("loan/get_item.php", {asset_no: ""+asset_no+"", serial_no: ""+serial_no +""}, function(data){        
        if(data.length >0) {
            var rows = data.split(',');
            var items = $('#items').val();
            var cnt = 0;
            if (items.length>0)
                cnt=items.split(',').length;
			else {
			var cols = rows[0].split('|');
				category=cols[8];// capture first category from first item
			}
			if(tr == 0 && category > 0)
			{
				$.post("loan/get_checklist.php", {category: ""+category+""}, function(dataa){
					if(dataa.length > 0){
						//display_checklist(dataa);
    					$('#loan_checklist_item').html(dataa);
					} else 
    					$('#loan_checklist_item').html('<p class="info">* checklist is not available!</p>');
				});
			}			
            var text,cols ;
            for (var i=0; i<rows.length;i++){
                cols = rows[i].split('|');

				if (items == '') items = cols.join('|');
                else items += ',' + cols.join('|');
            }
            $('#items').val(items);
            display_list(items);
			all_accs[cnt] = null;
			all_accs_text[cnt] = null;
			//current_acc_index = cnt;
			//$('#cat-'+cols[8]).trigger('click');			
			
        }
		$('#edit_item').removeAttr('disabled');
    });
}

function display_checklist(dataa)
{	
	var dta = dataa.split(',');
	var text = '';
	var title = '';
	var chk_item = '';
	var id_chk = '';
	var ttl_dta = '';
	var id_item = '';
	if (dta != '' && dta.length > 0){
	text ='<tr class="passed"><td colspan="4" align="right"><b>Passed</b></td></tr>';
			
		for(var i = 0; i<dta.length; i++){
			var data_ttl = dta[i].split('|');	
			var clss = (i % 2 == 0) ? 'top' : 'alt';
			if(data_ttl.length > 0 && data_ttl !=''){
				id_chk = data_ttl[0];
				title = data_ttl[1];
				ttl_dta = data_ttl[2];				
				if(data_ttl.length > 2){
					text +='<tr class="'+clss+'"><td width="80%">&nbsp;<b>'+title+'</b></td><td></td><td></td><td></td></tr>';
				}
				if(data_ttl.length == 2){
					text +='<tr class="'+clss+'"><td width="80%">&nbsp;'+title+'</td>';
				    text +='<td><input type="radio" class="chk" id="chk" name="'+title+'" value="1_'+id_chk+'">Yes</td>';
					text +='<td><input type="radio" class="chk" id="chk" name="'+title+'" value="0_'+id_chk+'">No</td>';
					text +='<td><input type="radio" class="chk" id="chk" name="'+title+'" value="2_'+id_chk+'" checked>NA</td></tr>';	
				}					
			}		
			for(var x = 1; x<data_ttl.length;x++){
				var ttl_split = data_ttl[x].split(',');
				for(var y = 0; y<ttl_split.length;y++){
					var ttl_item = ttl_split[y].split('_');
					if(ttl_item.length > 1){
						id_item = ttl_item[0];
						chk_item = ttl_item[1];	
						text  +='<tr class="'+clss+'"><td>&nbsp;&nbsp;&nbsp;&nbsp;'+chk_item+'</td>';
						text +='<td><input type="radio" class="chk" id="chk" name="'+chk_item+'" value="1_'+id_chk+'_'+id_item+'">Yes</td>';
						text +='<td><input type="radio" class="chk" id="chk" name="'+chk_item+'" value="0_'+id_chk+'_'+id_item+'">No</td>';
						text +='<td><input type="radio" class="chk" id="chk" name="'+chk_item+'" value="2_'+id_chk+'_'+id_item+'" checked>NA</td></tr>';	
						//console.log(chk_item);
					}
				}
			}							
		}	
		text += '</table>';
	}else{
        text = '--- no item specified ---';
        }
    $('#loan_checklist_item').html(text);
}


function display_list(items)
{
    var text = '';
    var cols = '';
    var recs = items.split(',');
    if (items != '' && recs.length > 0){
        text  ='<table width="100%" class="grid itemlist">';
		text += '<tr><th>No</th><th>Serial No</th><th>Asset No</th><th>Category</th><th>Brand</th><th>Model</th><th>Accessories</th><th class="del" width=20>Del</th></tr>';
        for (var i=0; i < recs.length; i++){
            cols = recs[i].split('|'); // asset_no|serial_no|id_item|cart|category|brand|model|loan_period|id_category|accessories-list
			var accessories_list = '';
			var acc = '';
			var cn = (i % 2 == 0) ? 'alt' : '';
			if (all_accs_text[i]) acc = all_accs_text[i].join(', ');
			accessories_list = '<span id="acclist-'+i+'">'+acc+'</span><a href="#'+i+'" onclick="addacc(this)" id="cat-'+cols[8]+'" style="font-weight: bold"> + </a>';
			text += '<tr class="an_item '+cn+'" id="' + cols[1] + '"><td>' ;
            text += (i+1) + '. </td><td>' + cols[1] +  '</td><td>' + cols[0] + '</td>';
			text += '<td>'+cols[4]+'</td><td>'+cols[5]+'</td><td>'+cols[6]+'</td><td>'+accessories_list+'</td>';
            text += '<td class="delImg"><a onclick="del_item(\''+ recs[i] +'\')"><img id="delImg" class="icon" src="images/delete.png" alt="delete"></a></td></tr> ';
			
        }
		text += '</table>';
		//console.log(cols[8]);
    } else
        text = '--- no item specified ---';
    $('#item_list').html(text);
}

function fill(id, thisValue, onclick) 
{	
	if (thisValue.length>0 && onclick){
		var cols = thisValue.split('|');
		$('#'+id).val(cols[1] + ', ' + cols[0] + ', ' + cols[2] + ', ' + cols[3] + ', ' + cols[4]);
	}
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function suggest(me, inputString)
{
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
        if (/,/.test(inputString)){
            var mathces = /.*, *(.+)/.exec(inputString);
            if (mathces != null)
                inputString = mathces[1];
        }
        var pd = {queryString: ""+inputString+"", inputId: ""+me.id+"", deptId: ""+department+""};
        if (category>0) pd.catId =""+category+"";
        
		$.post("loan/suggest_item.php", pd, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			} else
                $('#suggestions').fadeOut();
		});
	}
}

function fill_user(id, thisValue, onclick) 
{
	if (thisValue.length>0 && onclick){
		$('#'+id).val($(this).text());
	}
	setTimeout("$('#suggestions_user').fadeOut();", 100);
}

function suggest_user(me, inputString)
{
	if(inputString.length == 0) {
		$('#suggestions_user').fadeOut();
	} else {
		$.post("loan/suggest_user.php", {queryString: ""+inputString+"", inputId: ""+me.id+""}, function(data){
			if(data.length >0) {
				$('#suggestions_user').fadeIn();
				$('#suggestionsList_user').html(data);
			}
		});
	}
}

$('#btn_draft').click(	function () {
    var frm = document.forms[0]
    var items_val = $('#items').val();    
	var radios = document.getElementsByClassName('chk');
    var checklist = new Array;	
	
	var nom = 0, noc = 0;
    for (var i = 0;i < radios.length; i++) {
		if ($(radios[i]).hasClass('mandatory')){
			nom++;
			var col = radios[i].id.split('-'); // cbo-id_check-value[yes|no|na)
			var id_check = col[1]; 
			if (radios[i].checked) {
			   noc++;
			   checklist[i]=id_check+'-'+col[2];
			}	 
		}
 	}
	if (noc != nom){
	
	}
	
	$('#loan_checklist').val(checklist.join(','));   
  
    if (items_val == ''){
        alert('Please add Serial No of Item!');
        return false;
    }
    var items = items_val.split(',');
    if (items.length != $('#quantity').val()){
        if (!confirm('Quantity required and Number of Inserted Items different. Continue?'))
            return false;
    }
	var acc_text = '';
	for (var i=0; i<all_accs.length; i++){
		var row = items[i].split('|');
		if (all_accs[i]!=null && all_accs[i].length>0)
			acc_text += row[2]+':'+all_accs[i].join('|')+'~~';
		else
			acc_text += row[2]+':~~';
	}
	$('#accs').val(acc_text);	
	
    var ok = confirm('Are you sure proceed this Loan-Out as Draft ?');
    if (!ok)
        return false;
    
    var cvs = document.getElementById('imageView');
    frm.issue_signature.value = cvs.toDataURL("image/png");
    cvs = document.getElementById('imageView2');
    frm.loan_signature.value = cvs.toDataURL("image/png"); 
	if(sl > 0){
		cvs = document.getElementById('imageView3');
		frm.parent_signature.value = cvs.toDataURL("image/png"); 
	}
	   

    frm.issue.value = 4;
    frm.submit();
});

$('#btn_submit').click(	function () {
    var frm = document.forms[0]
    var items_val = $('#items').val();    
	var radios = document.getElementsByClassName('chk');
    var checklist = new Array;
	var nom = 0, noc = 0;
    for (var i = 0;i < radios.length; i++) {
		if ($(radios[i]).hasClass('mandatory')){
			nom++;
			var col = radios[i].id.split('-'); // cbo-id_check-value[yes|no|na)
			var id_check = col[1]; 
			if (radios[i].checked) {
			   noc++;
			   checklist[i]=id_check+'-'+col[2];
			}	 
		}
 	}
	if (noc != nom){
	
	}
	
	$('#loan_checklist').val(checklist.join(','));   
  
    if (items_val == ''){
        alert('Please add Serial No of Item!');
        return false;
    }
    var items = items_val.split(',');
    if (items.length != $('#quantity').val()){
        if (!confirm('Quantity required and Number of Inserted Items different. Continue?'))
            return false;
    }
	var acc_text = '';
	for (var i=0; i<all_accs.length; i++){
		var row = items[i].split('|');
		if (all_accs[i]!=null && all_accs[i].length>0)
			acc_text += row[2]+':'+all_accs[i].join('|')+'~~';
		else
			acc_text += row[2]+':~~';
	}
	$('#accs').val(acc_text);
	
	if (isCanvasEmpty || isCanvas2Empty){
        alert('Please sign-in for issuer and requester!');
        return false;
    }
    var ok = confirm('Are you sure proceed this Loan-Out?');
    if (!ok)
        return false;
    
    var cvs = document.getElementById('imageView');
    frm.issue_signature.value = cvs.toDataURL("image/png");
    cvs = document.getElementById('imageView2');
    frm.loan_signature.value = cvs.toDataURL("image/png");    
	if(sl > 0){
		cvs = document.getElementById('imageView3');
		frm.parent_signature.value = cvs.toDataURL("image/png"); 
	} 
	
    frm.issue.value = 1;
    frm.submit();
});


function more_accessories()
{
	var url = './?mod=item&sub=accessories&act=popup';
	var opt = 'height=400,width=360,left=100,top=100,resizable=no,location=no,directory=no';
	var win = window.open(url, 'AccessoriesPopup', opt);

}

function addacc(me){
	var id_cat = me.id.substr(4);
	var id_idx = me.href.substr(me.href.lastIndexOf('#')+1);
	current_acc_index = id_idx;
	if (id_cat > 0)
		load_accessories(id_cat);
	else 
		alert("Item's category is unknown!");
}

function load_accessories(cat){
	$.post("loan/get_accessories.php", {id_category: cat}, function(data){
		if(data.length >0) {
			display_accessories_dialog(data);
		} else alert('This item does not has accessories!');
	})
}


function display_accessories_dialog(data){
	$('#dialog_accessories').dialog({
		modal: true, width: 400, height: 200,
		title: 'Add Accessories'});

	var rows = data.split('|');
	var thelist = '<ul>';
	for (var i=0; i<rows.length; i++){
		var cols = rows[i].split('~');
		var cbid = 'cbacc-'+cols[0];
		thelist += '<li><input type="checkbox" name="acc" class="cbacc" value="'+cols[0]+'" id="'+cbid+'"> <label for="'+cbid+'">'+cols[1]+'</li>';
	}
	thelist += '</ul>';
	$('#accessories_option').html(thelist);
}

$('#btn_close').click(function(){
	$('#dialog_accessories').dialog('close');
	current_acc_index = -1;
});

$('#btn_setacc').click(function(){
	var selected = Array();
	var selected_text = Array();
	$('.cbacc').each(function(id, elm){
		if ($(elm).attr('checked')){
			selected.push($(elm).attr('value'));
			selected_text.push($('label[for='+$(this).attr('id')+']').html());
		}
	});
	$('#dialog_accessories').dialog('close');
	
	if (current_acc_index>-1){
		all_accs[current_acc_index] = selected;
		all_accs_text[current_acc_index] = selected_text;
		var text= '';
		if (selected_text.length>0)
			text = selected_text.join(', ');
		$('#acclist-'+current_acc_index).html(text+' ');
		
	}
	current_acc_index = -1;
});



$('#additem').click(function(){

	$('#dialog_find_item').dialog({
		modal: true, width: 400, height: 200,
		title: 'Find item to be loaned'});
});

function display_find_item_dialog()
{
	var w = $('#find_item_dialog');
	if (find_item_invisible){
		w.show();
		$('#dialog_outer').css('z-index', 1000 );
	} else {
		w.hide();
		$('#dialog_outer').css('z-index', -10);
	}
	find_item_invisible = !find_item_invisible;
}

loaned_by_update(document.getElementById('refname'));
display_list($("#items").val());
$('#edit_item').focus();

$('#btn_loan_issuance').click(function (e){
	toggle_fold(this);
});

$('#btn_loan_checklist_body').click(function (){
	toggle_fold(this);	
});


</script>
