<div class="submod_wrap">
	<div class="submod_links">
	<?php
		if (defined('PORTAL')){
			echo '<a href="./?mod=portal&portal=facility" class="button" > Booking Calendar </a>';
		} else {
			echo '<a href="./?mod=booking" class="button" > Cancel </a> ';
			echo '<a href="./?mod=booking&act=import_template" class="button" > Generate Template </a> ';
			echo '<a href="./?mod=booking&act=list_subject" class="button" > Subject List </a>';
		}
	?>
	</div>
	<div class="submod_title"><h4 >Import Booking</h4></div>
	<div class="clear"> </div>
</div>

<?php 

if (!defined('FIGIPASS')) exit;

$_msg = null;
$err = array();
$filename = null;
if (isset($_POST['import'])) {
	$err['code'] = -2;
	
	$filename = $_FILES['csv']['tmp_name'];
	if (is_uploaded_file($filename)) 
		$err = booking_import($filename);
	
	switch($err['code']){
		case -1 : $_msg = 'Import can not be performed. Invalid csv format.'; break;
		case -2 : $_msg = 'Upload was failed. Import can not be performed.'; break;		
		case -3 : $_msg = 'Internal System error.'; break;
		case  0 : $_msg = 'Import booking has been done. Click <a href="./?mod=booking&act=view&id='.$err['id_book'].'">detail</a> to view booking detail.'; break;
	}
}

function booking_import($path)
{
	$err = array('code' => -3);
	$subject_list = booking_subject_list(true);
	$id_subject = 0;
	$reason = '';
	$instruction = '';
	$periods = array();
	$fp = fopen($path, 'r');
	if ($fp){

		//id_facility,id_term,date,id_period,period,recurring,recurring_times,id_subject,reason,instruction
		$skip = fgetcsv($fp, 1024, ',');
		while (($period = fgetcsv($fp, 1024, ',')) !== FALSE){
			$row['id_facility'] = $period[0];
			$row['id_term'] = $period[1];
			$row['date'] = $period[2];
			$row['id_time'] = $period[3];
			$row['recurring'] = $period[5];
			$row['recurring_times'] = $period[6];
			if (!empty($period[7])) $row['id_subject'] = $period[7];
			if (!empty($period[8])) $row['reason'] = $period[8];
			if (!empty($period[9])) $row['instruction'] = $period[9];
			$periods[] = $row;
		}
		fclose($fp);
		$id_book = 0;

		$slice = array_slice($periods, 1, 1);
		$first = $slice[0];
		error_log(serialize($first));
		$purpose = mysql_real_escape_string($first['purpose']);
		$remark = mysql_real_escape_string($first['remark']);
		$recurring = !empty($first['recurring']) ? strtolower($first['recurring']) : 'none';
		$id_facility = $first['id_facility'];
		$id_subject = !empty($first['id_subject']) ? $first['id_subject']: 0;
		$id_user = USERID;
		$dt_instance = 0;
		$status = 'BOOK';
		$is_recurring = ($recurring != 'none');
		$term = period_term_get(0, $id_facility, 1);
		$id_term = (!empty($term['id_term'])) ? $term['id_term']: 0;
		$recurring_times = 1;
		if ($is_recurring)
			$recurring_times = $first['recurring_times'];
		
		if (!empty($id_facility)){
		//error_log(serialize($post));	
			$query = "INSERT INTO booking_list(book_date, id_user, id_facility, recurring, purpose, remark, status, id_subject, id_term, recurring_times)
						VALUE (UNIX_TIMESTAMP(), $id_user, $id_facility, '$recurring', '$purpose', '$remark', '$status', $id_subject, $id_term, $recurring_times)"; 
			mysql_query($query);
		}
		//error_log(mysql_error().$query);
		if (mysql_affected_rows()>0){
			$id_book = mysql_insert_id();
			$ok = $id_book;
			//keep periods
			foreach($periods as $row){
				error_log(serialize($row));
				$dt = strtotime($row['date']);
				$id_time = $row['id_time'];
				$tp_subject = $row['id_subject'];
				$tp_purpose = mysql_real_escape_string($row['purpose']);
				$tp_remark = mysql_real_escape_string($row['remark']);
				
				$query = "INSERT INTO booking_list_period(id_book, booked_date, id_time, id_subject, purpose, remark, is_instance) 
							VALUE($id_book, '$dt', $id_time, $tp_subject, '$tp_purpose', '$tp_remark', 0)";
				if (mysql_query($query)){
				
					// if it's recurring, duplicate period with different date as recurring type selected
					if ($is_recurring && $recurring_times>1){
						$valid_from = !empty($term['valid_from_sec']) ? $term['valid_from_sec'] : 0;
						$valid_to = !empty($term['valid_to_sec']) ? $term['valid_to_sec'] : 0;
						$oneday = 60 * 60 * 24; // s*m*d
						$rdt = $dt; // first booked date
						for ($i=1; $i<$recurring_times; $i++){
							if ('weekly'==$recurring) $rdt += $oneday*7;
							elseif ('fortnightly'==$recurring) $rdt += $oneday*14;
							elseif ('monthly'==$recurring) {
								// get same date for next month
								$rdt = mktime(0, 0, 0, date('n', $rdt)+1, date('j', $rdt), date('Y', $rdt));
							}
							//check rdt if out of valid range date
							//error_log("term: $id_term, vf: $valid_from, dt: $rdt, vt: $valid_to\r\n");
							if ($rdt >= $valid_from && $rdt <= $valid_to) {
								$query = "INSERT INTO booking_list_period(id_book, booked_date, id_time, id_subject, purpose, remark, is_instance) 
											VALUE($id_book, '$rdt', $id_time, $tp_subject, '$tp_purpose', '$tp_remark', 1)";
								mysql_query($query);
								//error_log(mysql_error().$query);
							}
						}
					}
				}
				error_log(mysql_error().$query);
			}
			
			// keep equipment
			if (!empty($post['use_qty'])){
				$equipments = $post['use_qty'];
				if (!empty($equipments) && is_array($equipments)){
					foreach($equipments as $id_equipment => $quantity){
						$query = "INSERT INTO booking_list_equipment(id_book, id_equipment, quantity) VALUE($id_book, $id_equipment, $quantity)";
						mysql_query($query);
						//error_log(mysql_error().$query);
					}
				}
			}
			// keep attachment if any
			attachment_save($id_book);
			
			
		}// booking_list

		$err['code'] = 0;
		$err['id_book'] = $id_book;
	}

	return $err;
}

?>
<br/>
<form method="POST" enctype="multipart/form-data" onsubmit="return checkfile(this)">
<table width="60%"  border="0" cellspacing=4 cellpadding=4 style="color: white">
<tr><th style="color: white">Import bookings in a CSV File</th></tr>
<tr><td height=20>&nbsp;</td></tr>
<?php
if (!empty($_msg)){
	$msg_class = 'info';
	if ($err['code'] != 0)
		$msg_class = 'error';
	echo '<tr><td ><p class="msg '.$msg_class.' center">'.$_msg.'</p></td></tr>'; 
}
?>

<tr><td height=20>&nbsp;</td></tr>
<tr valign="top">
  <td align="center">
    Select the file 
    <input type="file" name="csv" value="Select...">
  </td>
</tr>
<tr>
  <td align="center"> <input type="submit" name="import" value=" Process Import " > </td>
</tr>  
</table>  
</form>

<div style="height: 50px">&nbsp;</div>
<script>
	function checkfile(frm){
		if (frm.csv.files.length == 0){
			alert('Please select the csv file to be uploaded!');
			return false;
		}
		return true;
	}


</script>
