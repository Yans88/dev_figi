<?php 

$tbl = '';

if (!defined('FIGIPASS')) exit;
if (!$i_can_create) {
   include 'unauthorized.php';
   return;
}
$_facility = !empty($_POST['id_facility']) ? $_POST['id_facility'] : null;
$location_list = get_location_list();
if (count($location_list) == 0)
	$location_list[0] = '--- no location available! ---';

//$spec_item = get_item_spec($data_item['id_item']);
$query = "SELECT f.id_facility, f.id_item, f.register_number, i.asset_no, l.location_name FROM facility_fixed_item f
		  left join item i on i.id_item =f.id_item left join location l on l.id_location = f.id_facility"; 
$query .=" where f.id_facility = ".$_facility. " order by f.register_number ASC";
$rs = mysql_query($query);
//error_log(mysql_error().$query);    
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
			</td>
    </tr>  
	<tr>
        <td width=90 valign="top">Item </td>
			<td>
				 <input type="text" id="edit_item" name="serial_no" size="90" onKeyUp="suggest(this, this.value);" autocomplete="off" >
            <a href="javascript:void(0)" onclick="add_item()"><img class="icon" id="add" src="images/add.png"></a>
            <div class="suggestionsBox" id="suggestions" style="display: none; z-index: 500; left: 0px; width:auto;"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestionList" id="suggestionsList">
			</td>
    </tr>  
</table>
</form>
<br><br>
<style>
#facility_fixed{
	text-align: center;
	padding: 0;
	margin:0;
	width:860px;
}

#facility_fixed a{
	color:blue;
	font-size:9pt;
	float:right;
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
	padding-top:15px;
	background: #CACAAF;
}

#edit_facility h3, #edit_facility h2{
	color:#000000;
}

#edit_facility{
	color:#000000;
	margin-top:20px;
	z-index: 99999;
	width: 770px;
	height:140px;
	position: relative;
	margin: 10% auto;
	padding: 5px 20px 13px 20px;
	border-radius: 10px;
	background: #fff;
	background: -moz-linear-gradient(#fff, #999);
	background: -webkit-linear-gradient(#fff, #999);
	background: -o-linear-gradient(#fff, #999);
}

#edit_facility input{
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

</style>
<div id="facility_fixed">
<?php 
$asset = array();
$allItem = '';
if ($rs && (mysql_num_rows($rs) > 0)){
		while ($rec = mysql_fetch_array($rs))
{	
$register = $rec['register_number'];
$facilityy = $rec['id_facility'];
$assetNo = $rec['asset_no'];
$id_asset = $rec['id_item'];
$asset[] = $id_asset;
$allItem = implode(',',$asset);
if($assetNo == ''){
	$assetNo = 'Not Available';
}
	echo <<<DATA
	
	<button class="item_fixed">$register<br/> Asset No : $assetNo<br/>
	<a href="javascript:void(0)" onclick="delete_item($id_asset, $register)">Delete</a>&nbsp;&nbsp;<a href="#editFacility" id="$facilityy,$assetNo,$register,$id_asset" class="editt">Edit</a>
	&nbsp;&nbsp;<a href="#" class="swap" onclick="swap_item($id_asset, $register)">Swap</a></button>		
DATA;

}

    }
?>
<input type="hidden" value="<?php echo $allItem;?>" name="all_item" class="all_item" id="all_item" readonly="readonly">
</div>
<div id="editFacility" class="modalDialog">
<form method="POST" id="edit_facility" class="edit_facility">
<h2>Edit Facility Item</h2>
<a href="#close" title="Close" class="close">X</a>
<input type="hidden" value="" id="item_edit" class="item_edit" readonly="readonly"/>
<input type="hidden" value="" id="register" class="register" readonly="readonly"/>
<h3><label>Register Number : </label><label class="reg_numb" id="reg_numb"><label></h3>
<h3><label>Facility/room : </label><label class="room" id="room"><label></h3>
<label>Asset No : </label><input type="text" id="edit_f" name="serial_no" size="90" onKeyUp="Mysuggest(this, this.value);" autocomplete="off" >
            <a href="javascript:void(0)" onclick="edit_item()"><img class="iconn" src="images/submit.png"></a>
            <div class="suggestions_Box" id="suggestion" style="display: none; z-index: 500; left: 50px; width:85%;"> 
                <img src="images/arrow.png" style="position: relative; top: -12px; left: 30px;" alt="upArrow" />
                <div class="suggestion_List" id="suggestions_List">
</form>
<div>
<script>
$(document).ready(function(){
	$('#editFacility').hide();		
})
$('#id_facility').change(function(){	
	$('#facility_fix').submit();		
});
$('.editt').click(function(e){
	var data = e.currentTarget.id;
	var room = $('#id_facility option:selected').text();
	var data2 = data.split(',');
	var id_facility = data2[0];
	var asset_no = data2[1];
	var register = data2[2];
	var id_asset = data2[3];
	$('#editFacility').fadeIn('fast');
	$('#item_edit').val(id_asset);
	$('#reg_numb').text(register);
	$('#room').text(room);
	$('#register').val(register);
	$('#edit_f').val(asset_no);
})

$('.close').click(function(){
	$('#item_edit').val('');
	$('#reg_numb').text('');
	$('#edit_f').val('');
	$('#register').val('');
	setTimeout("$('#suggestion').fadeOut();", 100);
	setTimeout("$('#suggestions').fadeOut();", 100);
});

function swap_item(id, reg_numb){
	var id_facility = $("#id_facility").val();
	var reg_numb =reg_numb;
	var id_item = id;
	var key = 'swap_item';
	var count = $('.item_fixed').length;
	var promp_swap = prompt("Swap register number : "+ reg_numb +" to Number : ")
	var swap ={idFacility: ""+id_facility+"", register_number: ""+reg_numb+"", to: ""+promp_swap+"", idItem: ""+id_item+"", key: ""+key+""};
	//console.log(swap);
	if(reg_numb == promp_swap){
		alert('Number must different with register number ' +reg_numb);
		return false;
	}	
	if (promp_swap) {           
			$.post('facility/save_facility_item.php',swap, function (swap) {
               if (swap == 'ok') {                        
                        location.reload();
                    }else{
						alert('Error: Please check your data !!!');
					}
            });			
        }
}

function suggest(me, inputString)
{
	var id_facility = $("#id_facility").val();	
	if(id_facility == ''){
		alert('Please select a facility/room !');
		$('#edit_item').val('');
	}
	if(inputString.length == 0) {
		$('#suggestions').fadeOut();
	} else {
        if (/,/.test(inputString)){
            var mathces = /.*, *(.+)/.exec(inputString);
            if (mathces != null)
                inputString = mathces[1];
        }
        var pd = {queryString: ""+inputString+"", inputId: ""+me.id+"", loc_id: ""+id_facility+""};		       
		$.post("facility/suggest_item.php", pd, function(data){
			if(data.length >0) {
				$('#suggestions').fadeIn();
				$('#suggestionsList').html(data);
			} else
                $('#suggestions').fadeOut();
		});
	}
}

function Mysuggest(me, inputString)
{
	var location = $("#id_facility").val();
	if(inputString.length == 0) {
		$('#suggestion').fadeOut();
	} else {
        if (/,/.test(inputString)){
            var mathces = /.*, *(.+)/.exec(inputString);
            if (mathces != null)
                inputString = mathces[1];
        }
        var pd = {queryString: ""+inputString+"", inputId: ""+me.id+"", loc_id: ""+location+""};		       
		$.post("facility/suggest_item.php", pd, function(data){
			if(data.length >0) {
				$('#suggestion').fadeIn();
				$('#suggestions_List').html(data);
			} else
                $('#suggestion').fadeOut();
		});
	}
}

function fill(id, thisValue, onclick) 
{
	$('#id_item').val('');
	$('#item_edit').val('');
	if (thisValue.length>0 && onclick){
		var cols = thisValue.split('|');
		$('#'+id).val(cols[1] + ', ' + cols[0] + ', ' + cols[2] + ', ' + cols[3] + ', ' + cols[4]);
		$('#id_item').val(cols[5]);
		$('#item_edit').val(cols[5]);
	}
	setTimeout("$('#suggestion').fadeOut();", 100);
	setTimeout("$('#suggestions').fadeOut();", 100);
}

function add_item(){
	var id_facility = $("#id_facility").val();
	var id_item = $('#id_item').val();
	var serial_no = $('#edit_item').val();
	var all_id = $("#all_item").val();
	var pattern = all_id.split(',');
	var ok = '';
	var registerNumber = $('.item_fixed').length + 1;
	var save = 'save';
	//console.log(id_location + '-' + id_item + '-' + number_urut);
	var data = {idFacility: ""+id_facility+"",idItem: ""+id_item+"", register_number: ""+registerNumber+"", key: ""+save+""};
	if(id_facility.length > 0 && serial_no.length > 0){
		for(var i=0;i<pattern.length;i++){		
			if(id_item == pattern[i]){
				alert('Asset no or serial number is already exist in list !');
				return false;				
			}else{
				ok = 'ok';
			}
		}		
	}else{
		alert("Please input the facility and asset number !")
	}
	if(ok == 'ok'){
		$.post("facility/save_facility_item.php", data, function(data){
			$('#facility_fix').submit();
			$("#id_item").val('');	
		});
		$("#id_item").val('');
	}	
}

function edit_item(){		
	var id_facility = $("#id_facility").val();
	var id_item = $('#item_edit').val();
	var registerNumber = $('#register').val();
	var serialNo = $('#edit_f').val();
	var edit = 'edit';
	var all_id = $("#all_item").val();
	var pattern = all_id.split(',');
	var data = {idFacility: ""+id_facility+"",idItem: ""+id_item+"", register_number: ""+registerNumber+"", key: ""+edit+""};
	if(serialNo.length > 0){
		for(var i=0;i<pattern.length;i++){		
			if(id_item == pattern[i]){
				alert('Asset no or serial number is already exist in list !');
				return false;
			}else{
				$.post("facility/save_facility_item.php", data, function(data){
				$("#editFacility").fadeOut(1000);
				$('#facility_fix').submit();
				$("#id_item").val('');				
			});
			$("#id_item").val('');
			}
		}	
	}else{
		alert("Please input the serial/asset number !")
	}	
}

function delete_item(id, reg_numb){
	var id_facility = $("#id_facility").val();
	var id_item = id;
	var reg = reg_numb;
	var del = '';	
	var jmlah = $(".item_fixed").length;
	if(reg == jmlah){
		del = 'del';
	}
		
	if(reg < jmlah){
		del = 'del_update';
	}	
	
	var data = {idFacility: ""+id_facility+"", register_number:""+reg+"", key: ""+del+""};	
	console.log(data);
	var ok = confirm('Are you sure delete ?');
    if (ok){
		$.post("facility/save_facility_item.php", data, function(data){					
			$('#facility_fix').submit();
		});
	}else{
		return false;
	}     
}

</script>