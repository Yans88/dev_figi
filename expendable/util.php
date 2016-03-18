<?php

function get_vendor_list($swap = false, $lowercase = false)
{
    $data = array();
    $query  = "SELECT id_vendor, vendor_name FROM vendor ORDER BY vendor_name";
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_row($rs))
        if ($swap){
            if ($lowercase)
                $rec[1] = strtolower($rec[1]);
            $data[$rec[1]] = $rec[0];
        } else
            $data[$rec[0]] = $rec[1];
    return $data;
}

function build_vendor_combo($selected = -1) {
    
    return build_combo('id_vendor', get_vendor_list(), $selected);
}


function build_category_combo($type = null, $selected = -1, $department = 0, $onchange = null) {
    
    return build_combo('id_category', get_category_list($type, $department), $selected, $onchange);
}
function build_manufacturer_combo($selected = -1) {
    
    return build_combo('id_manufacturer', get_manufacturer_list(), $selected);
}

function get_manufacturer_list($swap = false, $lowercase = false)
{
    $data = array();
    $query  = "SELECT id_manufacturer, manufacturer_name FROM manufacturer ORDER BY manufacturer_name ";
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_row($rs))
        if ($swap){
            if ($lowercase)
                $rec[1] = strtolower($rec[1]);
            $data[$rec[1]] =$rec[0];
        } else
            $data[$rec[0]] =$rec[1];
    return $data;
}

function build_brand_combo($manufacturer = 0, $selected = -1) {
    
    return build_combo('id_brand', get_brand_list(), $selected);
}

function get_brand_list($swap = false, $lowercase = false)
{
    $data = array();
    $query  = "SELECT id_brand, brand_name FROM brand ORDER BY brand_name ASC";
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_row($rs))
        if ($swap){
            if ($lowercase)
                $rec[1] = strtolower($rec[1]);
            $data[$rec[1]] = $rec[0];
        }else
            $data[$rec[0]] = $rec[1];
    return $data;
}

function count_category($type = 'equipment', $dept = 0)
{
    $result = 0;
    $query  = " SELECT count(*) FROM category c ";
    if ($dept >0)
        $query .= " LEFT JOIN department_category dc ON dc.id_category = c.id_category ";
    $query .= " WHERE category_type = '$type' ";
    if ($dept > 0) $query .= ' AND dc.id_department = ' . $dept;
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs)){
        $rec = mysql_fetch_row($rs);
        $result = $rec[0];
    }
    return $result;
}

function get_categories($type = 'equipment', $sort = 'asc', $start = 0, $limit = 10, $dept = 0)
{
    $query  = " SELECT * FROM category  c ";
    if ($dept >0)
        $query .= " LEFT JOIN department_category dc ON dc.id_category = c.id_category ";
    $query .= " WHERE category_type = '$type' ";
    if ($dept > 0) $query .= ' AND dc.id_department = ' . $dept;
    $query .= " ORDER BY category_name $sort LIMIT $start,$limit ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    return $rs; 
}
function count_expendable_item($searchby = null, $searchtext = null, $dept = 0)
{
    $wheres = array();
	$result = 0;
	$query  = "SELECT count(ei.id_item)  
                 FROM expendable_item ei 
                LEFT JOIN category cat ON cat.id_category = ei.id_category 
                LEFT JOIN department dept ON dept.id_department = cat.id_department   ";               
    if (!empty($searchtext))
        $wheres[] = " $searchby like '%$searchtext%' ";
    if ($dept > 0)
        $wheres[] = " cat.id_department = $dept ";
    if (count($wheres) > 0)
        $query .= ' WHERE ' . implode(' AND ', $wheres);
	$rs = mysql_query($query);
    //echo mysql_error().$query;
	if ($rs && mysql_num_rows($rs)){
		$rec = mysql_fetch_row($rs);
		$result = $rec[0];
	}
	return $result;
}
function get_available_item($id){
	$query = "SELECT ei.item_stock, (SELECT SUM(elio.quantity) FROM expendable_loan_item_out elio where elio.id_item = ".$id.") as item_out, (SELECT SUM(elir.quantity) FROM expendable_loan_item_return elir where elir.id_item = ".$id.") as item_in FROM expendable_item ei WHERE ei.id_item =".$id;
	 $rs = mysql_query($query);
	 $rec = mysql_fetch_row($rs);
	 
	 return $rec[0] - $rec[1] + $rec[2];
}
function get_expendable_items($orderby = 'item_code', $sort = 'asc', $start = 0, $limit = 10, $searchby = null, $searchtext = null, $dept = 0)
{
    $wheres = array();
	$query  = "SELECT ei.*, department_name, category_name 
                FROM expendable_item ei 
                LEFT JOIN category cat ON cat.id_category = ei.id_category 
                LEFT JOIN department dept ON dept.id_department = cat.id_department   ";           
    if (!empty($searchtext) && !empty($searchby))
        $wheres[] = " $searchby like '%$searchtext%' ";
    if ($dept > 0)
        $wheres[] = " cat.id_department = $dept ";
    if (count($wheres) > 0)
        $query .= ' WHERE ' . implode(' AND ', $wheres);
	$query .= " ORDER BY $orderby $sort  LIMIT $start,$limit ";
	$rs = mysql_query($query);
    //echo mysql_error().$query;
    
	return $rs;
}

function get_expendable($id = 0)
{
    $result = array();
	$query  = "SELECT ei.*, department_name, category_name 
                FROM expendable_item ei 
                LEFT JOIN category cat ON cat.id_category = ei.id_category 
                LEFT JOIN department dept ON dept.id_department = cat.id_department   
                WHERE ei.id_item = $id ";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);
    return $result;
}
function get_expendable_item_out($id){
	$result = array();
	$query = "SELECT count(*) as s from expendable_loan_item_out where id_item=".$id;
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs))
		$result = mysql_fetch_assoc($rs);
	
	return $result;
}
function get_expendable_item_out_by_id_loan($id){
	$result = array();
	$query = "SELECT *,(SELECT COUNT(*) FROM expendable_loan_item_return elir WHERE elir.id_loan = elio.id_loan) as returned from expendable_loan_item_out elio LEFT JOIN expendable_item ei on ei.id_item = elio.id_item where id_loan=".$id;
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs))
		
		while($rec = mysql_fetch_assoc($rs)){
			$result[] = $rec;
		}
	
	// echo mysql_error();
	return $result;
}
function get_expendable_stock($id = 0)
{
    $result = 0;
	$rec = get_expendable($id);
	$out_number = get_expendable_item_out($id);
    if (isset($rec['item_stock']))
        $result = $rec['item_stock'] - $out_number['s'];
    return $result;
}

function get_request_process($id = 0){
    $result = array();
    $format_date = '%d-%b-%Y %H:%i';
    $query = "SELECT elp.*, 
                date_format(loan_date, '$format_date') as loan_date, 
                date_format(return_date, '$format_date') as return_date,
                date_format(issue_date, '$format_date') as issue_date,
                date_format(receive_date, '$format_date') as receive_date,
                
                (SELECT full_name FROM user WHERE id_user = issued_by) as issued_by_name, 
                (SELECT full_name FROM user WHERE id_user = received_by) as received_by_name 
                FROM expendable_loan_process elp 
                WHERE id_loan = $id";
    $rs = mysql_query($query);
    if ($rs && mysql_num_rows($rs)>0){
        $result = mysql_fetch_assoc($rs);
    }
    return $result;
}
function get_loan_request($id_user){
	$result = array();
	$format_date = '%d-%b-%Y %H:%i';
	$query = "SELECT elr.*,
				date_format(elr.request_date, '$format_date') as loan_date, 
				date_format(elr.start_loan, '$format_date') as start_date, 
				date_format(elr.end_loan, '$format_date') as end_date, 
				(SELECT SUM(quantity) FROM expendable_loan_item_out elio where elio.id_loan = elr.id_loan) as quantity
				FROM expendable_loan_request elr
				LEFT JOIN expendable_loan_item_out elio ON elio.id_loan = elr.id_loan
				WHERE requester = $id_user AND quantity > 0" ;
	$rs = mysql_query($query);
	if($rs && mysql_num_rows($rs)>0){
		while($rec = mysql_fetch_assoc($rs)){
			$result[] = $rec;
		}
		
		return $result;
	}
}
function display_request($request, $forprint = false){
    global $transaction_prefix,$_act,$count_returned_partial,$status_loan_request;
	$theader = "<td>";
	$theader .= "<table>";
	$item_req_list = get_expendable_item_by_id_loan($request['id_loan']);
	
		// foreach($item_req_list as $row){
			
		// }	
	$theader .= "</table>";
	$theader .="</td>";
?>
    <table width="100%" cellpadding=2 cellspacing=1 class="request" >
      <tr valign="top" align="left">
        <th align="left" colspan=4>Loan Request
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_request" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
        </th>
      </tr>  
      <tbody id="loan_request">
      <tr valign="top" align="left" class="alt">
        <td align="left" width="14%" >Request No.</td>
        <td align="left" width="30%">
            <?php 
            echo $transaction_prefix.$request['id_loan'];
            if ($request['long_term'] == 1)
                echo ' &nbsp; <span class="long_term_tag">(Long Term Loan)</span>';
        ?>
        </td>
        <td align="left" width="17%">Request Date/Time</td>
        <td align="left" ><?php echo $request['request_date']?></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Requested By</td>
        <td align="left"><?php echo $request['requester']?></td>
        <td align="left">Loan Period</td>
        <td align="left"><?php echo $request['start_loan']?> - <?php echo $request['end_loan']?></td>
      </tr>
      <tr valign="top" class="alt">  
        <td align="left">Purpose</td>
        <td align="left" colspan=1><?php echo $request['purpose']?></td>
        <td align="left">Items</td>
		<td>
			<table cellpadding=2 cellspacing=1 class="request">
				<tr>
					<th>
						Item Name
					</th>
					<th>
						Quantity
					</th>
					<th>
						Returned
					</th>
				</tr>
				<?php foreach($item_req_list as $row){ ?>
				<tr>
					<td>
						<a href="./?mod=expendable&act=view&id=<?php echo $row['id_item'] ?>"><?php echo $row['item_name'] ?></a>
					</td>
					<td>
						<?php echo $row['quantity'] ?>
					</td>
					<td>
						<?php echo $row['returned'] ?>
					</td>
				</tr>
				<?php } ?>
			</table>
		</td>
      </tr>  
      
      <tr valign="top" >  
        <td align="left">Remark</td>
        <td align="left" colspan=3><?php echo $request['remark']?></td>
      </tr>
      </tbody>
    </table>
    <script>
    $('#btn_loan_request').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
}

function display_issuance($issue, $forprint = false, $forreturn=false){
   
   global $item_list;
   
	$item_req_list = get_expendable_item_out_by_id_loan($issue['id_loan']);
	
?>
    <table width="100%" cellpadding=2 cellspacing=1 class="issue" >
      <tr valign="top" align="left">
        <th align="left" colspan=4>Loan-Out Details
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_issuance" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
        </th>
      </tr>  
      <tbody id="loan_issuance">
      <tr valign="top">  
        <td align="left" width="13%">Loan Out to</td>
        <td align="left" width="30%"><?php echo $issue['name']?></td>
        <td align="left" colspan=2><strong>Projected Date to return:</strong></td>
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">NRIC &nbsp; </td>
        <td align="left"><?php echo $issue['nric']?></td>
        <td align="right" width="16%">Sign Out &nbsp; </td>
        <td align="left"><?php echo $issue['loan_date']?></td>    
      </tr>  
      <tr valign="top">  
        <td align="left">Contact No.</td>
        <td align="left"><?php echo $issue['contact_no']?></td>    
        <td align="right">To be Returned &nbsp; </td>
        <td align="left"><?php echo $issue['return_date']?></td>    
      </tr>  
      <tr valign="top" class="alt">  
        <td align="left">Department &nbsp; </td>
        <td align="left"><?php echo $issue['department_name']?></td>    
        <td align="right"></td>
        <td align="left" ></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Location &nbsp; </td>
        <td align="left" colspan=2><?php echo $issue['location_name']?></td>    
      </tr>
      <tr valign="top" class="alt" align="left">
        <td align="left">Item List</td>
        <td align="left" colspan=4>
            <div id="returnitemlist"><table cellpadding=2 cellspacing=1 class="request">
				<tr>
					<th>
						Item Name
					</th>
					<th>
						Quantity
					</th>
					<th>
						Returned
					</th>
				</tr>
				<?php foreach($item_req_list as $row){ ?>
				<tr>
					<td>
						<a href="./?mod=expendable&act=view&id=<?php echo $row['id_item'] ?>"><?php echo $row['item_name'] ?></a>
					</td>
					<td>
						<?php echo $row['quantity'] ?>
					</td>
					<td>
						<?php echo $row['returned'] ?>
					</td>
				</tr>
				<?php } ?>
			</table></div>
        </td>
      </tr>  
      </tbody>
    </table>
    <script>
    $('#btn_loan_issuance').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php

}


function display_return_process($process, $signs, $forprint = false){
//print_r($process);
?>
<table width="100%" cellpadding=2 cellspacing=1 class="process">
<tr valign="top">
    <th rowspan=5>&nbsp;</th>
    <th width=200></th>
    <th width=200>Returned By</th>
    <th width=200>Received By
<?php
    if (!$forprint)
        echo '<div class="foldtoggle"><a id="btn_loan_return_process" rel="open" href="javascript:void(0)">&uarr;</a></div>';
?>
    </th>
</tr>
<tbody id="loan_return_process">
<tr valign="top">
    <td></td>
   <td>Name</td>
    <td><?php echo $process['returned_by_name']?></td>
    <td><?php echo $process['received_by_name']?></td>
</tr>
<tr valign="top" class="alt">
    <td></td>
    <td>Date/Time Signature</td>
    <td><?php echo $process['return_date']?></td>
    <td><?php echo $process['receive_date']?></td>
</tr>
<tr valign="top">
    <td></td>
    <td>Remarks</td>
    <td><?php echo $process['return_remark']?></td>
    <td><?php echo $process['receive_remark']?></td>
</tr>
<tr valign="top" class="alt">
    <td></td>
    <td>Signatures</td>
    <td><img src="<?php echo $signs['return_sign']?>" class="signature"></td>
    <td><img src="<?php echo $signs['receive_sign']?>" class="signature"></td>
</tr>
</tbody>
</table>
    <script>
    $('#btn_loan_return_process').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
}

function display_issuance_process($process, $signs, $forprint = false){
    //print_r($process);
    
?>
<table width="100%" cellpadding=2 cellspacing=1 class="process">
<tr valign="top">
    <th rowspan=5></th>
    <th width=200></th>
    <th width=200>Issued By</th>
    <th width=200>Loaned By
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_issuance_process" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
    </th>
</tr>
<tbody id="loan_issuance_process">
<tr valign="top">
    <td></td>
    <td>Name</td>
    <td><?php echo $process['issued_by_name']?></td>
    <td><?php echo $process['loaned_by_name']?></td>
</tr>
<tr valign="top" class="alt">
    <td></td>
    <td>Date/Time Signature</td>
    <td><?php echo $process['issue_date']?></td>
    <td><?php echo $process['loan_date']?></td>
</tr>
<tr valign="top">
    <td></td>
    <td>Remarks</td>
    <td><?php echo $process['issue_remark']?></td>
    <td><?php echo $process['loan_remark']?></td>
</tr>
<tr valign="top" class="alt">
    <td></td>
    <td>Signatures</td>
    <td><img src="<?php echo $signs['issue_sign']?>" class="signature"></td>
    <td><img src="<?php echo $signs['loan_sign']?>" class="signature"></td>
</tr>
</tbody>
</table>
    <script>
    $('#btn_loan_issuance_process').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
}


function get_request_out($id = 0){
    $result = array();
    $format_date = "%d-%b-%Y %H:%i";
    $query = "SELECT elo.*, department_name, location_name, 
              date_format(loan_date, '$format_date') as loan_date, 
              date_format(return_date, '$format_date') as return_date 
              FROM expendable_loan_out elo 
              LEFT JOIN department d ON d.id_department = elo.id_department 
              LEFT JOIN location l ON l.id_location = elo.id_location 
              WHERE elo.id_loan = '$id' ";
    $rs = mysql_query($query); 
    //echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_request_by_status($status = '', $start = 0, $limit = RECORD_PER_PAGE, $ordby = 'request_date', $orddir = 'ASC'){
    $result = array();
	$rm = query_request_by_status($status, $start, $limit, $ordby, $orddir);
	$rs = mysql_query($rm);
	$i = 0;
	if ($rs && (mysql_num_rows($rs)>0))
		while ($rec = mysql_fetch_assoc($rs))
			$result[$i++] = $rec;    
	return $result;
}

function get_request($id = 0){
    $result = array();
    $dtf = "'%d-%b-%Y %H:%i'";
    $dtf1 = "'%d-%b-%Y'";    
    $query = "SELECT elr.id_loan, date_format(start_loan, $dtf) as start_loan, date_format(end_loan, $dtf) as end_loan, purpose, 
                 date_format(request_date, $dtf) as request_date, nric, contact_no, without_approval, long_term,   
                 user_email, user.full_name as requester, remark, status,  user.id_user
                 FROM expendable_loan_request elr 
                 LEFT JOIN user ON requester = user.id_user 
                 WHERE elr.id_loan = '$id' ";
    $rs = mysql_query($query); 
    // echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_request_return($id = 0){
    $result = array();
    $format_date = "%d-%b-%Y %H:%i";
    $query = "SELECT full_name received_by, (SELECT returned_by FROM expendable_loan_return WHERE id_loan = '$id') returned_by_name 
				FROM expendable_loan_process elp 
				LEFT JOIN user u ON elp.received_by=u.id_user 
				WHERE id_loan = '$id' ";
    $rs = mysql_query($query); 
    //echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_lost_report($id){
    $result = null;
    $query = 'SELECT elir.*,ei.*, elr.* FROM expendable_loan_item_return elir 
				LEFT JOIN expendable_item ei ON ei.id_item = elir.id_item 
				LEFT JOIN expendable_loan_request elr ON elr.id_loan = elir.id_loan
				WHERE elir.id_loan = '.$id.' AND elir.status = "LOANED"';
    $rs = mysql_query($query);
	echo mysql_error();
    if ($rec = mysql_fetch_assoc($rs))
      $result = $rec;
    return $result;
}

function temp_attachment($files){
	
	
	$id_list = array();
	for ($i = 0; $i < count($files['attachment']['name']); $i++){
		$filesize = $files['attachment']['size'][$i];
		$filename = $files['attachment']['name'][$i];
		$filetemp = $files['attachment']['tmp_name'][$i];
		$errorcode = $files['attachment']['error'][$i];

		//$different = $id.'-'.$filesize.'-'.$filename;

		if (($filesize > 0) && ($errorcode == 0) && is_uploaded_file($filetemp)){

		$data_attach = base64_encode(file_get_contents($filetemp));
		//$filethumb = resize($filetemp, THUMB_WIDTH, THUMB_HEIGHT, tempnam('/tmp', 'thumb'));
		//$thumbnail = base64_encode(file_get_contents($filethumb)); 
		// $id = save_temp_att($filename,$data_attach);
		$id_list[$i] = $id;
		}
	
	}
	return $id_list;
}
function get_item_list_by_id($id){
	$query = "SELECT GROUP_CONCAT(DISTINCT ei.item_name) as item_list_name, GROUP_CONCAT(DISTINCT c.category_name) as category_list_name FROM expendable_loan_item eli JOIN expendable_item ei ON eli.id_item = ei.id_item JOIN category c ON ei.id_category = c.id_category where id_loan = $id";
	$rs = mysql_query($query);
	$result = mysql_fetch_assoc($rs);
	return $result;
}
function query_request_by_status($status = '', $start = 0, $limit = RECORD_PER_PAGE, $ordby = 'request_date', $orddir = 'ASC'){
    $dept = defined('USERDEPT') ? USERDEPT : 0;
    $query  = "SELECT elr.id_loan, date_format(start_loan, '%d-%b-%Y') as start_loan, date_format(end_loan, '%d-%b-%Y') as end_loan, 
             date_format(request_date, '%d-%b-%Y') as request_date, without_approval, purpose, 
             user.full_name as requester, remark, status, long_term, 
             approved_by, approval_date, approval_remark, issued_by, issue_date, issue_remark, returned_by, 
             return_remark, received_by, receive_date, receive_remark, acknowledged_by, acknowledge_date, acknowledge_remark,
             date_format(return_date, '%d-%b-%Y') as return_date,(SELECT COUNT(*) FROM expendable_loan_item_out elio where elio.id_loan = elr.id_loan) as quantity
                          
             FROM expendable_loan_request elr 
             LEFT JOIN user ON requester = user.id_user 
             LEFT JOIN loan_process lp ON lp.id_loan = elr.id_loan";
    if (!SUPERADMIN)
        $query .= " where elr.id_department = $dept ";

	/** +here **/
	if ($status != '' && is_array($status)){
		foreach($status as $key => $val_status){
			if($key==0)
				$query .= " AND status = '$val_status' ";
			else
				$query .= " OR status = '$val_status' ";
		}
		foreach($status as $key => $val_satus){
			$query .= " OR status = '$val_satus' "; //+
		}
	}
	elseif ($status != '' && !is_array($status))
		$query .= " AND status = '$status' ";
	if(isset($ordby)||isset($orddir)){
		$query .= " ORDER BY $ordby $orddir ";
	}
	if(isset($start)||isset($limit)){
		$query .= "LIMIT $start, $limit";
	}
	$rs = mysql_query($query);
	// echo $query.'<br>';
	return $query;
}
function count_request_by_status($status = ''){
    $result = 0;
    
	$rs = query_request_by_status($status);
	$r = mysql_query($rs);
	$result = mysql_num_rows($r);
    return $result;
}

function get_expendable_request($id){
	
    $result = array();
    $dtf = "'%d-%b-%Y %H:%i'";
    $dtf1 = "'%d-%b-%Y'";    
    $query = "SELECT elr.*, elr.id_loan id_loans, 
				 date_format(start_loan, $dtf) as start_loan, date_format(end_loan, $dtf) as end_loan, purpose, 
                 date_format(request_date, $dtf) as request_date, nric, contact_no, without_approval,  long_term,   
                 user_email, user.full_name as requester,  remark, status,  user.id_user
                 FROM expendable_loan_request elr 
                 LEFT JOIN user ON requester = user.id_user 
                 WHERE elr.id_loan = '$id' ";
    $rs = mysql_query($query); 
    // echo mysql_error().$query;
    if ($rs && (mysql_num_rows($rs)>0))
        $result = mysql_fetch_assoc($rs);
    return $result;

}
function get_expendable_loan_item($_id){
	
     $result = array();
    $query = "SELECT * FROM expendable_loan_item eli JOIN (select * from expendable_item ei group by ei.id_item) ei ON ei.id_item = eli.id_item WHERE eli.id_loan = $_id";
    $rs = mysql_query($query); 
    // echo mysql_error().$query;
    // if ($rs && (mysql_num_rows($rs)>0))
	while ($rec = mysql_fetch_assoc($rs)){
        $result[] = $rec;
	}
    return $result;
}
function get_expendable_item_by_id_loan($_id){
	$result = array();
    $query = "SELECT *,(SELECT COUNT(*) FROM expendable_loan_item_return elir WHERE elir.id_loan = eli.id_loan) as returned FROM expendable_loan_item eli LEFT JOIN expendable_item ei ON ei.id_item = eli.id_item WHERE eli.id_loan = $_id";
    $rs = mysql_query($query); 
    // echo mysql_error().$query;
    // if ($rs && (mysql_num_rows($rs)>0))
	while ($rec = mysql_fetch_assoc($rs)){
        $result[] = $rec;
	}
    return $result;
}
function display_expendable_request($request, $forprint = false){
    global $transaction_prefix,$_act,$count_returned_partial,$status_loan_request;
	$rest = $request['quantity'] - $count_returned_partial;
	if($_act=='return'){
		$theader='<th align="left"><label style="display:inline-block;width:30%;">Loaned</label><label style="display:inline-block;width:30%;">Returned</label><label for="return_now" style="display:inline-block;">Return Now</label></th>';
		$trow ="<td align='left'><input type='hidden' name='qty_loaned' value='$request[quantity]' /><label style='display:inline-block;width:30%;'>$request[quantity]</label>
				<label style='display:inline-block;width:30%;'>$count_returned_partial</label>
				<label style='display:inline-block;'><input style='width:70px;' type='number' min='0' id='return_now' name='return_now'></label></td>";
	}
	elseif($status_loan_request==PARTIAL_IN && ($_act=='view_issue' || $_act=='view_return' || $_act=='print_issue')){
		$theader='<th align="left"><label style="display:inline-block;width:30%;">Loaned</label><label style="display:inline-block;width:30%;">Returned</label><label for="return_now" style="display:inline-block;">Rest Item</label></th>';
		$trow ="<td align='left'><input type='hidden' name='qty_loaned' value='$request[quantity]' /><label style='display:inline-block;width:30%;'>( $request[quantity] )</label>
				<label style='display:inline-block;width:30%;'>$count_returned_partial</label>
				<label style='display:inline-block;'>$rest</label></td>";
	}
	else{
		$theader="<td align='left'>$request[quantity]</td>";
		$trow ='<td></td>';
	}
?>
    <table width="100%" cellpadding=2 cellspacing=1 class="request" >
      <tr valign="top" align="left">
        <th align="left" colspan=4>Loan Request
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_request" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
        </th>
      </tr>  
      <tbody id="loan_request">
      <tr valign="top" align="left" class="alt">
        <td align="left" width="14%" >Request No.</td>
        <td align="left" width="30%">
            <?php 
            echo $transaction_prefix.$request['id_loan'];
            if ($request['long_term'] == 1)
                echo ' &nbsp; <span class="long_term_tag">(Long Term Loan)</span>';
        ?>
        </td>
        <td align="left" width="17%">Request Date/Time</td>
        <td align="left" ><?php echo $request['request_date']?></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Requested By</td>
        <td align="left"><?php echo $request['requester']?></td>
        <td align="left">Loan Period</td>
        <td align="left"><?php echo $request['start_loan']?> - <?php echo $request['end_loan']?></td>
      </tr>
      
      <tr valign="top" class="alt">  
        <td align="left">Purpose</td>
        <td align="left" colspan=2><?php echo $request['purpose']?></td>
		<?php echo $trow; ?>
		<!--<th align="left" colspan=2><label style="display:inline-block;width:100px;">Loaned</label><label style="display:inline-block;width:80px;">Rest</label><label style="display:inline-block;width:110px;">Returned Now</label></th>-->
      </tr>
      <tr valign="top" >  
        <td align="left">Remark</td>
        <td align="left" colspan=3><?php echo $request['remark']?></td>
      </tr>
      </tbody>
    </table>
    <script>
    $('#btn_loan_request').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
}
function get_purchase_item($id = 0)
{
    $result = array();
	$query  = "SELECT cii.*, dci.*, department_name, category_name, vendor_name  
                FROM consumable_item_in cii 
                LEFT JOIN consumable_item dci ON cii.id_item = dci.id_item 
                LEFT JOIN category cat ON cat.id_category = dci.id_category 
                LEFT JOIN department dept ON dept.id_department = cat.id_department   
                LEFT JOIN vendor v ON v.id_vendor = cii.id_vendor 
                WHERE cii.id_trx = $id ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);
    return $result;
}

function get_expendable_item_by_code($item_code)
{
    $result = array();
	$query  = "SELECT ei.*, department_name, category_name 
                FROM expendable_item ei 
                LEFT JOIN category cat ON cat.id_category = ei.id_category 
                LEFT JOIN department dept ON dept.id_department = cat.id_department   
                WHERE ei.item_code = '$item_code' ";
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);
    return $result;
}
function get_expendable_item_out_by_code($item_code,$requester)
{
    $result = array();
	$query  = "SELECT ei.*, elio.*,elr.*, sum(quantity) as quantity
                FROM expendable_loan_item_out elio
				LEFT JOIN expendable_item ei ON ei.id_item = elio.id_item
				LEFT JOIN expendable_loan_request elr ON elr.id_loan = elio.id_loan
                WHERE ei.item_code = '$item_code' AND elr.requester = '$requester' AND elr.status = 'LOANED' ";
    $rs = mysql_query($query);
    // echo mysql_error().$query;
    if ($rs && mysql_num_rows($rs))
        $result = mysql_fetch_assoc($rs);
    return $result;
}
function get_consumer_signature($id)
{
    $result = null;
	$query  = "SELECT signature FROM consumable_user_signature WHERE id_trx = '$id' ";
    $rs = mysql_query($query);    
    if ($rs && mysql_num_rows($rs)){
        $rec = mysql_fetch_assoc($rs);
        $result = $rec['signature'];
    }
    return $result;
}



function save_expendable_item($id, $data)
{
    // get old data for existing item
    // $olddata = get_consumable($id);
    $query = "REPLACE INTO expendable_item(id_item, item_code, item_name, item_stock, id_category,id_brand,id_manufacturer)
              VALUES($id, '$data[item_code]', '$data[item_name]', '$data[item_stock]', '$data[id_category]','$data[id_brand]','$data[id_manufacturer]')";
    mysql_query($query);
    //echo mysql_error().$query;
    $affected  = mysql_affected_rows();
    if (($affected > 0) && ($id == 0))
        $id = mysql_insert_id();

    return $id;
}

function purchase_expendable_item($id, $data)
{
    // record item_in
    $query = "INSERT INTO expendable_purchase(id_item, trx_time, quantity, price, do_no, id_vendor)
              VALUES($id, now(), '$data[quantity]', '$data[price]', '$data[do_no]', '$data[id_vendor]')";
    mysql_query($query);
    //echo mysql_error().$query;
    if (mysql_affected_rows() > 0){
        // update stock
        $query = "UPDATE expendable_item SET item_stock = item_stock + $data[quantity] 
                    WHERE id_item = $id ";
        mysql_query($query);
    }
    return $id;
}

function goto_view($id, $status){
    switch($status){
    case LOANED : $view_act = 'view_issue'; break;
    case RETURNED : $view_act = 'view_return'; break;
    case LOST: $view_act = 'view_lost'; break;
    case COMPLETED : $view_act = 'view_complete'; break;
    default: $view_act = 'view';
    }
    ob_clean();
    header('Location: ./?mod=expendable&sub=loan&act='.$view_act.'&id=' . $id);
    ob_end_flush();
    exit;
}


function get_expendable_signature($lid = 0, $status = 'approve'){

	$query = "SELECT ".$status."_sign FROM expendable_loan_signature WHERE id_loan = $lid ";
	$rs = mysql_query($query);
	if ($rs && mysql_num_rows($rs)>0){
		$rec = mysql_fetch_row($rs);
		return $rec[0];
	}
	return false;
}
function get_expendable_signatures($lid = 0){
  $result = array();
  $query = "SELECT * FROM expendable_loan_signature WHERE id_loan = $lid ";
  $rs = mysql_query($query);
  if ($rs && mysql_num_rows($rs)>0)
      $result = mysql_fetch_assoc($rs);
  return $result;
}

// id_item,item_code,item_name,stock,category,department
function export_csv_expendable_item($path, $dept = 0)
{
    $dtf = '%d-%b-%Y %H:%i:%s';    
    $query  = "SELECT id_item, item_code, item_name, item_stock, category_name, department_name     
               FROM expendable_item ei 
               LEFT JOIN category cat ON cat.id_category = ei.id_category 
               LEFT JOIN department d ON d.id_department = cat.id_department 
               WHERE ei.id_item > 0 ";
    if ($dept > 0)
        $query .= " AND cat.id_department = $dept ";

    $fp = fopen($path, 'w');
    $header  = 'ItemID,ItemCode,ItemName,Stock,Category,Department';
    fputs($fp, $header."\r\n");
    $i = 0;  
    $rs = mysql_query($query);
    //echo mysql_error().$query;
    if (mysql_num_rows($rs)) {   
        while ($rec = mysql_fetch_row($rs)){
            fputs($fp, implode(',', $rec) . "\r\n");
        }
    }
    fclose($fp);
}

function get_category_list($type = null, $department = 0, $swap = false, $lowercase = false) {
    $data = array();
    $wheres = array();
    $tables = array();
    $query = 'SELECT c.id_category,c.category_name FROM category c ';
    if ($type != null) 
        $wheres[] = " c.category_type = '$type' ";
    if ($department > 0) {
        $wheres[] = " dc.id_department = $department ";
        $tables[] = ' department_category dc ON dc.id_category = c.id_category ';
    }
    if (count($wheres) > 0){
        if (count($tables) > 0) $query .= ' LEFT JOIN '. $tables[0];
        $query .= ' WHERE ' . implode(' AND ', $wheres);
        
    }
    $query .= ' ORDER BY category_name ASC ';
    
    $rs = mysql_query($query);
    while ($rec = mysql_fetch_row($rs))
        if ($swap){
            if ($lowercase)
                $rec[1] = strtolower($rec[1]);
            $data[$rec[1]] =$rec[0];
        } else
            $data[$rec[0]] =$rec[1];
    return $data;
}


function import_csv_expendable_item($path, $dept = 0)
{
    $row = 0;
    $result = 0; // upload failed
    if (!empty($path) && file_exists($path)) {
        if (($fp = fopen($path, 'r')) !== FALSE){
            $cols = fgetcsv($fp, 512, ',');
// id_item,item_code,item_name,stock,category,department
            if (count($cols) >= 6){ // 
                $departments = get_department_list();                
                $categories = get_category_list('EXPENDABLE',$dept,true,true);
                $my_dept = strtolower($departments[$dept]);
                $item_code_map = array();
                while ($cols = fgetcsv($fp, 512, ',')){
                    $row++;
                    $deptname = strtolower($cols[5]); // department
                    if ($my_dept != $deptname) continue;
                    $catname = strtolower($cols[4]);
                    $cid = isset($categories[$catname]) ? $categories[$catname] : 0;
                    if (!empty($cols[1]) && ($cid > 0)){
                        $query  = 'INSERT INTO expendable_item (item_code, item_name, item_stock, id_category) ';
                        $query .= "VALUES ('$cols[1]', '$cols[2]', '$cols[3]', '$cid')";
                        mysql_query($query);
                        if (mysql_affected_rows() == 1){
                            $id_item = mysql_insert_id();
                            $result++;
                        }
                    }
                }
                if ($result == 0)
                    $result = ($row > 0) ? -4 : -3;
            } else // colums is mismatch
                $result = -1;
            fclose($fp);
        } else
            $result = -2; // system error, can't open the file      
    }
    return $result;
}



?>
