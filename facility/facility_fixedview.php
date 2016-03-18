<style>
#facility_fixed{
	text-align: center;
	padding: 0;
	margin:0;
	width:880px;
}

#facility_fixed a, #facility_fixed input[type=checkbox]{
	color:blue;
	font-size:9pt;	
	margin-right:7px;
	margin-top: 30px;
	text-decoration:none;
}
#facility_fixed a:hover{
	color: black;
}
#facility_fixed td{
	border: 1px solid #000000;
	height:70px;
}

.item_fixed{
	width:210px;
	height: 120px;	
	margin-top:0;
	float:left;
	font-size : 10pt;
	background: #CACAAF;
}

#editStudent h3, #editStudent h2{
	color:#000000;
}

#editStudent{
	color:#000000;
	margin-top:20px;
	z-index: 99999;
	width: 300px;
	height:160px;
	margin: 10% auto;
	padding: 5px 20px 13px 20px;
	border-radius: 10px;
	background: #fff;
	background: -moz-linear-gradient(#fff, #999);
	background: -webkit-linear-gradient(#fff, #999);
	background: -o-linear-gradient(#fff, #999);
}

#editStudent input{
	height:20px;
	padding-left:5px;
}

.modalDialog {
	position: fixed;
	font-family: Arial, Helvetica, sans-serif;
	text-align:left;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;	
	z-index: 99999;
	background: rgba(0,0,0,0.8);
	opacity:0;
	-webkit-transition: opacity 400ms ease-in;
	-moz-transition: opacity 400ms ease-in;
	transition: opacity 400ms ease-in;
	pointer-events: none;
}

.modalDialog:target {
	opacity:1;
	pointer-events: auto;
}

.modalDialog > div {
	width: 400px;
	position: relative;
	margin: 10% auto;
	padding: 5px 20px 13px 20px;
	border-radius: 10px;	
}

.close {
	background: #606061;
	color: #FFFFFF;
	line-height: 25px;
	position: absolute;
	right: -12px;
	text-align: center;
	top: -10px;
	width: 24px;
	text-decoration: none;
	font-weight: bold;
	-webkit-border-radius: 12px;
	-moz-border-radius: 12px;
	border-radius: 12px;
	-moz-box-shadow: 1px 1px 3px #000;
	-webkit-box-shadow: 1px 1px 3px #000;
	box-shadow: 1px 1px 3px #000;
}

.close:hover { background: #00d9ff; }

.iconn{
		width: 70px;
		float:right;		
}
.upd_student{
	float:right;
	position:relative;
}
.item_list{
	font-size:10pt;
	line-height:18px;
}
.use{
	float:right; 
	width:80px; 
	height:40px; 
	margin-top:10px; 
	font-size:14px; 
	margin-right:150px;
	background:blue; color:#ffffff;
}
.use:hover{
	cursor:pointer;
}
</style>

<?php
if (!defined('FIGIPASS')) exit;

$user_end = $_SESSION['figi_userid'];
$_id = isset($_GET['id']) ? $_GET['id'] : 0;
$query = "SELECT st.id_location, l.location_name, st.id_class, st.status, st.start_date, st.end_date ";
$query .="FROM students_trans st, location l where st.id_location=l.id_location and st.id_trans = $_id"; 
$rs = mysql_query($query);
//error_log(mysql_error().$query); 
$rec = mysql_fetch_array($rs);
$sttus = $rec['status'];
if($sttus == 1){
	$status = 'In use';
	$date_use = 'Not Available';
}else{
	$status = 'End Used(Realesed)';
	$date_use = date("d-M-Y H:i ", strtotime($rec['end_date']));
}
?>
<br/>
<h2>Fixed item view</h2>
<table cellspacing=1 cellpadding=2 class="item_list">
<tr>
<td>Facility / room </td><td> :</td><td><?php echo $rec['location_name'] ?></td> <td style="display:block; padding-left:250px;"> </td>
<td>Start Date </td><td> :</td><td><?php echo date("d-M-Y H:i ", strtotime($rec['start_date']))?></td>
</tr>
<tr>
<td>Class </td><td> :</td><td><?php echo $rec['id_class'] ?></td><td></td>
<td>End Date </td><td> :</td><td><?php echo $date_use ?></td>
</tr>
<tr>
<td>Status </td><td> :</td><td><?php echo $status ?></td>
</tr>

</table>
<button type="button" class="use" id="use" onclick="use_room();">End Use</button>

<br><br><br><br><br>
<div id="facility_fixed">
<?php
$query = "SELECT st.id_student, st.reg_number,st.id_item, st.absent_present, s.full_name, i.asset_no FROM students_trans_detail st "; 
$query .= "LEFT JOIN students s ON s.id_student = st.id_student LEFT JOIN item i ON i.id_item = st.id_item where id_trans = $_id order by st.reg_number asc";
$rs = mysql_query($query);
//error_log(mysql_error().$query); 
if ($rs && (mysql_num_rows($rs) > 0)){
		while ($rec = mysql_fetch_array($rs)){
			$fullname = $rec['full_name'];
			$assetNo = $rec['asset_no'];
			$register = $rec['reg_number'];
			$absent = $rec['absent_present'];
			if($absent == 1){
				$stts = 'Present';
			}else{
				$stts = 'Absent';
			}
	echo <<<DATA
	<button class="item_fixed">$register<br/> Asset No : $assetNo<br/>
	Name : <lable class="fullname">$fullname</lable> <br/>
$stts	
DATA;
}
}
?>
</div>
<script>
var id= '<?php echo $_id ?>';
var status = '<?php echo $sttus ?>';
var user_end = '<?php echo $user_end ?>';
$(document).ready(function(){
	if(status == 1){
		$('.use').show();
	}else{
		$('.use').hide();
	}
});

function use_room(){		
	var data = {id :""+id+"",user:""+user_end+"",key:"end_use"};
	$.post("facility/save_facility_item.php", data, function(data){
		console.log(data);
		if(data == 'ok'){
			alert('Released');
			location.href='./?mod=facility&act=fixedview&id='+id;
		}else{
			alert(data);
		}
	});
	
}
</script>