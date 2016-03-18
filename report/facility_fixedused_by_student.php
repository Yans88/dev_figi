<?php
if (!defined('FIGIPASS')) exit;

	$fullname = $_GET['fullname'] ? $_GET['fullname'] : null;
	$nric = $_GET['nric'] ? $_GET['nric'] : null;
	$start_date = $_GET['start_date'] ? $_GET['start_date'] : null;
	$end_date = $_GET['end_date'] ? $_GET['end_date'] : date("d-m-Y");
	$facility = $_GET['facility'] ? $_GET['facility'] : null;
	$asset_no = $_GET['asset_no'] ? $_GET['asset_no'] : null;
	$administrated_by = $_GET['administrated_by'] ? $_GET['administrated_by'] : null;
?>
<h2>Facility fixed by Student</h2>
<form method="GET" id='filter'>
	<table>
		<tr>
			<td>Full Name  </td><td>
				<input type="text" name="fullname" id='full_name' value='<?php echo $fullname;?>'>
			</td>
		</tr>
		<tr>
			<td>NRiC  </td><td><input type="text" name="nric" id='nric'  value='<?php echo $nric;?>'></td>
		</tr>
		<tr>
			<td>Start Date </td><td><input type="text" name="start_date" id="start_date"  value='<?php echo $start_date;?>'></td>
		</tr>
		<tr>
			<td>End Date </td><td><input type="text" name="end_date" id="end_date"  value='<?php echo $end_date;?>'></td>
		</tr>
		<tr>
			<td>Facility </td><td><input type="text" name="facility" id='facility'  value='<?php echo $facility;?>'></td>
		</tr>
		<tr>
			<td>Asset No </td><td><input type="text" name="asset_no" id='asset_no'  value='<?php echo $asset_no;?>'></td>
		</tr>
		<tr>
			<td>Administrated By </td><td><input type="text" name="administrated_by" id="administrated_by"  value='<?php echo $administrated_by;?>'></td>
		</tr>
		<tr>
			<td></td><td><input type="button" name="view" id="view" value="View"><input type="reset" name="reset" id="reset" value="Reset"></td>
		</tr>
	</table>
</form>
<script>
        $('#start_date').AnyTime_noPicker().AnyTime_picker({format: "%d-%m-%Y"});
		$('#end_date').AnyTime_noPicker().AnyTime_picker({format: "%d-%m-%Y"});
        
    </script>
<?php
$_page = isset($_GET['page']) ? $_GET['page'] : 1;
$total_page = 0;
$total_sum =0;

$_limit = RECORD_PER_PAGE;
$_start = 0;

if($_GET['view'] == 1){
	
	
	$count = count_data($fullname, $nric, $start_date, $end_date, $facility, $asset_no, $administrated_by);
	
	$total_item = $count;
		$total_page = ceil($total_item/$_limit);
		if ($_page > $total_page) 
			$_page = 1;
		if ($_page > 0)
			$_start = ($_page-1) * $_limit;		
	
	$data = get_data($fullname, $nric, $start_date, $end_date, $facility, $asset_no, $administrated_by, $_start, $_limit);
	echo "<br /><br /><br />";
	if($count > 0){
	
	echo "
	<table class='itemlist' id='itemlist'>
		<tr>
			<th>No</th>
			<th>Full Name</th>
			<th>Facility</th>
			<th>Station No.</th>
			<th>Administered By</th>
			<th>Asset No</th>
			<th>Start Date/Time</th>
			<th>End Date/Time</th>
		</tr>";
		
	$no=$_page-1;
	foreach ($data as $value){
		$no++;
		$class = ($no % 2 == 0) ? 'class="alt"':'class="normal"';
		
			echo"<tr ".$class.">
			<td>".$no."</td>
			<td>".$value['full_name']."</td>
			<td>".$value['location_name']."</td>
			<td align='center'>".$value['reg_number']."</td>
			<td align='center'>".$value['user_full_name']."</td>
			<td>".$value['asset_no']."</td>
			<td align='center'>".$value['start_date']."</td>
			<td align='center'>".$value['end_date']."</td>
			</tr>";
		
	}
	echo"
	<tr><td colspan='8' align='center'>";
	$host = $_SERVER[REQUEST_URI];
	echo make_paging($_page, $total_page, $host."&page=");
	
	echo "</td></tr>
	</table>";
	} else {
		echo "<div style='margin:0 auto;text-align:center;'>Data not available!</div>";
	}
	
	
}


function get_data($fullname=null, $nric=null, $start_date=null, $end_date=null, $facility=null, $asset_no=null, $administrated_by=null, $start, $limit){

$start_date = date("Y-m-d H:i", strtotime($start_date));
$end_date = date("Y-m-d H:i", strtotime($end_date));

$query = "SELECT students.full_name, location.location_name, students_trans_detail.reg_number, user.full_name as user_full_name, item.asset_no, 
students_trans.start_date, students_trans.end_date 
FROM students_trans_detail 
LEFT JOIN students_trans ON students_trans.id_trans = students_trans_detail.id_trans
LEFT JOIN students ON students.id_student = students_trans_detail.id_student
LEFT JOIN user ON user.id_user = students_trans.user_start
LEFT JOIN location ON location.id_location = students_trans.id_location 
LEFT JOIN item ON item.id_item = students_trans_detail.id_item 
WHERE start_date >= '".$start_date."' AND end_date <= '".$end_date." ' ";

if(!empty($facility)){
 $query .= " AND location.location_name  LIKE '%".$facility."%' ";
}
if(!empty($administrated_by)){
$query .= " AND user.full_name LIKE '%".$administrated_by."%'";
}
if(!empty($fullname)){
$query .= " AND students.full_name LIKE '%".$fullname."%' ";
}
if(!empty($asset_no)){
$query .= " AND item.asset_no LIKE '%".$asset_no."%' "; 
}
if(!empty($nric)){
$query .= " AND students.nric LIKE '%".$nric."%' ";
}

$query .= " ORDER BY students_trans.start_date LIMIT ".$start.",".$limit;

$mysql = mysql_query($query);
$row = mysql_num_rows($mysql);
if($row > 0){
	while($fetch = mysql_fetch_array($mysql)){
		$array[] = $fetch;
	}
	
	
}
error_log($query);
return $array;
}

function count_data($fullname=null, $nric=null, $start_date=null, $end_date=null, $facility=null, $asset_no=null, $administrated_by=null){

$dtfmt = "'%d-%m-%Y'";
$query = "SELECT count(*) as total
FROM students_trans_detail 
LEFT JOIN students_trans ON students_trans.id_trans = students_trans_detail.id_trans
LEFT JOIN students ON students.id_student = students_trans_detail.id_student
LEFT JOIN user ON user.id_user = students_trans.user_start
LEFT JOIN location ON location.id_location = students_trans.id_location 
LEFT JOIN item ON item.id_item = students_trans_detail.id_item 
WHERE start_date >= '".$start_date."' AND end_date <= '".$end_date."' ";

if(!empty($facility)){
 $query .= " AND location.location_name  LIKE '%".$facility."%' ";
}
if(!empty($administrated_by)){
$query .= " AND user.full_name LIKE '%".$administrated_by."%'";
}
if(!empty($fullname)){
$query .= " AND students.full_name LIKE '%".$fullname."%' ";
}
if(!empty($asset_no)){
$query .= " AND item.asset_no LIKE '%".$asset_no."%' "; 
}
if(!empty($nric)){
$query .= " AND students.nric LIKE '%".$nric."%' ";
}

$query .= " ORDER BY students_trans.start_date";

$mysql = mysql_query($query);
$fetch = mysql_fetch_array($mysql);

return $fetch['total'];

}

?>
<script>
$("#view").click(function(){
var fullname = $("#full_name").val();
var nric = $("#nric").val();
var start_date = $("#start_date").val();
var end_date = $("#end_date").val();
var facility = $("#facility").val();
var asset_no = $("#asset_no").val();
var administrated_by = $("#administrated_by").val();



location.href="./?mod=report&sub=facility&act=view&term=fixedused&by=student&view=1&fullname="+fullname+"&nric="+nric+"&start_date="+start_date+"&end_date="+end_date+"&faciity="+facility+"&asset_no="+asset_no+"&administrated_by="+administrated_by;

});

$("#reset").click(function(){
location.href="./?mod=report&sub=facility&act=view&term=fixedused&by=student";
});
</script>