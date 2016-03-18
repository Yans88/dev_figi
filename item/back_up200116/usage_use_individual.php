<div style="color:#fff;">
<?php 

//require 'facility_util.php';
//require_once 'student/student_util.php';
$msg = '';
$query = '';
$rs = '';
if (!defined('FIGIPASS')) exit;
$_facility = !empty($_POST['id_facility']) ? $_POST['id_facility'] : 0;

/*CHECK LOCATION IN USE*/
$location_in_use = false;

$check = "SELECT id_trans, id_location, status, user_start from students_trans where id_location = ".$_facility." and status = 0"; 
$check_rs = mysql_query($check);
if($check_rs &&(mysql_num_rows($check_rs) > 0)){	
	$trans = mysql_fetch_array($check_rs);
	$id_trans = $trans['id_trans'];
	if($trans['user_start'] == USERID){
		$url='./?mod=portal&portal=student_usage&act=view&id='.$id_trans;
		redirect($url);
	}else{
		$msg = '<div class="msg"><h1>Location in use</h1></div>';
        $location_in_use = true;
	}	
}
/*END OF CHECK LOCATION IN USE*/

$location_list = get_location_with_fixed_item_list();
if (count($location_list) == 0){
	$location_list[0] = '--- no location available! ---';
} else {
	$location_list = array('0' => '* select location') + $location_list;
}

// GET MAX ITEM
$query = "SELECT MAX(register_number) max_regno FROM facility_fixed_item WHERE id_facility = '$_facility'";
$rs = mysql_query($query);
$rec = mysql_fetch_assoc($rs);
$max_regno = $rec['max_regno'];

$mappings = array();
$students = array();
$items = array();

if (!$location_in_use){ /* LOCATION IN USE START*/
	$info = array();
	$current_facility = 0;
	
	// extract information from session
	if (!empty($_SESSION['student_usage'])){
		$info = unserialize($_SESSION['student_usage']);
		
		if (!empty($info['current_facility'])) $current_facility = $info['current_facility'];
		//if (!empty($info['current_class'])) $current_class = $info['current_class'];
		if (!empty($info['items'])) $items = $info['items'];
		//if (!empty($info['students'])) $students = $info['students'];
		if (!empty($info['mappings'])) $mappings = $info['mappings'];
		if (empty($_POST)){
			if ($current_facility>0) $_facility = $current_facility;
			if ($current_class>0) $_class = 0;//
		}
	}
	
	$need_remapping = false;
	
	
	if ($current_facility != $_facility){
	
		$items = fixed_item_list($_facility);
		$info['current_facility'] = $_facility; // change current  facility
		$info['items'] = $items; // keep items for new current facility
		$need_remapping = true;
		
	}
	
	if(!empty($_SESSION['id_student'])){
		$mappings = array();
		$need_remapping = true;
	}
	
	if ($need_remapping){
		$mappings = array();
		if (!empty($items)){
			foreach ($items as $regno => $rec){
				$map = $rec;
				if (!empty($students[$regno])) 
					$map += $students[$regno];
				$mappings[$regno] = $map;
				//print_r($map);
			}
		}
		$info['mappings'] = $mappings; // keep new mapping
	}
	
	
	if($_POST['confirm'] > 0){
			
		$count_now = count($mappings);
		
		for($x=1;$x<=$count_now;$x++){
		
			unset($mappings[$x]['id_student']);
			unset($mappings[$x]['full_name']);
			
		}
		
		$student = explode(',', $_POST['students']);
		$nric = explode(',', $_POST['nric_student']);
		$count = count($student);
		$no = 0;
		for($i=0;$i<$count;$i++){
			$no++;
			//$arr[$no] = array(
				//'register_number' => $no,
				//'nric' => $nric[$i],
				//'full_name' => $student[$i]
			//);
			
			if($mappings[$no]['id_student'] == 0){
				
				$mappings[$no]['id_student'] = $nric[$i];
				$mappings[$no]['full_name'] = $student[$i];
				$mappings[$no]['register_number'] = $no;
			} else {
				unset($mappings[$no]['id_student']);
				unset($mappings[$no]['full_name']);
			
			}
			
			
			
		}
		$info['mappings'] = $mappings;
		
	}
	
	if (!empty($_POST['addStudent_submit'])){
		// ADD NEW STUDENT
		
		$id_student = $_POST['nric_student'];
		$get_student = checkNRICStudent($id_student);
		
		
		if($get_student == 0){
		
			echo "<script>alert('NRIC Student not available. Please check your NRIC again. Thank You.'); location.href='./?mod=portal&portal=student_usage&act=use_individual';</script>";
			
		} else {
		
			$full_name = $get_student[0];
			$nric = $get_student[1];
			$id_student = $get_student[2];
			
			$checkIdStudent = checkStudentWhetherLocationUse($id_student);
			if($checkIdStudent > 0){
			
				echo "<script>alert('NRIC student is still on class . Please check your NRIC again. Thank You.'); location.href='./?mod=portal&portal=student_usage&act=use_individual';</script>";
			
			} else {
			
				$to_regno = $_POST['register_number'];
				$old_mappings = $mappings[$to_regno];
				
				$id_item = $old_mappings['id_item'];
				$asset_no = $old_mappings['asset_no'];
				$register_number = $old_mappings['register_number'];
				
				//print_r(unserialize($_SESSION['student_usage']));
				//unset($mappings);
				//$mappings = unserialize($_SESSION['student_usage']);
				//
				
				$mappings[$to_regno]['id_student'] = $id_student;
				$mappings[$to_regno]['full_name'] = $full_name;
				$mappings[$to_regno]['asset_no'] = $asset_no;
				$mappings[$to_regno]['id_item'] = $id_item;

				$info['mappings'] = $mappings;
				
			}
			$_SESSION['student_usage'] = serialize($info);
		}
		
		
	}
	
	if (!empty($_POST['assign'])){
	
		$assigned_student = $_POST['assigned_student'];
		$assign_to_regno = $_POST['assign_to_regno'];
		foreach($students as $regno => $rec)
			if ($rec['id_student'] == $assigned_student){
				$mappings[$assign_to_regno] += $rec;
				break;
			}
		$info['mappings'] = $mappings; // keep manipulated mapping
		
	} 
	
	if (!empty($_POST['swap'])){
		
		$from_regno = $_POST['from_regno'];
		$to_regno = $_POST['to_regno'];
		
		$from_info = $mappings[$from_regno];
		$to_info = $mappings[$to_regno];
		$new_from = $from_info;
		$new_from['id_item'] = $to_info['id_item'];
		$new_from['id_student'] = $to_info['id_student'];
		$new_from['full_name'] = $to_info['full_name'];
		$new_to['asset_no'] = $to_info['asset_no'];
		
		$new_from['register_number'] = $from_regno;
		$new_to['id_item'] = $from_info['id_item'];
		$new_to['id_student'] = $from_info['id_student'];
		$new_to['full_name'] = $from_info['full_name'];
		$new_from['asset_no'] = $from_info['asset_no'];
		$new_to['register_number'] = $to_regno;
		$mappings[$from_regno] = $new_from;
		$mappings[$to_regno] = $new_to;
		$info['mappings'] = $mappings; // keep manipulated mapping
		
	} 
	
	if (!empty($_POST['remove'])){
	
		$from_regno = $_POST['from_regno'];
		$confirm_text = $_POST['remove'];
		if ($confirm_text=='Remove'){
			unset($mappings[$from_regno]['id_student']);
			unset($mappings[$from_regno]['full_name']);
			unset($mappings[$from_regno]['register_number']);
			$info['mappings'] = $mappings; // keep manipulated mapping
		}
		
	} 
	
	
	if($_POST['use_mapping'] > 0){
		
		$id_user = USERID;
		$status = 0;
		$start_time = date('Y-m-d H:i:s');
		$id_trans = 0;
		$query = "INSERT into students_trans (id_class,id_location, status, start_date, end_date, user_start, user_end) ";
		$query .= " VALUE('MIX', '$current_facility', '$status', '$start_time', 0, '$id_user', 0)";
		
		mysql_query($query);
		if ($rs && mysql_affected_rows()>0) 
			$id_trans = mysql_insert_id();
		if ($id_trans > 0){
			$absent_present = $_POST['absent_present'];
		//error_log(serialize($absent_present));
			$items = array(); // keep items for status changing
			foreach($mappings as $regno => $rec){
				$id_item = !empty($rec['id_item']) ? $rec['id_item'] : 0;
				$nric = $rec['id_student'];
				$id_student = get_id_student_by_nric($nric);
				$register_number = !empty($rec['register_number']) ? $rec['register_number'] : 0;
				$is_present = 0;
				if (!empty($absent_present[$regno]) && $absent_present[$regno]=='present') $is_present = 1;
				$values[] = "('$id_trans', '$id_student', '$id_item', '$register_number', $is_present)";
				if (!empty($id_student) && $is_present > 0)
					$items[] = $id_item;
			}
			
			if (!empty($values)){
				
				$query = "INSERT into students_trans_detail (id_trans, id_student, id_item, reg_number, absent_present) "; 
				$query .= " VALUES ".implode(', ', $values);
				mysql_query($query);
				error_log($query);
			}
			// update item's status to IN_USE
			if (!empty($items)){
				$in_use_status = IN_USE;
				$query = "UPDATE item SET id_status = '$in_use_status', status_defect = 'Student usage' WHERE id_item IN (".implode(', ', $items).")";
				mysql_query($query);
					error_log(mysql_error().$query);

			}
			
			unset($_SESSION['student_usage']);
			$url='./?mod=portal&portal=student_usage&act=view&id='.$id_trans;
			redirect($url);
			
		}
	}

	
	// MANIPULATED NECESSARY
	
	$prev_content = ob_get_contents(); // get internal buffer
	ob_clean(); // clean up first
	$_SESSION['student_usage'] = serialize($info); // keep info in session
	echo $prev_content;// output the previous buffer
	
	

} /* LOCATION IN USE == END ==*/


?>

<script type="text/javascript" src="js/jquery.fancybox.pack.js"></script>
<link rel="stylesheet" type="text/css" href="style/default/jquery.fancybox.css" media="screen" />
<link rel="stylesheet" type="text/css" href="./style/default/student_usage.css" />
<div class="mod_wrap">
	<div class="mod_title"><h3>Student Usage Management</h3></div>
		<div class="mod_links">	
			<a class="button" href="#mixedclass">Back</a>
		</div>
		<div class="mod_links">	
			<a class="button" href="#history">Student Usage History</a>
		</div>
</div>
<div class="clear"><!-- CLEAR --></div>


<form method="POST" id="facility_fix">
<div style="width: 800px; " class="middle">

	<input type="hidden" name="id_item" class="id_item" id="id_item">
	<input type="hidden" name="from_regno" id="from_regno" value=0>
	<input type="hidden" name="to_regno" id="to_regno" value=0>
	<input type="hidden" name="assign_to_regno" id="assign_to_regno" value=0>
	<input type="hidden" name="managed_student" id="managed_student" value=0>
	<input type="hidden" name="assigned_student" id="assigned_student" value=0>
	<input type="hidden" name="register_number" id="register_number">
	<input type="text" style="width: 200px;display:none;" id="students" name="students">
	<input type="text" style="width: 200px;display:none;" id="nric_student" name="nric_student">

	<table cellspacing=1 cellpadding=2 style="width: 700px; float: left;" class="round-corner separate space5-top space5-bottom ">
		<tr>
			<td width=90>Facility/Room</td>
				<td>
					<select name="id_facility" id="id_facility" class="id_facility">
					<?php echo build_option($location_list, $_facility);?>
					</select>
				</td>
				<td width=140>
					<input type='checkbox' id="all_present"> <label for="all_present">Check All Present</label>
				</td>
		</tr>  
		<tr>
			<td width=90 valign="top"> </td>
			<td></td>
			<td><input type='checkbox' id="all_absent"> <label for="all_absent">Check All Absent</label></td>
		</tr>
		
	</table>
	<div style="padding: 15px 10px; float: right" class="right">
		<button type="button" class="use round-corner filter" id="use_mapping">Use</button>	
	</div>
	
</div>



<div style='display:none;width:300px;padding:20px;height:auto;' id='list_student'>
<div>
	Insert NRiC Student : <br />
	<input type="text" style="width: 200px;" id="nric" name="nric">
    <button type="button" id="addStudent">Add Student</button>
	
	
	<table id='student_list' style='margin-top:10px;width:100%;'>
	<th>
	<td>No</td><td>Name</td><td>NRiC</td><td>Action</td>
	</th>
	
	</table>
	<button id='confirm' name='confirm' style="display:none;" value="1">Confirm</button>
</div>
</div>


<div style='display:none;color:#000;' id='add_list_student'>
<br /><br />
Assign Student to Mixed Class.<br /> Insert the NRIC student to add student.
<br /><br />
<div>
	NRIC Student <br />
	<input type="text" style="width: 120px;" id="single_nric" name="single_nric">
    <button type="button" id="addStudent_submit">Add Student</button>
</div>
</div>
<div class="clear"></div>



<div class="manage_student" id="manage_student" style="display: none">
<h3>Manage Student Position</h3>
<h4>Move/Swap Student</h4>
<div>
    Enter destination reg. no. &nbsp; 
    <input type="text" style="width: 20px;" id="move_to_regno" name="move_to_regno"> 
    <button type="button" id="swap">Move / Swap</button>
</div>
<div class="clear"></div>
<h4>Remove Student</h4>
<div>
    Enter confirm word 'Remove' &nbsp; 
    <input type="text" style="width: 80px;" id="remove_confirm" name="remove_confirm"> 
    <button type="button" id="remove">Remove</button>
</div>
<div class="clear"></div>

<div id="msg" style="height: 20px;"></div>
</div>

<div class="manage_student" id="assign_student" style="display: none">
<h3>Assign Student to Fixed Item</h3>
<h4>Available Students</h4>
<div>
    <?php 
	
	echo build_combo('id_student', $available_students);?> 
    <button type="button" id="assign" >Assign Student</button>
</div>
<div class="clear"></div>
<div id="msg" style="height: 20px;"></div>
</div>


<br /><br />
<br /><br />
<br />
<br />
<br />
<div id="fixed_item_list">

<?php

$get_data = get_template($_facility);


if($get_data[0] == 1){ /*TEMPLATE 1*/

	echo "<div class='container_template_right_1'>";
	$unavailable_students = array();
	for($x=1;$x<=$get_data[1];$x++){

		//$mappings[$_SESSION['register_number']]['id_student'] = $_SESSION['id_student'];
		
		$id_asset = $mappings[$x]['id_item'];
		$asset_no = $mappings[$x]['asset_no'];
		$id_student = !empty($mappings[$x]['id_student']) ? $mappings[$x]['id_student'] : 0;
		$fullname = $mappings[$x]['full_name'];
		$abs = null;
		
		if (! empty($id_student)) {
		
			$display_name  = '<a class="manage" href="#manage" id="manage-'.$id_student.'-'.$x.'" >'.$fullname.'</a>';
			$unavailable_students[$id_student] = $x; 
			if(empty($asset_no)){$button_asset_number=$x;} else {$button_asset_number = "<a href='#get_asset_number' id='asset_".$asset_no."' class='asset' >".$x.".</a>";}
			$kl = $button_asset_number." ".$display_name."<br />";
			$kl .= '<span class="">
					<div><input '.$abs.' type="radio" name="absent_present['.$x.']" class="absent_present present" id="present-'.$x.'" value="present">
					<label for="present-'.$x.'">Present</label></div>
					<div><input '.$abs.' type="radio" name="absent_present['.$x.']" class="absent_present absent" id="absent-'.$x.'" value="absent" >
					<label for="absent-'.$x.'">Absent</label></div>
				</span>';
				
		} else {
			$display_name = '<a class="addStudent" href="#addStudent" id="addStudent-'.$x.'">Add Student</a>';
			if(empty($asset_no)){$button_asset_number=$x;} else {$button_asset_number = "<a href='#get_asset_number' id='asset_".$asset_no."' class='asset' >".$x.".</a>";}
			$kl = $button_asset_number." ".$display_name."<br />";
			$kl .= '<span class="">
					<div><input '.$abs.' type="radio" name="absent_present['.$x.']" class="absent_present present" id="present-'.$x.'" value="present">
					<label for="present-'.$x.'">Present</label></div>
					<div><input '.$abs.' type="radio" name="absent_present['.$x.']" class="absent_present absent" id="absent-'.$x.'" value="absent" >
					<label for="absent-'.$x.'">Absent</label></div>
				</span>';
				
		}
		
		if($x > 13){$css ="container_template_right_2";} else { $css ="container_template_right_1";}
		if(($x % 4 == 0) && ($x !=40)){ $y = " </div> <div class='".$css."'> "; } else { $y = ""; }
		if($x % 4 == 1) { $j="bottom";}
		if($x % 4 == 2) { $j="left";}
		if($x % 4 == 3) { $j="top";}
		if($x % 4 == 0) { $j="right";}
		
		echo "<div class='lab1_".$j."'>".$kl."</div> ".$y;
		
	} 
	echo "</div>";
	
} else if($get_data[0] == 2) { /* TEMPLATE 2 */
	
	$unavailable_students = array();	
	for($s=1;$s<=$get_data[1];$s++){
		
		$id_asset = $mappings[$s]['id_item'];
		$asset_no = $mappings[$s]['asset_no'];
		$id_student = !empty($mappings[$s]['id_student']) ? $mappings[$s]['id_student'] : 0;
		$fullname = $mappings[$s]['full_name'];
		$abs = null;
		
		if (! empty($id_student)) {
		
			$display_name  = '<a class="manage" href="#manage" id="manage-'.$id_student.'-'.$s.'" >'.$fullname.'</a>';
			$unavailable_students[$id_student] = $s; 
			if(empty($asset_no)){$button_asset_number=$s;} else {$button_asset_number = "<a href='#get_asset_number' id='asset_".$asset_no."' class='asset' >".$s.".</a>";}
			$kl = $button_asset_number." ".$display_name."<br />";
			$kl .= '<span class="">
					<div><input '.$abs.' type="radio" name="absent_present['.$s.']" class="absent_present present" id="present-'.$s.'" value="present">
					<label for="present-'.$s.'">Present</label></div>
					<div><input '.$abs.' type="radio" name="absent_present['.$s.']" class="absent_present absent" id="absent-'.$s.'" value="absent" >
					<label for="absent-'.$s.'">Absent</label></div>
				</span>';
				
		} else {
			$display_name = '<a class="addStudent" href="#addStudent" id="addStudent-'.$s.'">Add Student</a>';
			if(empty($asset_no)){$button_asset_number=$s;} else {$button_asset_number = "<a href='#get_asset_number' id='asset_".$asset_no."' class='asset' >".$s.".</a>";}
			$kl = $button_asset_number." ".$display_name."<br />";
			$kl .= '<span class="">
					<div><input '.$abs.' type="radio" name="absent_present['.$s.']" class="absent_present present" id="present-'.$s.'" value="present">
					<label for="present-'.$s.'">Present</label></div>
					<div><input '.$abs.' type="radio" name="absent_present['.$s.']" class="absent_present absent" id="absent-'.$s.'" value="absent" >
					<label for="absent-'.$s.'">Absent</label></div>
				</span>';
				
		}
		
		
		if($s >= 1 && $s<= 38){
		
			if($s % 2 == 0){ 
				//belakang
				if($s % 10 == 0){
					
					echo "<div class='css_fields_left_2'>".$kl."</div></div></div>";
					
				} else {
					
					echo "<div class='css_fields_left_2'>".$kl."</div></div>";
				}
			} else { 
				if($s % 10 == 1){
					if($s > 30){ 
						$css = "container_left"; 
					} else {
						$css = "container_left_1";
					}
					echo "<div class='".$css."'><div class='body'><div class='css_fields_left_1'>".$kl."</div>";
				} else {
					echo "<div class='body'><div class='css_fields_left_1'>".$kl."</div>";
				}
			}
		}
		
		if($s > 38 && $s<= 41){
		
			if($s % 2 == 0){
				echo "<div class='css_fields_left_2'>".$kl."</div></div>";
			} else {
				echo "</div><div class='container_left_center'><div class='body'><div class='css_fields_left_1'>".$kl."</div>";
			}
		
		}
	}
	// collect available students
	$no_more_student = true;
	$available_students = array('-1'=>'* select student');

	foreach($students as $regno => $rec){
		if (empty($unavailable_students[$rec['id_student']])){
			$available_students[$rec['id_student']] = $rec['full_name'];
			$no_more_student = false;
		}
	}

} else if($get_data[0] == 3){ /*TEMPLATE 3*/
	
	echo "<div class='container_template_left_1'>";
	$unavailable_students = array();
	for($x=1;$x<=$get_data[1];$x++){
	
		$id_asset = $mappings[$x]['id_item'];
		$asset_no = $mappings[$x]['asset_no'];
		$id_student = !empty($mappings[$x]['id_student']) ? $mappings[$x]['id_student'] : 0;
		$fullname = $mappings[$x]['full_name'];
		$abs = null;
		
		if (! empty($id_student)) {
		
			$display_name  = '<a class="manage" href="#manage" id="manage-'.$id_student.'-'.$x.'" >'.$fullname.'</a>';
			$unavailable_students[$id_student] = $x; 
			if(empty($asset_no)){$button_asset_number=$x;} else {$button_asset_number = "<a href='#get_asset_number' id='asset_".$asset_no."' class='asset' >".$x.".</a>";}
			$kl = $button_asset_number." ".$display_name."<br />";
			$kl .= '<span class="">
					<div><input '.$abs.' type="radio" name="absent_present['.$x.']" class="absent_present present" id="present-'.$x.'" value="present">
					<label for="present-'.$x.'">Present</label></div>
					<div><input '.$abs.' type="radio" name="absent_present['.$x.']" class="absent_present absent" id="absent-'.$x.'" value="absent" >
					<label for="absent-'.$x.'">Absent</label></div>
				</span>';
				
		} else {
			$display_name = '<a class="addStudent" href="#addStudent" id="addStudent-'.$x.'">Add Student</a>';
			if(empty($asset_no)){$button_asset_number=$x;} else {$button_asset_number = "<a href='#get_asset_number' id='asset_".$asset_no."' class='asset' >".$x.".</a>";}
			$kl = $button_asset_number." ".$display_name."<br />";
			$kl .= '<span class="">
					<div><input '.$abs.' type="radio" name="absent_present['.$x.']" class="absent_present present" id="present-'.$x.'" value="present">
					<label for="present-'.$x.'">Present</label></div>
					<div><input '.$abs.' type="radio" name="absent_present['.$x.']" class="absent_present absent" id="absent-'.$x.'" value="absent" >
					<label for="absent-'.$x.'">Absent</label></div>
				</span>';
				
		}
		
		if($x > 13){$css ="container_template_left_2";} else { $css ="container_template_left_1";}
		if(($x % 4 == 0) && ($x !=40)){ $y = " </div> <div class='".$css."'> "; } else { $y = ""; }
		if($x % 4 == 1) { $j="bottom";}
		if($x % 4 == 2) { $j="left";}
		if($x % 4 == 3) { $j="top";}
		if($x % 4 == 0) { $j="right";}
		
		echo "<div class='lab1_".$j."'> ".$kl."</div> ".$y;
		
	} 
	echo "</div>";
	// collect available students
	$no_more_student = true;
	$available_students = array('-1'=>'* select student');

	foreach($students as $regno => $rec){
		if (empty($unavailable_students[$rec['id_student']])){
			$available_students[$rec['id_student']] = $rec['full_name'];
			$no_more_student = false;
		}
	}

} else if($get_data[0] == 4){ /*TEMPLATE 4*/
	
	$unavailable_students = array();
	for($s=1;$s<=$get_data[1];$s++){
	
		$id_asset = $mappings[$s]['id_item'];
		$asset_no = $mappings[$s]['asset_no'];
		$id_student = !empty($mappings[$s]['id_student']) ? $mappings[$s]['id_student'] : 0;
		$fullname = $mappings[$s]['full_name'];
		$abs = null;
		
		if (! empty($id_student)) {
		
			$display_name  = '<a class="manage" href="#manage" id="manage-'.$id_student.'-'.$s.'" >'.$fullname.'</a>';
			$unavailable_students[$id_student] = $s; 
			if(empty($asset_no)){$button_asset_number=$s;} else {$button_asset_number = "<a href='#get_asset_number' id='asset_".$asset_no."' class='asset' >".$s.".</a>";}
			$kl = $button_asset_number." ".$display_name."<br />";
			$kl .= '<span class="">
					<div><input '.$abs.' type="radio" name="absent_present['.$s.']" class="absent_present present" id="present-'.$s.'" value="present">
					<label for="present-'.$s.'">Present</label></div>
					<div><input '.$abs.' type="radio" name="absent_present['.$s.']" class="absent_present absent" id="absent-'.$s.'" value="absent" >
					<label for="absent-'.$s.'">Absent</label></div>
				</span>';
				
		} else {
			$display_name = '<a class="addStudent" href="#addStudent" id="addStudent-'.$s.'">Add Student</a>';
			if(empty($asset_no)){$button_asset_number=$s;} else {$button_asset_number = "<a href='#get_asset_number' id='asset_".$asset_no."' class='asset' >".$s.".</a>";}
			$kl = $button_asset_number." ".$display_name."<br />";
			$kl .= '<span class="">
					<div><input '.$abs.' type="radio" name="absent_present['.$s.']" class="absent_present present" id="present-'.$s.'" value="present">
					<label for="present-'.$s.'">Present</label></div>
					<div><input '.$abs.' type="radio" name="absent_present['.$s.']" class="absent_present absent" id="absent-'.$s.'" value="absent" >
					<label for="absent-'.$s.'">Absent</label></div>
				</span>';
				
		}
		
		
		if($s >= 1 && $s<= 38){
			if($s % 2 == 0){ 
				
				//belakang
				if($s % 10 == 0){
					echo "<div class='css_fields_right_2'>".$kl."</div></div></div>";
				} else {
					echo "<div class='css_fields_right_2'>".$kl."</div></div>";
				}
			} else { 
				
				if($s % 10 == 1){
					if($s > 30){ 
						$css = "container_right"; 
					} else {
						$css = "container_right_1";
					}
					
					echo "<div class='".$css."'><div class='body'><div class='css_fields_left_1'>".$kl."</div>";
				} else {
				
					echo "<div class='body'><div class='css_fields_right_1'>".$kl."</div>";
				}
			}
		}
		
		if($s > 38 && $s<= 41){
		
			if($s % 2 == 0){
				echo "<div class='css_fields_left_2'>".$kl."</div></div>";
			} else {
				echo "</div><div class='container_left_center'><div class='body'><div class='css_fields_left_1'>".$kl."</div>";
			}
		
		}
	}
	
	// collect available students
	$no_more_student = true;
	$available_students = array('-1'=>'* select student');

	foreach($students as $regno => $rec){
		if (empty($unavailable_students[$rec['id_student']])){
			$available_students[$rec['id_student']] = $rec['full_name'];
			$no_more_student = false;
		}
	}


}

















?>
</div>


</form>



<script>
var is_mapped = <?php echo (count($mappings)>0) ? 'true' : 'false'?>;



$(document).ready(function(){	
    $('.fancybox').fancybox({padding: 5 });


	$('#all_present').click(function(event) { 
		$('#all_absent').attr('checked',false);	
		$('.absent').each(function() { 
			this.checked = false;               
		});			
		if(this.checked) { 		
            $('.present').each(function() { 
               if (!this.disabled) this.checked = true;             
            });			    
        } else {
			$('.present').each(function() {
				if (!this.disabled) this.checked = false;                    
			}); 
		}
    });
	
	$('#all_absent').click(function(event) {
		$('#all_present').attr('checked',false);	
		$('.present').each(function() { 
			this.checked = false;              
		});
        if(this.checked) { 		
			$('.absent').each(function() { 
                if (!this.disabled) this.checked = true;                
            });
        }else{
			$('.absent').each(function() { 
                if (!this.disabled) this.checked = false;               
            });			
        }
    });	
});

$('.assign').click(function(){
    var id = this.id.substr(7);
    $('#assign_to_regno').val(id);
	
    $.fancybox.open({href: '#assign_student', padding: 5});
});

$('.asset').click(function(){
    var id = this.id.substr(7);
    //$('#show_asset_number').val(id);
	alert('Asset Number : '+id);
    //$.fancybox.open({href: '#show_asset_number', padding: 5});
});



$('#assign').click(function(){
    var student = $('#id_student').val();
    if (student > -1){
        $('#assigned_student').val(student);
        $('#facility_fix').append('<input type="hidden" name="assign" value=1>');
        $('#facility_fix').submit();
    } else {
        alert('Select a student to be assigned to.');
        $('#id_student').focus();
    }
});


$('a[href=#manage]').click(function(){
    var cols = this.id.split('-');
    var student = cols[1];
    var regno = cols[2];
    $('#from_regno').val(regno);
    $('#managed_student').val(student);
    $.fancybox.open({href: '#manage_student', padding: 5});
});

$('#swap').click(function(){
    //var student = $('#managed_student').val();
    //var from_regno = $('#from_regno').val();
    var move_to_regno = $('#move_to_regno').val();
    if (move_to_regno > 0){
        $('#to_regno').val(move_to_regno);
        $('#facility_fix').append('<input type="hidden" name="swap" value=1>');
        $('#facility_fix').submit();
		
    } else {
        alert('Enter correct registration number for destination!');
        $('#move_to_regno').focus();
    }
});

$('#remove').click(function(){
    var remove_confirm = $('#remove_confirm').val();
    if (remove_confirm == 'Remove'){
        $('#facility_fix').append('<input type="hidden" name="remove" value="Remove">');
        $('#facility_fix').submit();
    } else {
        alert('Enter correct confirmation word. It is case sensitive!');
        $('#remove_confirm').focus();
    }
});


$('#use_mapping').click(function(){
    var checked_all = false;
    var count_enabled = 0;
    var count_checked = 0;
    $('.absent_present').each(function(){
        if (!$(this).hasClass('no_abs')){
            count_enabled++;
            if (this.checked) count_checked++;
        }
    });
    if (count_enabled % count_checked == 0){
        $('#facility_fix').append('<input type="hidden" name="use_mapping" value=1>');
        $('#facility_fix').submit();
        
    } else {
        alert('Please check mark all student presence!');
    }
});


$('a[href=#addStudent]').click(function(){

	var number = this.id.substr(11);
	$('#register_number').val(number);
	var loc = $('#id_facility').val();
	var template = $('#template').val();
	
		//href: './?mod=facility&sub=fixed_item_assign_mixed_class&loc='+loc+'&regno='+number+'&template='+template,
		$.fancybox.open({href: '#add_list_student', padding: 5});

});

$('a[href=#history]').click(function(){
    var _location = $('#id_facility').val();
    location.href = "./?mod=portal&portal=student_usage&act=list&loc="+_location+"&class="+_class;
});

$('a[href=#mixedclass]').click(function(){
    location.href = "./?mod=portal&portal=student_usage";
});

$('#addStudent_submit').click(function(){

    var nric = $('#single_nric').val();
	var register_number = $('#register_number').val();
	
	
    if (nric){
        $('#nric_student').val(nric);
		$('#regno').val(nric);
        $('#facility_fix').append('<input type="hidden" name="addStudent_submit" value=1>');
        $('#facility_fix').submit();
		
    } else {
        alert('Enter correct registration number for destination!');
        $('#nric').focus();
    }
	
});

// Choose Facility
$('#id_facility').change(function(){
	var a = this.value;
	if(a > 0){
		
		$.fancybox.open({href: '#list_student', padding: 5});
		
	} else {
		$("#fixed_item_list").hide();
	}
});

// Add Student
$("#addStudent").click(function(){

	var a = $("#nric").val();
	var b = $("#students").val();
	var c = $("#nric_student").val();
	var d = c.search(a);
	
	if(d == -1){
		$.post('facility/usage_use_get_student.php', {nric:"" + a + ""}, function (data) {
			if(data != 0){
				
				if(data == -2){ 
					alert("NRiC "+ a +" still at other class.");
				} else {
					if(b == ''){
						var x = $("#students").val(data);
						$("#nric_student").val(a);
						display();
						$("#nric").val("");
						$("#nric").focus();
					} else {
						var x = $("#students").val(b+','+data);
						$("#nric_student").val(c+','+a);
						display();
						$("#nric").val("");
						$("#nric").focus();
					}
				}
			}  else {
				alert("NRiC not found. Please insert other NRiC Number.");
			}
		});
		
	} else {
	
		alert("NRiC number already exist.");
		
	}
	
});

function display(){
	var text = '';
	var a = $("#students").val();
	var b = $("#nric_student").val();
	var variable = a.split(',');
	var nric = b.split(',');
	
	if(variable != '' && variable.length > 0){
		var no = 0;
		for (var i=0; i < variable.length; i++){
		
			no++;
			text += '<tr style=list-style:none;><td>' + no + '.</td><td>' + variable[i] +'</td><td>' + nric[i] + '</td><td><a onclick="return delete_student(\''+ variable[i] +'\', \''+nric[i]+'\')"><img class=icon src=images/delete.png alt=delete></a></td></tr> ';
		
        }
		$("#confirm").show();
	} else {
        text = '--- empty list ---';
		$("#confirm").hide();
	}
	
	$('#student_list').html(text);
	
}


$("#confirm").click(function(){
	$('#facility_fix').append('<input type="hidden" name="confirm" value=1>');
	$("#facility_fix").submit();
	
});

function delete_student(fullname, nric){
	if (confirm("Are you sure want to delete "+ fullname +"?")){
		var students = $("#students").val();
		var nrics = $("#nric_student").val();
		
		var recs = students.split(',');
		var nrics_recs = nrics.split(',');
		
		var f = new Array();
		var n = new Array();
		
		for(var i=0; i < recs.length; i++){
			if(recs[i].search(fullname) == -1){
				f.push(recs[i]);
				n.push(nrics_recs[i]);
			}
		}
		
		$("#students").val(f);
		$("#nric_student").val(n);
		display();
	}
}



</script>


</div>

<?php
function get_id_student_by_nric($nric){
	$query = "SELECT * FROM students WHERE nric='$nric'";
	$mysql = mysql_query($query);
	$row = mysql_fetch_array($mysql);
	return $row['id_student'];
}
?>