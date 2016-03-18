<?php
if (!defined('FIGIPASS')) exit;
$statuses = array(
    AVAILABLE_FOR_LOAN => 'Available for Loan',
    STORAGE => 'Storage',
    FAULTY=> 'Faulty',
    LOST => 'Lost'
    );

$id_loan = isset($_GET['id']) ? $_GET['id'] : 0;
$issue = get_request_out($id_loan);
$today = date('j-M-Y');

$next_day = mktime(date('H'), date('i'), date('s'), date("m"), date("d")+1, date("Y"));
$next_day_str = date('j-M-Y', $next_day);
$format_date = '%d-%b-%Y %H:%i:%s';
$format_date_only = '%d-%b-%Y';

$items = array();
$item_ids = array();
$request = get_request($id_loan);


$need_approval = ($request['without_approval'] == 0);
$request_items = get_expendable_item_by_id_loan($issue['id_loan']);
$_items = isset($_POST['items']) ? $_POST['items'] : null;
$_max = isset($_POST['max']) ? $_POST['max'] : null;
$_input = isset($_POST['input']) ? $_POST['input'] : null;
$_del_id = isset($_POST['del_id']) ? $_POST['del_id'] : 0;
$_nric = isset($issue['nric']) ? $issue['nric'] : null;
$items = json_decode($_items);
$quantities = array();

$hidden_fields = null;
$max_quantities = json_decode($_max,true);
$user = array();
if(isset($_nric)){
$user = get_user_by_nric($_nric);
$_id_user = $user['id_user'];
}


if ($_input != null){ // scan item
    $item = get_expendable_item_out_by_code($_input,$_id_user);
    if (!empty($item['id_item'])){
		
		$items[] = array($item['id_item'],1,$item['id_loan']);
		$max_quantities[$item['id_item']] = $item['quantity'];
    }
	
	
	
}
// echo print_r($max_quantities);
// echo print_r($item);
$new_item = array();
foreach($items as $key=>$row){
	if(($_del_id - 1) == $key){
		
		continue;
	}
	else{
		$new_item[] = $row;
	}
}

$items = $new_item;


$items_list = array();
foreach($items as $row){
	$items_list[] = $row[0];
}
$scanned_list = '<div id="itemspace"></div>';
if (count($items) > 0){	
	foreach($items as $row){
		$quantities[] = $row[1];
	
	}
	
	$scanned_list  = '<table class="consumable_item_list" cellpadding=3 cellspacing=3 width="800">';
			$scanned_list .= '<tr><th width=30>No</th><th width=120>Item Code</th>
							  <th >Name</th><th width=100>Quantity</th><th>Status</th><th width=20></th></tr>';
	$no = 1;
	
	foreach($items as $key => $row){
		
		$query  = "SELECT ei.*,elio.*, department_name, category_name ,sum(quantity) as quantity
                FROM expendable_loan_item_out elio 
				LEFT JOIN expendable_item ei ON ei.id_item = elio.id_item
                LEFT JOIN category cat ON cat.id_category = ei.id_category 
                LEFT JOIN department dept ON dept.id_department = cat.id_department   
                WHERE elio.id_item = $row[0] and id_loan = $id_loan group by elio.id_item";
		
		$rs = mysql_query($query);
		if ($rs && mysql_num_rows($rs)>0){
			$rec = mysql_fetch_assoc($rs);
			
			$select_op = '<select name="st-item['.$key.']"><option value="AVAILABLE">AVAILABLE</option><option value="FAULTY">FAULTY</option><option value="LOST">LOST</option><option value="STORAGE">STORAGE</option></select>';
			$dellink = '<a class="button delete" href="javascript:void(0)" onclick="del_this(' . ($key+1) . ')">x</a>';
			$scanned_list .= '<tr><td align="center">' . ($no++) . '.</td><td>' .
				 $rec['item_code'] . '</td><td>' . $rec['item_name'] . '</td><td align="center" id="row-'.$rec['id_item'].'" ><input data-row="'.$key.'" data-id="' . $rec['id_item'] . '" style="width: 60px;" type="number" value="' . 
				 $quantities[$key] . '" class="qty-' . $rec['id_item'] . '" name="qty['.$rec['id_item'].']['.$rec['quantity'].']['.$rec['id_loan'].']" max="'.$rec['quantity'].'"></td><td>'.$select_op.'</td><td>' . $dellink . '</td></tr>';
			// }                
			
		}
	}
	echo $query;
	$scanned_list .= '</table><br/>';
	
}

  
/*function update_status_items($id_loan, $items, $status, $defect = null){
    if (count($items)>0){
        $query = "UPDATE item SET status_update = now(), status_defect = '$defect', 
                  id_status = '$status', issued_to = 1, issued_date = now()   
                  WHERE id_item in (" . implode(',', $items) . ")";
        mysql_query($query);
        $item_status = 0;
        switch($status){
            case AVAILABLE_FOR_LOAN: $item_status = 'AVAILABLE'; break;
            case STORAGE: $item_status = 'STORAGE'; break;
            case FAULTY: $item_status = 'FAULTY'; break;
            case LOST: $item_status = 'LOST'; break;
        }
        foreach($items as $id){
            $query = "INSERT INTO loan_return_item(id_loan,id_item,status,process,referer)
                        VALUE ($id_loan, $id, '$item_status', 'NONE', '')";
            mysql_query($query);
            //echo mysql_error();
        }
    }
}

//print_r($_POST);*/
if (isset($_POST['returning']) && ($_POST['returning'] == 1)){
	echo print_r($_POST);
    // store loan-out
  
    $received_by = FULLNAME;
    $query = "REPLACE INTO expendable_loan_return(id_loan, returned_by, received_by) 
              VALUES ($id_loan, '$_POST[returned_by]', '$received_by')";
    mysql_query($query);
	echo mysql_error();
	
    $admin_id = USERID;
    $query = "UPDATE expendable_loan_process SET 
              received_by = $admin_id, 
              receive_date = now(), 
              receive_remark = '$_POST[receive_remark]', 
              return_by = 0, 
              return_date = now(),  
              return_remark = '$_POST[return_remark]' 
              WHERE id_loan = $id_loan";
    mysql_query($query);
	echo mysql_error();
    
    $query = "UPDATE expendable_loan_signature SET 
              receive_sign = '$_POST[receive_signature]', 
              return_sign = '$_POST[return_signature]' 
              WHERE id_loan = $id_loan";
    mysql_query($query);
    echo mysql_error();
	
	$item_list = json_decode($_POST['items']);
	foreach($item_list as $key => $row){
		$item_ids[]= array($row[0],$row[1],$_POST['st-item'][$key]);
	}
	echo print_r($item_ids);
	// update item's status 
    // $available_items = array();
	if(count($item_ids)>0){
		
		foreach($item_ids as $row){
				$sp = '';
				$sd = '';
				switch ($row[2]){
					case 'AVAILABLE' : 
						$sp 	= 'NONE';
						$sd 	= '';
						$query = "UPDATE expendable_item set item_stock = item_stock - $row[1] where id_item = $row[0]";
						mysql_query($query);
						break;
					case 'LOST':
						$sp 	= 'NONE';
						$sd 	= 'Lost on loan #'.$id_loan;
						$query = "UPDATE expendable_item set item_stock = item_stock - $row[1] where id_item = $row[0]";
						mysql_query($query);
						break;
					case 'FAULTY':
						$sp		= 'VOID';
						$sd 	= 'Faulty on loan #'.$id_loan;
						$query = "UPDATE expendable_item set item_stock = item_stock - $row[1] where id_item = $row[0]";
						mysql_query($query);
						break;
				}				
			    $query = "INSERT INTO expendable_loan_item_return(id_loan, id_item, status, quantity, process, referer,status_defect)
						VALUES($id_loan, '$row[0]', '$row[2]','$row[1]', '$sp', '','$sd')";
				
				mysql_query($query);
				
				///////////////////////////////
				
				$query = "UPDATE expendable_loan_item_out set quantity = item_stock - $row[1] where id_item = $row[0] AND id_loan = $id_loan";
				
		}
		$query = "SELECT sum(quantity) as sq from expendable_loan_item_out where id_loan = $id_loan";
		
		$rs = mysql_query($query);
		
		
		$result = mysql_fetch_assoc($rs);
		if($result['sq']>0){
			$query = "UPDATE expendable_loan_request set status = 'PARTIAL' where id_loan = $id_loan";
		}else{
			$query = "UPDATE expendable_loan_request set status = 'COMPLETED' where id_loan = $id_loan";
		}
		mysql_query($query);
	}
    // $lost_items = array();
    // $faulty_items = array();
    // $item_status = $_POST['item_status'];
    // if (count($item_ids)>0){
        // foreach ($item_ids as $id)
            // if (isset($item_status[$id])){
                // switch ($item_status[$id]){
                // case AVAILABLE_FOR_LOAN: $available_items[] = $id; break;
                // case FAULTY: $faulty_items[] = $id; break;
                // case LOST: $lost_items[] = $id;
                // }
            // }
        
        // update_status_items($id_loan, $available_items, AVAILABLE_FOR_LOAN);
        // update_status_items($id_loan, $faulty_items, FAULTY, 'Faulty on loan #'.$id_loan);
        // update_status_items($id_loan, $lost_items, LOST, 'Lost on loan #'.$id_loan);
        
    // }

    // sending notification
   // send_returned_item_notification($id_loan);
    // avoid refreshing the page
    goto_view($id_loan, RETURNED);    
}


$users = get_user_list();  
$approved_by = !empty($request['approved_by']) ? $users[$request['approved_by']] : 0;
$approve_sign = get_signature($id_loan, 'approve');
$admin_name = $users[USERID];

$process = get_request_process($id_loan);

$issue_sign = '<img src="'.get_signature($id_loan, 'issue').'" width=200 height=80>';
$loan_sign = '<img src="'.get_signature($id_loan, 'loan').'" width=200 height=80>';



$signs = get_expendable_signatures($id_loan);

?>
<script type="text/javascript">
function submit_return(){
    var frm = document.forms[0];
    if (frm.returned_by.value == ''){
        alert('Please fill in who retrun the item!');
        return false;
    }
/*
    if (frm.received_by.value == ''){
        alert('Please fill in who receive the item!');
        return false;
    }
*/
    if (isCanvasEmpty || isCanvas2Empty){
        alert('Please sign-in for issuer and requester!');
        return false;
    }
    var cvs = document.getElementById('imageView');
    frm.return_signature.value = cvs.toDataURL("image/png");
    cvs = document.getElementById('imageView2');
    frm.receive_signature.value = cvs.toDataURL("image/png");    
    frm.returning.value = 1;
    frm.submit();
    return false;
}
</script>
<h4>Loan Return Form</h4>

<form method="post">
<input type="hidden" id="items" name="items" value='<?php if(!empty($items))echo json_encode($items)?>'>
<input type="hidden" id="max" name="max" value='<?php if(!empty($max_quantities))echo json_encode($max_quantities)?>'>
<input type="hidden" id="del_id" name="del_id" value="0">
<table  class="loanview return" cellpadding=2 cellspacing=1>
<tr valign="top"><td><?php display_request($request); ?></td></tr>
<tr valign="top"><td><?php display_issuance($issue, false, true); ?> </td></tr>
<tr>
	<td style="text-align:center">
		<table width="100%">
			<tr>
				<th style="text-align: left">
				Return Items
				</th>
			</tr>
			<tr>
				<td>
				<div>
				
				<?php
					
					if (count($items) == 0)
						echo 'Scan an item: ';
					else
						echo 'Scan another item: ';
				echo <<<TEXT
					<br/>    
					<br/>    
					<input type="text" id="input" name="input" class="inputbox" autocomplete="off" onkeyup="check_entry()"/>
TEXT;
				
					
					echo $scanned_list;
				?>
				
				</th>
			<tr>
		</table>
		</div>
	</td>
</tr>
<tr>
    <td>
<?php
    $issue = array_merge($issue, $process);
    if ($issue['loaned_by'] == 0)
        $issue['loaned_by_name'] = $issue['name'];


    display_issuance_process($issue, $signs); 
    
?>

        <table width="100%" cellpadding=2 cellspacing=1  >

<tr valign="top">
    <th rowspan=6>&nbsp;</th>
    <th width=200 align="center"></th>
    <th width=200 align="center">Returned By</th>
    <th width=200 align="center">Received By</th>

</tr>
<tr valign="top">
    <td>Name</td>
    <td><input type="text" name="returned_by" size=22 value="<?php echo $issue['name']?>"></td>
    <td><?php echo FULLNAME?></td>
</tr>
<tr valign="top">
    <td>Date/Time Signature</td>
    <td><?php echo $today?></td>
    <td><?php echo $today?></td>
</tr>
<tr valign="top">
    <td>Remarks</td>
    <td><textarea name="return_remark" cols=22 rows=3></textarea></td>
    <td><textarea name="receive_remark" cols=22 rows=3></textarea></td>
</tr>
<tr valign="top">
    <td>Signatures</td>
    <td>
		<div id="signature-pad" class="m-signature-pad" style='width: 200px;height: 80px;'>
			<div class="m-signature-pad--body">
			 <canvas id="imageView" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
			
		</div>
	</td>
    <td>
		<div id="signature-pad2" class="m-signature-pad" style='width: 200px;height: 80px;'>
			<div class="m-signature-pad--body">
			 <canvas id="imageView2" height=80 width=200></canvas>
			 <div style="text-align: right;position: relative;top: -80px;">
                    <a data-action="clear" class="button clearsign" title="Clear signature space">X</a>
             </div>
			</div>
			
		</div>
    </td>
</tr>

        </table>
    </td>
</tr>
<tr>
    <td align="right" valign="middle" colspan=2>
    <input type="image" onclick="return submit_return()" src="images/submit.png" />    
    </td>
</tr>
</table>
<input type="hidden" name="receive_signature">
<input type="hidden" name="return_signature">
<input type="hidden" name="returning">

</form>
<br/><br/>

<script type="text/javascript" src="js/signature.js"></script>
<script type="text/javascript" src="js/signature2.js"></script>
<script>
	var isbn_length = <?php echo ISBN_LENGTH?>;
	var nric_length = <?php echo NRIC_LENGTH?>;
	var serial_length = <?php echo SERIAL_LENGTH?>;
	var need_signature = '<?php echo $need_signature ?>';
	var list = $('#items').val();
	var m = $('#max').val();
	var items = $.parseJSON(list);
	var max = $.parseJSON(m);
	$('input[class|="qty"]').blur(function(){
		update_quantity($(this).data('id'),$(this).val(),$(this).attr('max'),$(this).data('row'));
		// alert($(this).val());
	});
	
	function update_quantity(id,newqty,max,row)
	{
		newqty = parseInt(newqty);
		max = parseInt(max);
	   
		var new_items = [];
		
		for(i = 0; i< items.length;i++){
			if(i == row){
				new_items[i] = [String(id),newqty];
			}
			else{
				new_items[i] = [items[i][0],items[i][1]]
			}
		}
		
		var jsons = JSON.stringify(new_items);
		  $('#items').val(jsons);
		
		if (isNaN(newqty)){
			alert('Please enter correct number of quantity');
		}
		else if(parseInt(newqty)>parseInt(max)){
			alert('You cannot fill more than '+max);
		}
		else{
			  $('form').submit();
		}
	}
	
function del_this(row)
{
    $('#del_id').val(row);
    $('form').submit();
}

function check_entry()
{
    var v = $('#input').val();
    
        if (v.length >= serial_length)
            $('form').submit();    
    
}

$('.inputbox').focus();
</script>

