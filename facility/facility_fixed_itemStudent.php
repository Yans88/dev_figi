<?php 
$msg = '';
$query = '';
$rs = '';
$use_hide = '';
if (!defined('FIGIPASS')) exit;
if (!$i_can_create) {
   include 'unauthorized.php';
   return;
}
$_facility = !empty($_POST['id_facility']) ? $_POST['id_facility'] : null;
$_class = !empty($_POST['id_class']) ? $_POST['id_class'] : null;
$_year = !empty($_POST['year']) ? $_POST['year'] : null;
$user_start = $_SESSION['figi_userid'];
$date_year = date("Y");
$location_list = get_location_list();
if (count($location_list) == 0)
	$location_list[0] = '--- no location available! ---';

$class_list = get_location_all();
if (count($class_list) == 0)
	$class_list[0] = '--- no class available! ---';

$year = get_year_list();
if (count($year) == 0)
	$year[0] = '--- no year available! ---';
$check = "SELECT id_trans, id_location, status, user_start from students_trans where id_location = ".$_facility." and status =1"; 
$check_rs = mysql_query($check);

$query = "SELECT f.id_facility, f.id_item, f.register_number, i.asset_no, l.location_name FROM item i, facility_fixed_item f, location l"; 
$query .=" where f.id_item = i.id_item and f.id_facility = l.id_location and f.id_facility = ".$_facility. " order by f.register_number ASC";	
if($check_rs &&(mysql_num_rows($check_rs) > 0)){	
	$trans = mysql_fetch_array($check_rs);
	$id_trans = $trans['id_trans'];
	if($trans['user_start'] == $user_start){
		$url='./?mod=facility&act=fixedview&id='.$id_trans;
		echo '<script>location.href="'.$url.'";</script>';	
	}else{
		$use_hide = 'use_hide';
		$msg = '<div class="msg"><h1>Location in use</h1></div>';
	}	
}else{
	$rs = mysql_query($query);
}
error_log(mysql_error().$query);
?>

<script type="text/javascript" src="./js/jquery.opacityrollover.js"></script>
<script type="text/javascript" src="./js/slimbox2.js"></script>
<script type="text/javascript" src='./js/jquery.MultiFile.js' language="javascript"></script>
<link rel="stylesheet" href="<?php echo STYLE_PATH?>slimbox2.css" type="text/css" media="screen" title="no title" charset="utf-8" />

<br/>
<h2>Fixed item</h2><br/>
<form method="POST" id="facility_fix">
<table cellspacing=1 cellpadding=2 id="">
<input type="hidden" name="id_item" class="id_item" id="id_item">
	<tr>
        <td width=90>Facility/Room</td>
			<td>
				<select name="id_facility" id="id_facility" class="id_facility">
				<?php echo build_option($location_list, $_facility);?>
				</select>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type='checkbox' id="all_present"> Check All Present
			</td>
    </tr>  
	<tr>
        <td width=90 valign="top">Class </td>
			<td>
				<select name="id_class" id="id_class" class="id_class">
				<?php echo build_option($class_list, $_class);?>
				</select>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type='checkbox' id="all_absent"> Check All Absent
			</td>
    </tr>  
	
	<tr style="display:none">
        <td width=90 valign="top">Year </td>
			<td>
			<input type="text" name="year" id="year" class="year" value="<?php echo $date_year ?>">
			</td>
    </tr>  
	<!---
	<tr>
        <td width=90 valign="top">Date </td>
			<td>
				<input type="text" id="date_of_purchase" name="date_of_purchase">
            <script>$('#date_of_purchase').AnyTime_picker({format: "%e-%b-%Y"});</script>
			</td>
    </tr>  
	--->
	<tr>	
	</tr>
</table>

</form>

<?php echo $msg ?>
</div>
<style>
.msg{
	background:red;
	width:420px;
	height:50px;	
	margin-top:30px;
}
.msg h1{
	margin-top:4px;
	text-align: center;
	font-weight:800;
	color : #fff;
	font-size:22pt;
}
#facility_fixed{
	text-align: center;
	padding: 0;
	margin-left:110px;
	width:860px;
}

.editt{
	color:blue;
	font-size:9pt;	
	margin-right:7px;
	margin-top: 30px;
	text-decoration:none;
	float:left;
}

.present, .absent{	
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

.no_abs{
	display:none;
}

.item_fixed{
	width:210px;
	height: 120px;	
	margin-top:0;
	float:left;
	padding-top:15px;
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

select{
  width: 180px;
}
.use{
	float:right; 
	width:80px; 
	height:40px; 
	margin-top:10px; 
	font-size:14px; 
	margin-right:20px;
	background:blue; color:#ffffff;
}
.use:hover{
	cursor:pointer;
}
.use_hide{
    display:none;
}
</style>
<div id="facility_fixed">
<button type="button" class="use <?php echo $use_hide ?>" id="use" onclick="use_room();">Use</button><br><br><br><br><br><br>
<?php 
$asset = array();
$reg_numb = array();
$allItem = '';
if ($rs && (mysql_num_rows($rs) > 0)){
		while ($rec = mysql_fetch_array($rs))
{	
$register = $rec['register_number'];
$facilityy = $rec['id_facility'];
$assetNo = $rec['asset_no'];
$id_asset = $rec['id_item'];
$asset[] = $id_asset;
$reg_numb[] = $register;
$allItem = implode(',',$asset);
$abs = '';
$chked = '';
$val_reg = '';
if($register > 0){
	if( $_class != '' && $_year != ''){
	$query_year = "SELECT s.id_student, s.register_number, s.full_name FROM student_classes sc LEFT JOIN students s ON s.id_student = sc.id_student WHERE sc.class = '".$_class."'"; 
    $query_year .= " and sc.year = ".$_year. " and s.register_number = " .$register;
}
	$ry = mysql_query($query_year);	
	$rec_y = mysql_fetch_array($ry);
	$fullname = $rec_y['full_name'];
	$id_student = $rec_y['id_student'];
	$_present = $register.'_present';
	$_absent = $register.'_absent';
	$name = 'Name : <lable class="fullname">'.$fullname.'</lable>';
	//error_log(mysql_error().$query_year); 
	if($id_student == ''){
		$abs = 'no_abs';
		$name = '';
		$chked = 'checked';
		$val_reg = '0,'.$register.','.$id_asset.','.$id_student;
	}
	if($id_student == '' || $id_student == '0'){
		$id_student = '0';
	}
}
	echo <<<DATA
	
	<input type="hidden" name="student_id" id="student_id" value="$register,$id_asset,$id_student">
	<input type="hidden" name="id" id="id" value="$id_student">
	<input type="text" name="$register" class="reg" id="$register" value="$val_reg">
	<button class="$register item_fixed">$register<br/> Asset No : $assetNo<br/>
	 $name<br/>
	<a href="#editStudent" class ="editt" id="$fullname,$assetNo,$register,$id_student">Edit</a>
	<div class="$abs">
	<input type="checkbox" onclick="chk($register);" name="stts" class="$_present present" id="present" value="1,$register,$id_asset,$id_student">Present || 
	<input type="checkbox" onclick="chk($register);" id="present" name="stts" class="$_absent absent" value="0,$register,$id_asset,$id_student" $chked>Absent</button>	
	</div>
DATA;

}
    }
	
	//print_r($reg_numb);
?>
<input type="hidden" value="<?php echo $allItem;?>" name="all_item" class="all_item" id="all_item" readonly="readonly">
</div>

<div id="editStudent" class="modalDialog">
<form method="POST" id="edit_student" class="edit_student">
<h2>Edit Student</h2>
<a href="#close" title="Close" class="close">X</a>
<input type="hidden" value="" id="id_student" class="id_student" readonly="readonly"/>
<input type="hidden" value="" id="register" class="register" readonly="readonly"/>
<input type="hidden" value="" id="old_idStudent" class="old_idStudent" readonly="readonly"/>
<h3><label>Register Number : </label><label class="reg_numb" id="reg_numb"><label></h3>
<h3><label>Asset no : </label><label class="asset" id="asset"><label></h3>
<h3><label>Name student : </label>
 <input type="text" id="name_student" name="name_student" size="27" onKeyUp="suggest(this, this.value);" autocomplete="off" >
            <a href="javascript:void(0)" onclick="editStudent()"><img class="iconn" src="images/submit.png"></a>
            <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500; left: 0px; width:210px; margin-bottom:0px;"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 10px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsList">

</h3>

</form>
<div>

<script>
$(document).ready(function(){	
	$('#editStudent').hide();	
	$('.no_abs').hide();
	$('.reg').hide();
	$('#all_present').click(function(event) { 
		$('#all_absent').attr('checked',false);	
        if(this.checked) { 		
            $('.present').each(function() { 
                this.checked = true;             
            });			    
			$('.absent').each(function() { 
                this.checked = false;			
            });
        }else{
            $('.present').each(function() {
                this.checked = false;                    
            }); 
			$('.absent').each(function() { 
                this.checked = false;               
            });			
        }
    });
	
	$('#all_absent').click(function(event) {
		$('#all_present').attr('checked',false);	
        if(this.checked) { 		
            $('.present').each(function() { 
                this.checked = false;              
            });
			$('.absent').each(function() { 
                this.checked = true;                
            });
        }else{
            $('.present').each(function() { 
                this.checked = false;                        
            }); 
			$('.absent').each(function() { 
                this.checked = false;               
            });			
        }
    });	
})

function chk(e){
	var reg = $('input[name="'+e+'"]').val(e);
	var x = '.'+e+'_present';
	var y = '.'+e+'_absent';
	$(y).click(function(){
		if(this.checked){
			this.checked = true;
			$(x).attr('checked', false);
		}else{
			this.checked = false;
			$('input[name="'+e+'"]').val('');
		}
	});		
	$(x).click(function(){
		if(this.checked){
			this.checked = true;
			$(y).attr('checked', false);
		}else{
			this.checked = false;	
			$('input[name="'+e+'"]').val('');
		}
	});	
	
}


$('#id_facility').change(function(){
	$('#facility_fix').submit();
});

$('#id_class').change(function(){
	$('#facility_fix').submit();
});

$('.editt').click(function(e){
	var data = e.currentTarget.id;	
	var data2 = data.split(',');
	var name = data2[0];
	var asset_no = data2[1];
	var register = data2[2];
    var old_idStudent = data2[3];
	$('#editStudent').fadeIn('fast');
	$('#name_student').val(name);
	$('#asset').text(asset_no);
	$('#reg_numb').text(register);	
	$('#register').val(register);
	$('#old_idStudent').val(old_idStudent);	
	//console.log(data2);
})

$('.close').click(function(){
	$('#item_edit').val('');
	$('#reg_numb').text('');
	$('#edit_f').val('');
	$('#register').val('');
	setTimeout("$('#suggestion').fadeOut();", 100);
	setTimeout("$('#suggestions').fadeOut();", 100);
})
 
function editStudent(){
	var old_idStudent = $("#old_idStudent").val();
	var id_student = $("#id_student").val();
	var Myclass = $('#id_class').val();
	var register = $('#register').val();
	var name = $('#name_student').val();
	var save = 'upd_student';	
	var data = {id_student: ""+id_student+"",old_student: ""+old_idStudent+"",register_number: ""+register+"", name: ""+name+"", Myclass:""+Myclass+"", key: ""+save+""};	
	$.post("facility/save_facility_item.php", data, function(data){
		$('#facility_fix').submit();
		$("#id_student").val('');	
		$('#name_student').val('');
	});
	$("#id_item").val('');
}

function use_room(){	
	var id_start = '<?php echo $user_start ?>';	
	var Myclass = $('#id_class').val();
	var id_facility = $("#id_facility").val();	
	var year = $('#year').val();
	var msg = '';
	var status = '1';
	var key = "trans";
	if(Myclass == '' || year == ''){
		alert('You must select a class or year option');
		return false;
	}else{
		$('.reg').each(function(){
			if ($(this).val() == '') {
				msg = 'false';
			}
		});	
	}
	if(msg == 'false'){
		alert('You must checklist complete');
		return false;
	}else{
		var trans = {Myclass: ""+Myclass+"",idFacility: ""+id_facility+"", user: ""+id_start+"", status: ""+status+"", key: ""+key+""};	
		$.post("facility/save_facility_item.php", trans, function(trans){
		console.log(trans);
		//$('#facility_fix').submit();	
			var dt = trans.split('_');
			var ok = dt[0];
			var id = dt[1];
			if(ok == 'ok'){
				$('#present:checked').each(function(){		
					var data = $(this).val().split(',');
					var absent = data[0];
					var register = data[1];
					var id_asset = data[2];
					var id_student = data[3];
					var save_all = "save_all";		
					var send_data = {id: ""+id+"",register_number: ""+register+"",idItem: ""+id_asset+"", id_student: ""+id_student+"", present: ""+absent+"", key: ""+save_all+""};
					$.post("facility/save_facility_item.php", send_data, function(send_data){
						//console.log(send_data);
						//$('#facility_fix').submit();
						//alert('Succes');
						if(send_data != 'ok'){
							return false;
						}
					});					
				});
				alert('succes');
				location.href='./?mod=facility&act=fixedview&id='+id;
			}		
		});
	}	
}


function suggest(me, inputString)
{	
	var Myclass = $('#id_class').val();
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
        if (/,/.test(inputString)){
            var mathces = /.*, *(.+)/.exec(inputString);
            if (mathces != null)
                inputString = mathces[1];
        }
        var pd = {queryString: ""+inputString+"", inputId: ""+me.id+"", Myclass: ""+Myclass+""};		       
		$.post("facility/suggest_students.php", pd, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			} else
                $('#suggestions').fadeOut();
		});
	}
}

function fill(id, thisValue, onclick) 
{	
	$('#name_student').val('');
	$('#id_student').val('');
	if (thisValue.length>0 && onclick){
		var cols = thisValue.split('|');
		$('#'+id).val(cols[1] + ', ' + cols[0] + ', ' + cols[2] + ', ' + cols[3] + ', ' + cols[4]);
		$('#name_student').val(cols[0]);
		$('#id_student').val(cols[1]);		
	}
	setTimeout("$('#suggestions').fadeOut();", 100);
}


</script>