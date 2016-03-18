<?php

date_default_timezone_set('Asia/Singapore');

$charlist = " \t\n\r\0\x0B".DIRECTORY_SEPARATOR;
$thisdir = rtrim(dirname(__FILE__), $charlist);
$updir = substr($thisdir, 0, strrpos($thisdir, DIRECTORY_SEPARATOR)+1);

define('BASE_PATH', $updir);

error_log('item return notification start.....');
if ((ITEM_EMAIL_NOTIFICATION == FALSE) || (!ENABLE_NOTIFICATION)){
    error_log("notification disabled or non-periodic alert type\n");
	exit;
}

$rs = get_stocktake_notification_frequency();
if ($rs && mysql_num_rows($rs) > 0){
	error_log('found: ' . mysql_num_rows($rs) . ' records');
	$dow_list = array(0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat');
    while ($rec = mysql_fetch_assoc($rs)){
		$status_active = $rec['status'];
		if($status_active > 0){
			$fq = explode("|", $rec['frequency']);
			$fq_freq = $fq[0];
			error_log($fq_freq);
			
			$now = date('d-m-Y H:i');
			
			if($fq_freq == "daily"){
				$time_preparing_daily = date('d-m-Y '.$fq[1]);
				
				if($time_preparing_daily == $now){ set_email_notification($rec['id_group'], $rec['id_department'], $rec['frequency'],$base_url,$from); } else { $a=" not Same"; }
				error_log("Daily : ".$time_preparing_daily.$a.$now);
			}
			
			if($fq_freq == "weekly"){
				$time_preparing_weekly = date($fq[2]);
				$day_preparing_weekly = $dow_list[$fq[1]];
				$day_weekly = date('D');
				$time_weekly = date('H:i');
				
				if(($day_preparing_weekly == $day_weekly) && ($time_preparing_weekly == $time_weekly)){
					set_email_notification($rec['id_group'], $rec['id_department'], $rec['frequency'],$base_url,$from);
				}  else { $a=" not Same"; }
				error_log("Weekly : ".$time_preparing.$a);
				
			}
			
			if($fq_freq == "monthly"){
				$time_preparing_monthly = date($fq[1]+1 .'-m-Y '.$fq[2]);
				
				if($time_preparing_monthly == $now){
					set_email_notification($rec['id_group'], $rec['id_department'], $rec['frequency'],$base_url,$from);
				} else { $a=" not Same"; }
				error_log("Monthly : ".$time_preparing_monthly.$a);
			}
			
			if($fq_freq == "yearly"){
				$time_preparing_yearly = date($fq[1].'-Y '.$fq[2]);
				
				if($time_preparing_yearly == $now){
					set_email_notification($rec['id_group'], $rec['id_department'], $rec['frequency'],$base_url,$from);
				}else { $a=" not Same"; }
				error_log($time_preparing_yearly.$a);
			}
		}	
       //set_email_notification($rec['id_group'], $rec['id_department'], $rec['frequency'],$base_url,$from);
    }
}

function get_stocktake_notification_frequency(){
$query = "SELECT id_group, id_department, frequency FROM notification_frequency";

$mysql_query = mysql_query($query);
return $mysql_query;
}

function set_email_notification($id_group, $id_department, $frequency, $base_url, $sender){
	$type_list = array('weekly'=>'Weekly','monthly'=>'Monthly','yearly'=>'Yearly');
	$dow_list = array(0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat');
	$dom_list = range(1,31);
	$months = array(0=>'Jan',1=>'Feb',2=>'Mar',3=>'Apr',4=>'May',5=>'Jun',6=>'Jul', 7 =>'Aug', 8=>'Sep', 9=>'Oct', 10=>'Nov', 11=>'Des');

	$frequency_arr = explode("|",$frequency);
	$freq = $frequency_arr[0];
	
	if($freq == "daily") { $time = $frequency_arr[1]; $current_date = date("d F Y"); } 
	if($freq == "weekly"){$time = $dow_list[$frequency_arr[1]]."/".$frequency_arr[2]; $current_date = date("d F Y");}
	if($freq == "monthly"){$time = $dom_list[$frequency_arr[1]]."/".$frequency_arr[2]; $current_date = date("F Y");}
	if($freq == "yearly"){
		$a=$frequency_arr[1];
		$time = $a."/".$frequency_arr[2]; 
		$current_date = date("d F Y");
	}
	
	if($id_group == 14){
	
		$query = "SELECT full_name, user_email, id_department FROM user";
		$rs = mysql_query($query);
		$r=array();
		while($row=mysql_fetch_array($rs)){
		
		$department = get_department_by_id($row['id_department']);
		$department_name = $department ? $department : "Administrator";
		$full_name = $row['full_name'];
		
		
		$message ="
Dear $full_name,
<br /><br />
Please be reminded that your assets/items for $department_name are due for Stock Taking for $current_date.<br />
Login to <b><a href='$base_url' target='BLANK' title='Login to figi'>Figi</a></b> to carry out stock taking processes.
<br /><br />
Thank you.
<br /><br />
Yours truly<br />
FiGi System Adminsitrator";

		$subject = 'Item Stock Take Reminder';
		
		$to = $row['user_email'];
		$from = $sender;
		$cc = $sender;
		//error_log("$to - $subject - $message");
		//error_log($row['id_department']);
		
        $id_msg = set_notification_message($from, $to, $subject, $message, $cc, 'item', 'email');
		process_notification($id_msg);
		}
		
	} else {
	
		$query = "SELECT full_name, user_email, id_department FROM user WHERE id_department = $id_department";
		$rs = mysql_query($query);
		$r=array();
		while($row=mysql_fetch_array($rs)){
		/*==========START WHILE==========*/
		$department = get_department_by_id($row['id_department']);
		$department_name = $department ? $department : "Administrator";
		$full_name = $row['full_name'];
		
				$message ="
Dear $full_name,
<br /><br />
Please be reminded that your assets/items for $department_name are due for Stock Taking for $current_date.<br />
Login to <b><a href='$base_url' target='BLANK' title='Login to figi'>Figi</a></b> to carry out stock taking processes.
<br /><br />
Thank you.
<br /><br />
Yours truly<br />
FiGi System Adminsitrator";


		$subject = 'Items Stock Take Reminder';
		
		$to = $row['user_email'];
		$from = $sender;
		$cc = $sender;
		error_log("$to - $subject - $message");
		//error_log($row['id_department']);
		
        $id_msg = set_notification_message($from, $to, $subject, $message, $cc, 'item', 'email');
		process_notification($id_msg);
		/*==========END WHILE==========*/
		}
		
	}
	

}

function get_department_by_id($id_department){
	$query = "SELECT id_department, department_name FROM department WHERE id_department = $id_department";
	$rs = mysql_query($query);
	$r = mysql_fetch_array($rs);
	return $r['department_name'];
}
?>