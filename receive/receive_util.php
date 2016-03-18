<?php

function get_receive($nric = 0, $acknowledge = 0, $start = 0, $limit = 10){
	$query = "SELECT r.*, l.location_name"; 
	$query .= " FROM receive r left join location l on l.id_location = r.id_location";	
	$query .= " where (r.nric_for_whom = '$nric' or r.nric_received = '$nric') and r.acknowledge=$acknowledge " ;
	$query .= " ORDER BY id_receive DESC LIMIT $start, $limit";	
	//print_r(mysql_error().$query);
	$mysql = mysql_query($query);
	return $mysql;
}

function get_myreceive($nric = 0){
	$result = null;
	$query = "SELECT r.*, l.location_name"; 
	$query .= " FROM receive r left join location l on l.id_location = r.id_location";	
	$query .= " where r.nric_for_whom = '$nric' and r.acknowledge = 0";
	$query .= " ORDER BY id_receive DESC LIMIT 0, 1";	
	//print_r(mysql_error().$query);
	$mysql = mysql_query($query);
	if ($mysql && (mysql_num_rows($mysql)>0))
        $result = mysql_fetch_assoc($mysql);
	return $result;
}

function count_receive($nric = 0, $acknowledge = 0){
	$query = "SELECT count(*) as total FROM receive ";	
	$query .= " where (r.nric_for_whom = '$nric' or r.nric_received = '$nric') and r.acknowledge=$acknowledge " ;
	$mysql = mysql_query($query);
	$fetch = mysql_fetch_array($mysql);	
	return $fetch['total'];	
}


function receive_view($id = 0){
	$result = array();
	$query = "SELECT r.*, l.location_name FROM receive r left join location l on l.id_location = r.id_location";	
	$query .= " where r.id_receive = '$id'";
	//print_r(mysql_error().$query);	
	$rs = mysql_query($query);	
	if ($rs && (mysql_num_rows($rs)>0))
        $result = mysql_fetch_assoc($rs);
    return $result;	
}

function getUsers(){
	$result = array();
	$query = "select * from user";		
	$rs = mysql_query($query);	
	while($rec = mysql_fetch_assoc($rs)){
		$result[$rec['nric']]['full_name'] = $rec['full_name'];
		$result[$rec['nric']]['email'] = $rec['user_email'];
	}
	
    return $result;	
}


function send_receive_alert($data){
    global $configuration;
    $config = $configuration['receive'];
    
    if ($config['enable_email_receive'] != 'true') return false;
    $id_receive = $data['id_receive'];
    $receive = receive_view($id_receive);
	$users = getUsers();
	
    $item = get_item_receive($receive['id_receive']);
	
    $figi_url = FIGI_URL;
	$data['figi_url'] = $figi_url;

    $nric_for_whom = $receive['nric_for_whom'];
    $nric_received = $receive['nric_received'];
	$subject = 'Receive - '.$receive['do_number'];
	$no = 1;
	while($_data = mysql_fetch_array($item)){
		$items[] = $_data['serial_no'];
	}
	$data['item'] = implode(',', $items);
    $data['fullname_for_whom'] = $users[$nric_for_whom]['full_name'];
    $data['fullname_received'] = $users[$nric_received]['full_name'];	
	$data['do_number'] = $receive['do_number'];
	$data['invoice_number'] = $receive['invoice_number'];
	$data['company_name'] = $receive['company_name'];
	$data['date_received'] = $receive['date_received'];
	$data['qty'] = $receive['qty'];	
	$data['email'] = $users[$nric_for_whom]['email'];
	
    if ($config['enable_email_receive'] == 'true'){
       $email = $data['email'];
	   $cc = null;
	   $to = $email;
	  
	  $message = compose_message('messages/receive-alert.msg', $data);
	  $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'receive', 'email');
      process_notification($id_msg);
    }
}

function get_item_receive($id = 0){
	$result = array();
	$query = "select serial_no from receive_item where id_receive ='$id'";
	$rs = mysql_query($query);	
	
    return $rs;
}

function count_item_receive($id = 0){
	//$result = array();
	$query = "select count(*) as ttal from receive_item where id_receive ='$id'";
	$mysql = mysql_query($query);
	$fetch = mysql_fetch_array($mysql);	
	return $fetch['ttal'];	
}

function display_receive($receive, $forprint = false){   
$item = get_item_receive($receive['id_receive']);
$cnt = count_item_receive($receive['id_receive']);
$users = getUsers();
$date = date_create($receive['date_received']);
?>

    <table width="100%" class="itemlist loan issue" >
	<thead>
      <tr >
        <th class="left" colspan=4>Receive Information
<?php if (!$forprint){ ?>
            <div class="foldtoggle"><a id="btn_loan_request" rel="open" href="javascript:void(0)">&uarr;</a></div>
<?php } // forprint ?>            
        </th>
      </tr>  
	</thead>
      <tbody id="loan_request">
      <tr  class="alt">
        <td align="left" width=100 >DO Number</td>
        <td align="left" width=260>
            <?php 
            echo $receive['do_number'];
           
        ?>
        </td>
        <td align="left" width=140>Received Date/Time</td>
        <td align="left" width=240><?php echo date_format($date, 'd-M-Y H:i');?></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Invoice Number</td>
        <td align="left"><?php echo $receive['invoice_number']?></td>
        <td align="left">Received by</td>
        <td align="left"><?php echo $users[$receive['nric_received']]['full_name']?></td>
      </tr>
      <tr valign="top" class="alt">  
        <td align="left">For Whom</td>
        <td align="left"><?php echo $users[$receive['nric_for_whom']]['full_name']?></td>
        <td align="left">Quantity</td>
        <td align="left"><?php echo $cnt; ?></td>
      </tr>  
      <tr valign="top">  
        <td align="left">Serial Number</td>
        <td align="left" colspan=3>
			<?php
				$no = 1;
				if(!empty($item)){
					echo 'N/A';
				}else{
					while($data = mysql_fetch_array($item)){
						echo $no.'. '.$data['serial_no'].'<br/>';
						$no++;
					}
				}			
			?>
		</td>
      </tr>
     
      </tbody>
    </table>
<?php if (!$forprint){ ?>
    <script>
    $('#btn_loan_request').click(function (e){
        toggle_fold(this);
    });
    </script>
<?php
	}
}