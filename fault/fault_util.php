<?php



function count_fault_request_by_status($status){

    $result = 0;

    $dept = defined('USERDEPT') ? USERDEPT : 0;

    $query  = "SELECT count(fr.id_fault) 

        FROM fault_report fr 

        LEFT JOIN fault_category fc ON fr.fault_category = fc.id_category 

        WHERE fault_status = '$status' AND id_department = $dept";

    $rs = mysql_query($query);

    if ($rs && (mysql_num_rows($rs)>0)){

        $rec = mysql_fetch_row($rs);

        $result = $rec[0];

    }

    return $result;

}


function count_fault_report_status($status){

    $result = 0;

    $dept = defined('USERDEPT') ? USERDEPT : 0;

    $query  = "SELECT count(fr.id_fault) 

        FROM fault_report fr 

        LEFT JOIN fault_category fc ON fr.fault_category = fc.id_category 

        WHERE fault_status = '$status' AND id_department = $dept";

    $rs = mysql_query($query);

    if ($rs && (mysql_num_rows($rs)>0)){

        $rec = mysql_fetch_row($rs);

        $result = $rec[0];

    }

    return $result;

}

function query_fault_request_by_status($status, $start = 0, $limit = RECORD_PER_PAGE ){
	
	

	$dept = defined('USERDEPT') ? USERDEPT : 0;

	$query  = "SELECT fr.id_fault, date_format(fault_date, '%d-%b-%Y %H:%i') as fault_date,  DATEDIFF(completion_date, report_date ) as lead_time,

                 date_format(report_date, '%d-%b-%Y %H:%i') as report_date, loc.location_name fault_location, 

                 user.full_name , category_name, fault_description, fault_status,

                 date_format(rect.rectify_date, '%d-%b-%Y %H:%i') as rectify_date, 

                 date_format(rect.completion_date, '%d-%b-%Y %H:%i') as completion_date,

				 rectify_remark, completion_remark 

                 FROM fault_report fr 

                 LEFT JOIN user ON report_user = user.id_user 

                 LEFT JOIN fault_category fc ON fr.fault_category = fc.id_category 

                 LEFT JOIN fault_rectification rect ON rect.id_fault = fr.id_fault 

                 LEFT JOIN location loc ON loc.id_location = fr.id_location 

                 WHERE fault_status = '$status' AND fc.id_department = $dept ";
		
	$query .= " ORDER BY fr.report_date LIMIT $start, $limit";

	$rs = mysql_query($query);

    //echo mysql_error();
	error_log($query.mysql_error());

	return $rs;

}



function get_fault_request_by_status($status,  $orderby, $sort, $start = 0, $limit = RECORD_PER_PAGE){

	$result = array();

	$rs = query_fault_request_by_status($status,  $orderby, $sort, $start, 0, $limit = RECORD_PER_PAGE);

	if ($rs && (mysql_num_rows($rs)>0))

		while ($rec = mysql_fetch_assoc($rs))

			$result[] = $rec;    

	return $result;

}



function get_fault_request($id = 0){

    $result = array();

    $dtf = "%d-%b-%Y %H:%i";    

	$query  = "SELECT fr.id_fault, date_format(fault_date, ' $dtf') as fault_date,  

                 date_format(report_date, ' $dtf') as report_date, user.user_email, 

                 user.full_name, category_name, fault_description, fault_status, 

                 location_name fault_location, fc.id_department, user.contact_no,

                 user.full_name reporter, fc.id_category  

                 FROM fault_report fr 

                 LEFT JOIN user ON report_user = user.id_user 

                 LEFT JOIN fault_category fc ON fr.fault_category = fc.id_category 

                 LEFT JOIN location loc ON loc.id_location = fr.id_location 

                 WHERE fr.id_fault = '$id' ";

    $rs = mysql_query($query); 

    if ($rs && (mysql_num_rows($rs)>0))

        $result = mysql_fetch_assoc($rs);

    return $result;

}



function get_fault_rectification($id = 0){

    $result = array();

    $dtf = "%d-%b-%Y %H:%i";    

	$query  = "SELECT fr.*, date_format(rectify_date, '$dtf') as rectify_date,

                date_format(completion_date, '$dtf') as completion_date 

                 FROM fault_rectification fr 

                 WHERE fr.id_fault = '$id' ";

    $rs = mysql_query($query); 

    if ($rs && (mysql_num_rows($rs)>0))

        $result = mysql_fetch_assoc($rs);

    return $result;

}



function send_submit_fault_report_notification($id = 0){

    global $transaction_prefix, $configuration;

    $config = $configuration['fault'];

    

    if ($config['enable_notification'] != 'true') return false;

    $data = get_fault_request($id);

    if (count($data) == 0) return false;

  

    $report_no = $transaction_prefix.$id;

    $figi_url = FIGI_URL;

    $data['report_no'] = $report_no;

    $data['figi_url'] = $figi_url;

    

    if ($config['enable_notification_email'] == 'true'){

        $emails = array($data['user_email']);

        $email_rec = get_notification_emails($data['id_department'],  $data['id_category'], 'fault');

        foreach ($email_rec as $rec)

            $emails[] = $rec['email'];

        if (count($emails) > 0) {

            $message = compose_message('messages/fault-report-submit.msg', $data);

            $to = array_shift($emails);

            $cc = implode(',', $emails);

            $subject = 'Fault Report ('. $report_no . ') by ' . $data['full_name'];

            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);

            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'fault', 'email');

            process_notification($id_msg);        

        }

    }

    
	if(sms_fault_reporting){
		if ($config['enable_notification_sms'] == 'true'){

			$message = null;

			$mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'fault'); // $data['id_category']

			$mobiles = array_keys($mobile_rec);
			$check_numb_sms = check_numb_sms($data['contact_no']);
			if (!empty($data['contact_no']) && $check_numb_sms)

				array_unshift($mobiles, $data['contact_no']);

			$to = implode(',', $mobiles);

			$message = compose_message('messages/fault-report-submit.sms', $data);

			//SendSMS(SMS_SENDER, $to, $message);

			$id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'fault', 'sms');

			process_notification($id_msg);

			writelog('send_submit_fault_report_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message .'|');

		}
	}
}



function send_fault_report_rectify_notification($id = 0){

    global $transaction_prefix, $configuration;

    $config = $configuration['fault'];

    

    if ($config['enable_notification'] != 'true') return false;

    $data = get_fault_request($id);

    if (count($data) == 0) return false;

  

    $report_no = $transaction_prefix.$id;

    $figi_url = FIGI_URL;

    $data['report_no'] = $report_no;

    $data['figi_url'] = $figi_url;



    $rectification = get_fault_rectification($id);

    $data = array_merge($data, $rectification);

    $users = get_user_list();

    $rectified_by = (!empty($users[$rectification['rectified_by']])) ? $users[$rectification['rectified_by']] : null;

    //$completed_by = (!empty($users[$rectification['completed_by']])) ? $users[$rectification['completed_by']] : null;

    $data['rectified_by'] = $rectified_by;

    //$data['completed_by'] = $completed_by;

    

    if ($config['enable_notification_email'] == 'true'){

        $emails = array($data['user_email']);

        $email_rec = get_notification_emails($data['id_department'], $data['id_category'], 'fault');

        foreach ($email_rec as $rec)

            $emails[] = $rec['email'];

        if (count($emails) > 0) {

            $message = compose_message('messages/fault-report-rectify.msg', $data);

            $to = array_shift($emails);

            $cc = implode(',', $emails);

            $subject = 'Rectification of Reported Fault ('. $report_no . ') has been Completed';

            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);

            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'fault', 'email');

            process_notification($id_msg);        

        }

    }

    if(sms_fault_reporting){
		if ($config['enable_notification_sms'] == 'true'){

			$message = null;

			$mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'fault');

			$mobiles = array_keys($mobile_rec);
			$check_numb_sms = check_numb_sms($data['contact_no']);
			if (!empty($data['contact_no']) && $check_numb_sms)

				array_unshift($mobiles, $data['contact_no']);

			$to = implode(',', $mobiles);

			$message = compose_message('messages/fault-report-rectify.sms', $data);

			//SendSMS(SMS_SENDER, $to, $message);

			$id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'fault', 'sms');

			process_notification($id_msg);

			writelog('send_fault_report_rectify_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);

		}
	}   

}



function send_fault_report_completed_notification($id = 0){

    global $transaction_prefix, $configuration;

    $config = $configuration['fault'];

    

    if ($config['enable_notification'] != 'true') return false;

    $data = get_fault_request($id);

    if (count($data) == 0) return false;

  

    $report_no = $transaction_prefix.$id;

    $figi_url = FIGI_URL;

    $data['report_no'] = $report_no;

    $data['figi_url'] = $figi_url;



    $rectification = get_fault_rectification($id);

    $data = array_merge($data, $rectification);

    $users = get_user_list();

    $rectified_by = (!empty($users[$rectification['rectified_by']])) ? $users[$rectification['rectified_by']] : null;

    $completed_by = (!empty($users[$rectification['completed_by']])) ? $users[$rectification['completed_by']] : null;

    $data['rectified_by'] = $rectified_by;

    $data['completed_by'] = $completed_by;

    

    if ($config['enable_notification_email'] == 'true'){

        $emails = array($data['user_email']);

        $email_rec = get_notification_emails($data['id_department'], $data['id_category'], 'fault');

        foreach ($email_rec as $rec)

            $emails[] = $rec['email'];

        if (count($emails) > 0) {

            $message = compose_message('messages/fault-report-rectified.msg', $data);

            $to = array_shift($emails);

            $cc = implode(',', $emails);

            $subject = 'Rectification of Reported Fault ('. $report_no . ') has been Completed';

            //SendEmail(SYSTEM_EMAIL, $to, $subject, $message, $cc);

            $id_msg = set_notification_message($configuration['global']['system_email'], $to, $subject, $message, $cc, 'fault', 'email');

            process_notification($id_msg);        

        }

    }

    
	if(sms_fault_reporting){
		if ($config['enable_notification_sms'] == 'true'){

			$message = null;

			$mobile_rec = get_notification_mobiles($data['id_department'], $data['id_category'], 'fault');

			$mobiles = array_keys($mobile_rec);
			$check_numb_sms = check_numb_sms($data['contact_no']);
			if (!empty($data['contact_no']) && $check_numb_sms)

				array_unshift($mobiles, $data['contact_no']);

			$to = implode(',', $mobiles);

			$message = compose_message('messages/fault-report-rectified.sms', $data);

			//SendSMS(SMS_SENDER, $to, $message);

			$id_msg = set_notification_message($configuration['global']['sms_sender'], $to, null, $message, null, 'fault', 'sms');

			process_notification($id_msg);

			writelog('send_fault_report_completed_notification(): '. $configuration['global']['sms_sender'] . '|' . $to . '|' . $message);

		}
	}   

}



function count_fault_category($dept = 0){

	$result = 0;

	$query  = "SELECT count(*) FROM fault_category_department cd 

                LEFT JOIN fault_category c ON c.id_category = cd.id_category 

                WHERE c.id_category IS NOT NULL ";

	if ($dept > 0)

		$query .= ' AND id_department = ' . $dept;

	$rs = mysql_query($query);

	if ($rs && mysql_num_rows($rs)){

		$rec = mysql_fetch_row($rs);

		$result = $rec[0];

	}

	return $result;

}



function get_fault_categories($sort = 'asc', $start = 0, $limit = 10, $dept = 0){

    $result = false;

	$query  = "SELECT c.* 

				FROM fault_category_department cd 

                LEFT JOIN fault_category c ON c.id_category = cd.id_category 

                WHERE c.id_category IS NOT NULL";

	if ($dept > 0)

		$query .= ' AND cd.id_department = ' . $dept;

	$query .= " ORDER BY category_name $sort LIMIT $start,$limit ";

	$result = mysql_query($query);

    return $result;

}



function get_fault_category_list($department = 0, $swap = false, $lowercase = false) {

	$data = array();

	$query = 'SELECT fcd.id_category, category_name 

                FROM fault_category_department fcd

                LEFT JOIN fault_category fc ON fcd.id_category=fc.id_category ';

	if ($department > 0) 

		$query .= " WHERE fcd.id_department = $department ";

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



function build_fault_category_combo($selected = -1, $department = 0, $onchange = null) {

    

	return build_combo('id_category', get_category_list($department), $selected, $onchange);

}



function get_machine_record_for_fault($id)

{

    $result = 0;

	$query  = "SELECT mh.id_machine, id_item 

                FROM machine_history mh 

                LEFT machine_info mi ON mi.id_machine = mh.id_machine 

                WHERE fault_reference = '$id'";

	$rs = mysql_query($query);

    if ($rs && mysql_num_rows($rs)>0){

        $rec = mysql_fetch_assoc($rs);

        $result = $rec['id_item'];

    }

    return $result;

}



function export_fault_request_status($status)

{

    $crlf = "\r\n";

    ob_clean();

    ini_set('max_execution_time', 60);

    $today = date('dMY');

	$fname = 'figi_'.strtolower($status)."_fault-$today.csv";

    header("Content-type: text/x-comma-separated-values");

    header("Content-Disposition: attachment; filename=$fname");

    header("Pragma: no-cache");

    header("Expires: 0");

	$transaction_prefix = TRX_PREFIX_FAULT;

    $total = count_fault_request_by_status($status);

    $rs = query_fault_request_by_status($status, 0, $total);

	if ($rs) // && (mysql_num_rows($rs)>0)

		switch(strtolower($status)){

		case 'notified':

			echo 'No,Date of Report,Reporter,Fault Date,Category,Description'.$crlf;

				while ($rec = mysql_fetch_assoc($rs)){

					echo "$transaction_prefix$rec[id_fault],$rec[report_date],$rec[full_name],$rec[fault_date],";

					echo "$rec[category_name],$rec[fault_description]$crlf";

				}

			break;

		case 'progress':

			echo 'No,Date of Report,Reporter,Rectification Date,Category,Rectification Remark'.$crlf;

				while ($rec = mysql_fetch_assoc($rs)){

					echo "$transaction_prefix$rec[id_fault],$rec[report_date],$rec[full_name],$rec[rectify_date],";

					echo "$rec[category_name],$rec[rectify_remark]$crlf";

				}

			break;

		case 'completed':

			echo 'No,Date of Report,Reporter,Rectification Date,Completion Date,Category,Completion Remark'.$crlf;

				while ($rec = mysql_fetch_assoc($rs)){

					echo "$transaction_prefix$rec[id_fault],$rec[report_date],$rec[full_name],$rec[rectify_date],";

					echo "$rec[completion_date],$rec[category_name],$rec[completion_remark]$crlf";

				}

			break;

	}

    ob_end_flush();

    exit;

}
/*
function get_attachment_service($id){

	$query = "SELECT * FROM fault_report_attachment WHERE id_attach = $id";

	$rs = mysql_query($query);

	$data = mysql_fetch_assoc($rs);

	return $data;

}
*/

function get_fault_attachment($id){

	$query = "SELECT id_attach,filename FROM fault_report_attachment WHERE id_fault = $id";

	$rs = mysql_query($query);

	$data = array();

	while($rec = mysql_fetch_assoc($rs)){

		$data[] = $rec;

	}

	$str = "<ul style='padding-left: 21px;margin: 0px;'>";

	

	foreach($data as $row){

		$str .= "<li><a href='./?mod=fault&sub=fault&act=get_files&id=$row[id_attach]'>$row[filename]</a></li>"; 

	}

	$str .= "</ul>";

	return $str;

}

function display_fault_report($rec, $forprint = false){

    global $transaction_prefix;

    $mhr_info = null;

    $mhr = get_machine_record_for_fault($rec['id_fault']);

	$rec['fault_attachment'] = get_fault_attachment($rec['id_fault']);

    if ($mhr > 0) {

        $mhr_info = <<<MHR

      <tr valign="top" class="normal">  

        <td align="left">Item</td>

        <td align="left"><a href="./?mod=item&act=view&id=$mhr">$mhr</a></td>    

      </tr>

MHR;

    }



?>

<table cellpadding=4 cellspacing=1 class="fault_table" >

  <tr valign="top" align="left">

    <th align="left" colspan=4>Fault Report

<?php if (!$forprint){ ?>

        <div class="foldtoggle"><a id="btn_fault_report" rel="open" href="javascript:void(0)">&uarr;</a></div>

<?php } // forprint ?>            

    </th>

  </tr>  

  <tbody id="fault_report">

  <tr valign="top" align="left">

    <td align="left" width=130>Report No</td>

    <td align="left"><?php echo $transaction_prefix.$rec['id_fault']?></td>

  </tr>  

  <tr valign="top" class="alt">  

    <td align="left">Date/Time of Report</td>

    <td align="left"><?php echo $rec['report_date']?></td>

  </tr>

  <tr valign="top" class="normal">  

    <td align="left">Reporter</td>

    <td align="left"><?php echo $rec['full_name']?></td>

  </tr>  

  <tr valign="top" class="alt">  

    <td align="left">Fault Date/Time</td>

    <td align="left"><?php echo $rec['fault_date']?></td>

  </tr>

  <tr valign="top" class="normal">  

    <td align="left">Category</td>

    <td align="left"><?php echo $rec['category_name']?></td>

  </tr>  

  <tr valign="top" class="alt">  

    <td align="left">Fault Location</td>

    <td align="left"><?php echo $rec['fault_location']?></td>    

  </tr>

  <tr valign="top" class="normal">  

    <td align="left">Fault Description</td>

    <td align="left"><?php echo $rec['fault_description']?></td>    

  </tr>

  <tr valign="top" class="normal">  

    <td align="left">Fault Attachment</td>

    <td align="left"><?php echo $rec['fault_attachment']?></td>    

  </tr>

  <?php echo $mhr_info?>

  </tbody>

</table>

<script>

    $('#btn_fault_report').click(function (e){

        toggle_fold(this);

    });

</script>

<?php

}



function display_fault_rectified($rec, $forprint = false){

    $fold_btn = (!$forprint) ? '<div class="foldtoggle"><a id="btn_fault_rectification" rel="open" href="javascript:void(0)">&uarr;</a></div>' : null;



echo <<<RECTIFIED

    <table cellpadding=4 cellspacing=1 class="fault_table detail" >

    <th align="left" colspan=4>Fault Rectification $fold_btn</th>

    </tr>  

    <tbody id="fault_rectification">

      <tr valign="middle" align="left">

        <td align="left" width=130>Rectified by</td>

        <td align="left">$rec[rectified_by]</td>

      </tr>  

      <tr valign="top" class="alt" >  

        <td align="left">Date of Rectification</td>

        <td align="left">$rec[rectify_date]</td>    

      </tr>

      <tr valign="top">  

        <td align="left">Remark</td><td align="left">$rec[rectify_remark]</td>    

      </tr>

    </tbody>

    </table>

<script>

    $('#btn_fault_rectification').click(function (e){

        toggle_fold(this);

    });

</script>

RECTIFIED;

}



function display_fault_completion($rec, $forprint = false){

    $fold_btn = (!$forprint) ? '<div class="foldtoggle"><a id="btn_fault_completion" rel="open" href="javascript:void(0)">&uarr;</a></div>' : null;



echo <<<COMPLETION

    <table cellpadding=4 cellspacing=1 class="fault_table detail" >

    <th align="left" colspan=4>Fault Completion $fold_btn</th>

    </tr>  

    <tbody id="fault_completion">

      <tr valign="middle" align="left">

        <td align="left" width=130>Rectified by</td>

        <td align="left">$rec[completed_by]</td>

      </tr>  

      <tr valign="top" class="alt" >  

        <td align="left">Date of Rectification</td>

        <td align="left">$rec[completion_date]</td>    

      </tr>

      <tr valign="top">  

        <td align="left">Remark</td><td align="left">$rec[completion_remark]</td>    

      </tr>

    </tbody>

    </table>

<script>

    $('#btn_fault_completion').click(function (e){

        toggle_fold(this);

    });

</script>

COMPLETION;

}



function get_available_fault_category_list($department = 0, $swap = false, $lowercase = false) {

    $data = array();

    $wheres = array();

    $query = 'SELECT DISTINCT(category_name), c.id_category  FROM fault_category  c 

                LEFT JOIN fault_category_department dc ON dc.id_category = c.id_category ';

    //if ($department > 0) $wheres[] = " dc.id_department != '$department' ";

    $wheres[] = " c.id_category NOT IN (SELECT id_category FROM fault_category_department WHERE id_department = '$department') ";

    if (count($wheres) > 0)

        $query .= ' WHERE ' . implode(' AND ', $wheres);



    $query .= ' ORDER BY category_name ASC ';

    

    $rs = mysql_query($query);

    //echo mysql_error().$query;

    while ($rec = mysql_fetch_row($rs))

        if ($swap){

            if ($lowercase)

                $rec[0] = strtolower($rec[0]);

            $data[$rec[0]] =$rec[1];

        } else

            $data[$rec[1]] =$rec[0];

    return $data;

}


function count_fault_request_by_date($status, $date){

    $result = 0;

    $dept = defined('USERDEPT') ? USERDEPT : 0;

    $query  = "SELECT count(fr.id_fault) 

        FROM fault_report fr 

        LEFT JOIN fault_category fc ON fr.fault_category = fc.id_category 

        WHERE fault_status = '$status' AND id_department = $dept AND DATE(report_date) = '".$date."' ";

    $rs = mysql_query($query);

    if ($rs && (mysql_num_rows($rs)>0)){

        $rec = mysql_fetch_row($rs);

        $result = $rec[0];

    }

    return $result;

}



function get_fault_request_by_date($status, $start = 0, $limit = RECORD_PER_PAGE, $date){

	$result = array();

	$rs = query_fault_request_by_date($status, $start, $limit, $date);

	if ($rs && (mysql_num_rows($rs)>0))

		while ($rec = mysql_fetch_assoc($rs))

			$result[] = $rec;    

	return $result;

}

function query_fault_request_by_date($status, $start = 0, $limit = RECORD_PER_PAGE , $date){
	
	

	$dept = defined('USERDEPT') ? USERDEPT : 0;

	$query  = "SELECT fr.id_fault, date_format(fault_date, '%d-%b-%Y %H:%i') as fault_date,  DATEDIFF(completion_date, report_date ) as lead_time,

                 date_format(report_date, '%d-%b-%Y %H:%i') as report_date, loc.location_name fault_location, 

                 user.full_name , category_name, fault_description, fault_status,

                 date_format(rect.rectify_date, '%d-%b-%Y %H:%i') as rectify_date, 

                 date_format(rect.completion_date, '%d-%b-%Y %H:%i') as completion_date,

				 rectify_remark, completion_remark 

                 FROM fault_report fr 

                 LEFT JOIN user ON report_user = user.id_user 

                 LEFT JOIN fault_category fc ON fr.fault_category = fc.id_category 

                 LEFT JOIN fault_rectification rect ON rect.id_fault = fr.id_fault 

                 LEFT JOIN location loc ON loc.id_location = fr.id_location 

                 WHERE fault_status = '$status' AND fc.id_department = $dept ";
				 
	if(!empty($date)){
		$query .= " AND DATE(report_date) = '".$date."' ";
	}

	$query .= " ORDER BY report_date LIMIT $start, $limit";

	$rs = mysql_query($query);

    //echo mysql_error();
	error_log($query.mysql_error());

	return $rs;

}


function count_get_fault_data($status){

    $result = 0;

    $dept = defined('USERDEPT') ? USERDEPT : 0;

    $query  = "SELECT count(fr.id_fault) 

        FROM fault_report fr 

        LEFT JOIN fault_category fc ON fr.fault_category = fc.id_category 

        WHERE fault_status = '$status' AND id_department = $dept";

    $rs = mysql_query($query);

    if ($rs && (mysql_num_rows($rs)>0)){

        $rec = mysql_fetch_row($rs);

        $result = $rec[0];

    }

    return $result;

}

function query_fault_data($status, $_orderby, $sort_order, $_start, $_limit){
	
	$dept = defined('USERDEPT') ? USERDEPT : 0;

	$query  = "SELECT fr.id_fault, date_format(fault_date, '%d-%b-%Y %H:%i') as fault_date,  DATEDIFF(completion_date, report_date ) as lead_time,

                 date_format(report_date, '%d-%b-%Y %H:%i') as report_date, loc.location_name fault_location, 

                 user.full_name , category_name, fault_description, fault_status,

                 date_format(rect.rectify_date, '%d-%b-%Y %H:%i') as rectify_date, 

                 date_format(rect.completion_date, '%d-%b-%Y %H:%i') as completion_date,

				 rectify_remark, completion_remark 

                 FROM fault_report fr 

                 LEFT JOIN user ON report_user = user.id_user 

                 LEFT JOIN fault_category fc ON fr.fault_category = fc.id_category 

                 LEFT JOIN fault_rectification rect ON rect.id_fault = fr.id_fault 

                 LEFT JOIN location loc ON loc.id_location = fr.id_location 

                 WHERE fault_status = '$status' AND fc.id_department = $dept ";
		
		
	$query .= " ORDER BY $_orderby $sort_order LIMIT $_start, $_limit";

	$rs = mysql_query($query);

    //echo mysql_error();
	error_log($query.mysql_error());

	return $rs;

}



function get_fault_data($status, $_orderby, $sort_order, $_start, $_limit){

	$result = array();

	$rs = query_fault_data($status, $_orderby, $sort_order, $_start, $_limit);

	if ($rs && (mysql_num_rows($rs)>0))

		while ($rec = mysql_fetch_assoc($rs))

			$result[] = $rec;    

	return $result;

}


?>